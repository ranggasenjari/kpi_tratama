<?php

namespace App\Services;

use App\Models\KpiIndicator;
use App\Models\Submission;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class KpiCalculator
{
    public function reportForUser(User $user, string $month): array
    {
        $start = CarbonImmutable::createFromFormat('Y-m', $month, config('app.timezone'))->startOfMonth();
        $end = $start->endOfMonth();

        $indicators = $this->assignedIndicators($user, $start);
        $totalWeight = max(0.01, (float) $indicators->sum(fn (KpiIndicator $indicator) => (float) $indicator->weight));

        $breakdown = $indicators->map(function (KpiIndicator $indicator) use ($user, $start, $end) {
            $submissions = Submission::query()
                ->approved()
                ->where('user_id', $user->id)
                ->when($indicator->form_template_id, fn ($query) => $query->where('form_template_id', $indicator->form_template_id))
                ->whereBetween('submitted_at', [$start, $end])
                ->get();

            $achieved = $this->achievementValue($indicator, $submissions);
            $target = max(0.01, (float) $indicator->target);
            $ratio = $achieved / $target;

            if (! $indicator->allow_overachievement) {
                $ratio = min(1, $ratio);
            }

            $ratio = max(0, $ratio);
            $score = round((float) $indicator->weight * $ratio, 2);
            $reward = $this->rewardValue($indicator, $achieved, $target);

            return [
                'indicator_id' => $indicator->id,
                'name' => $indicator->name,
                'type' => $indicator->type,
                'target' => round($target, 2),
                'achieved' => round($achieved, 2),
                'weight' => (float) $indicator->weight,
                'score_contribution' => $score,
                'achievement_percent' => round($ratio * 100, 2),
                'reward' => $reward,
                'approved_count' => $submissions->count(),
            ];
        })->values();

        $score = round(($breakdown->sum('score_contribution') / $totalWeight) * 100, 2);

        return [
            'user' => $user->only(['id', 'name', 'nik', 'role', 'outlet_id', 'job_role_id']),
            'month' => $month,
            'score' => $score,
            'reward_total' => round($breakdown->sum('reward'), 2),
            'breakdown' => $breakdown->all(),
        ];
    }

    public function assignedIndicators(User $user, CarbonImmutable $asOf): Collection
    {
        return KpiIndicator::query()
            ->with('assignments')
            ->where('is_active', true)
            ->whereHas('assignments', function ($query) use ($user, $asOf) {
                $query->where('is_active', true)
                    ->where(function ($dateQuery) use ($asOf) {
                        $dateQuery->whereNull('starts_at')->orWhere('starts_at', '<=', $asOf->toDateString());
                    })
                    ->where(function ($dateQuery) use ($asOf) {
                        $dateQuery->whereNull('ends_at')->orWhere('ends_at', '>=', $asOf->toDateString());
                    })
                    ->where(function ($assignment) use ($user) {
                        $assignment
                            ->where(function ($query) use ($user) {
                                $query->whereNull('user_id')->orWhere('user_id', $user->id);
                            })
                            ->where(function ($query) use ($user) {
                                $query->whereNull('outlet_id')->orWhere('outlet_id', $user->outlet_id);
                            })
                            ->where(function ($query) use ($user) {
                                $query->whereNull('job_role_id')->orWhere('job_role_id', $user->job_role_id);
                            });
                    });
            })
            ->orderBy('name')
            ->get();
    }

    private function achievementValue(KpiIndicator $indicator, Collection $submissions): float
    {
        if ($indicator->type === 'manual_score') {
            $average = $submissions->whereNotNull('manual_score')->avg('manual_score');

            return $average ? ((float) $average / 100) * max(1, (float) $indicator->target) : 0;
        }

        if ($indicator->type === 'quantity_reward') {
            return (float) $submissions->sum(fn (Submission $submission) => (float) $submission->approved_units);
        }

        return (float) $submissions->count();
    }

    private function rewardValue(KpiIndicator $indicator, float $achieved, float $target): float
    {
        $amount = (float) $indicator->reward_amount;

        if ($amount <= 0) {
            return 0;
        }

        if ($indicator->type === 'quantity_reward') {
            return round($achieved * $amount, 2);
        }

        return $achieved >= $target ? round($amount, 2) : 0;
    }
}
