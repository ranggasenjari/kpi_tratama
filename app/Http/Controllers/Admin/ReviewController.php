<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewLog;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(): Response
    {
        $submissions = Submission::query()
            ->with(['template', 'user.outlet', 'user.jobRole', 'answers.field', 'attachments.field', 'reviewer'])
            ->latest('submitted_at')
            ->paginate(20)
            ->through(fn (Submission $submission) => [
                'id' => $submission->id,
                'form_name' => $submission->template->name,
                'user' => [
                    'name' => $submission->user->name,
                    'nik' => $submission->user->nik,
                    'outlet' => $submission->user->outlet?->code,
                    'job_role' => $submission->user->jobRole?->name,
                ],
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
                'review_note' => $submission->review_note,
                'manual_score' => $submission->manual_score ? (float) $submission->manual_score : null,
                'approved_units' => (float) $submission->approved_units,
                'answers' => $submission->answers->map(fn ($answer) => [
                    'label' => $answer->field->label,
                    'value' => $answer->value['answer'] ?? null,
                ]),
                'attachments' => $submission->attachments->map(fn ($attachment) => [
                    'label' => $attachment->field?->label,
                    'name' => $attachment->original_name,
                    'mime' => $attachment->mime_type,
                    'size' => $attachment->size,
                ]),
            ]);

        return Inertia::render('Admin/Reviews', [
            'submissions' => $submissions,
        ]);
    }

    public function update(Request $request, Submission $submission): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,pending'],
            'review_note' => ['nullable', 'string'],
            'manual_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'approved_units' => ['required', 'numeric', 'min:0'],
        ]);

        $from = $submission->status;

        $submission->update([
            'status' => $data['status'],
            'review_note' => $data['review_note'] ?? null,
            'manual_score' => $data['manual_score'] ?? null,
            'approved_units' => $data['approved_units'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        ReviewLog::create([
            'submission_id' => $submission->id,
            'reviewed_by' => $request->user()->id,
            'from_status' => $from,
            'to_status' => $data['status'],
            'note' => $data['review_note'] ?? null,
            'metadata' => [
                'manual_score' => $data['manual_score'] ?? null,
                'approved_units' => $data['approved_units'],
            ],
        ]);

        return back()->with('success', 'Review submission berhasil disimpan.');
    }
}
