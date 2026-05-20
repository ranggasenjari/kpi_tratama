<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeSubmissionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Submission::query()
            ->with(['template', 'version', 'answers.field', 'attachments.field', 'reviewer'])
            ->latest('submitted_at');

        if (! $request->user()->isAdminLike()) {
            $query->where('user_id', $request->user()->id);
        }

        return Inertia::render('Submissions/Index', [
            'submissions' => $query->paginate(15)->through(fn (Submission $submission) => [
                'id' => $submission->id,
                'form_name' => $submission->template->name,
                'version' => $submission->version->version,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
                'reviewed_at' => $submission->reviewed_at?->timezone(config('app.timezone'))->format('d M Y H:i'),
                'reviewer' => $submission->reviewer?->name,
                'review_note' => $submission->review_note,
                'approved_units' => (float) $submission->approved_units,
                'manual_score' => $submission->manual_score ? (float) $submission->manual_score : null,
                'attachments_count' => $submission->attachments->count(),
            ]),
        ]);
    }
}
