<?php

namespace App\Http\Controllers;

use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeFormController extends Controller
{
    public function index(): Response
    {
        $forms = FormTemplate::query()
            ->with(['currentVersion.fields', 'kpiIndicators'])
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (FormTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'category' => $template->category,
                'fields_count' => $template->currentVersion?->fields->count() ?? 0,
                'is_kpi' => $template->kpiIndicators->isNotEmpty(),
            ]);

        return Inertia::render('Forms/Index', [
            'forms' => $forms,
        ]);
    }

    public function show(FormTemplate $form): Response
    {
        $form->load(['currentVersion.fields']);

        abort_unless($form->is_active && $form->currentVersion, 404);

        return Inertia::render('Forms/Show', [
            'template' => [
                'id' => $form->id,
                'name' => $form->name,
                'description' => $form->description,
                'category' => $form->category,
                'version' => $form->currentVersion->version,
                'fields' => $form->currentVersion->fields->values(),
            ],
        ]);
    }

    public function store(Request $request, FormTemplate $form): RedirectResponse
    {
        $form->load('currentVersion.fields');

        abort_unless($form->is_active && $form->currentVersion, 404);

        $fields = $form->currentVersion->fields;
        $answers = $request->input('answers', []);
        $files = $request->file('files', []);
        $errors = [];

        foreach ($fields as $field) {
            $value = Arr::get($answers, (string) $field->id);
            $fieldFiles = Arr::get($files, (string) $field->id, []);
            $fieldFiles = is_array($fieldFiles) ? $fieldFiles : [$fieldFiles];

            if ($field->type === 'file') {
                if ($field->is_required && count(array_filter($fieldFiles)) === 0) {
                    $errors["files.{$field->id}"] = "{$field->label} wajib diunggah.";
                }

                foreach (array_filter($fieldFiles) as $file) {
                    $maxMb = (int) ($field->config['max_size_mb'] ?? 10);

                    if ($file->getSize() > $maxMb * 1024 * 1024) {
                        $errors["files.{$field->id}"] = "{$field->label} maksimal {$maxMb} MB per file.";
                    }

                    $accepted = $field->config['accepted_types'] ?? [];
                    $mime = $file->getMimeType();

                    if ($accepted && ! $this->mimeAllowed($mime, $accepted)) {
                        $errors["files.{$field->id}"] = "{$field->label} harus sesuai tipe file yang diizinkan.";
                    }
                }
            } elseif ($field->is_required && $this->blankAnswer($value)) {
                $errors["answers.{$field->id}"] = "{$field->label} wajib diisi.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $submission = Submission::create([
            'form_template_id' => $form->id,
            'form_version_id' => $form->currentVersion->id,
            'user_id' => $request->user()->id,
            'outlet_id' => $request->user()->outlet_id,
            'job_role_id' => $request->user()->job_role_id,
            'status' => 'pending',
            'submitted_at' => now(),
            'approved_units' => $this->inferUnits($fields, $answers),
        ]);

        foreach ($fields as $field) {
            if ($field->type !== 'file') {
                $submission->answers()->create([
                    'form_field_id' => $field->id,
                    'value' => ['answer' => Arr::get($answers, (string) $field->id)],
                ]);

                continue;
            }

            $fieldFiles = Arr::get($files, (string) $field->id, []);
            $fieldFiles = is_array($fieldFiles) ? $fieldFiles : [$fieldFiles];

            foreach (array_filter($fieldFiles) as $file) {
                $path = $file->store("submissions/{$submission->id}", 'local');

                $submission->attachments()->create([
                    'form_field_id' => $field->id,
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()
            ->route('submissions.index')
            ->with('success', 'Form berhasil dikirim dan menunggu review admin.');
    }

    private function blankAnswer(mixed $value): bool
    {
        return $value === null || $value === '' || (is_array($value) && count(array_filter($value)) === 0);
    }

    private function mimeAllowed(?string $mime, array $accepted): bool
    {
        if (! $mime) {
            return false;
        }

        foreach ($accepted as $type) {
            if ($type === 'image' && str_starts_with($mime, 'image/')) {
                return true;
            }

            if ($type === 'pdf' && $mime === 'application/pdf') {
                return true;
            }
        }

        return false;
    }

    private function inferUnits($fields, array $answers): int
    {
        return max(1, $fields
            ->filter(fn (FormField $field) => $field->type === 'checkbox')
            ->map(fn (FormField $field) => Arr::get($answers, (string) $field->id, []))
            ->map(fn ($value) => is_array($value) ? count(array_filter($value)) : 0)
            ->max() ?? 1);
    }
}
