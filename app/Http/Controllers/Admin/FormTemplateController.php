<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\FormVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class FormTemplateController extends Controller
{
    public function index(): Response
    {
        $templates = FormTemplate::query()
            ->withCount('submissions')
            ->with(['currentVersion.fields'])
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (FormTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'category' => $template->category,
                'is_active' => $template->is_active,
                'version' => $template->currentVersion?->version,
                'fields' => $template->currentVersion?->fields->values() ?? [],
                'submissions_count' => $template->submissions_count,
            ]);

        return Inertia::render('Admin/Forms', [
            'templates' => $templates,
            'fieldTypes' => $this->fieldTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedForm($request);

        $template = FormTemplate::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5)),
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        $version = $this->createVersion($template, $data);
        $template->update(['current_version_id' => $version->id]);

        return back()->with('success', 'Form baru berhasil dibuat.');
    }

    public function newVersion(Request $request, FormTemplate $form): RedirectResponse
    {
        $data = $this->validatedForm($request);

        $form->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        $version = $this->createVersion($form, $data);
        $form->update(['current_version_id' => $version->id]);

        return back()->with('success', 'Versi form baru berhasil diterbitkan.');
    }

    public function toggle(FormTemplate $form): RedirectResponse
    {
        $form->update(['is_active' => ! $form->is_active]);

        return back()->with('success', 'Status form diperbarui.');
    }

    private function validatedForm(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:kpi,hr,operational'],
            'is_active' => ['boolean'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'in:text,textarea,date,select,radio,radio_other,checkbox,file'],
            'fields.*.is_required' => ['boolean'],
            'fields.*.options' => ['array'],
            'fields.*.config' => ['array'],
        ]);
    }

    private function createVersion(FormTemplate $template, array $data): FormVersion
    {
        $nextVersion = ((int) $template->versions()->max('version')) + 1;

        $version = $template->versions()->create([
            'version' => $nextVersion,
            'title' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_published' => true,
            'published_at' => now(),
        ]);

        collect($data['fields'])->values()->each(function (array $field, int $index) use ($version) {
            FormField::create([
                'form_version_id' => $version->id,
                'label' => $field['label'],
                'slug' => Str::slug($field['label']).'-'.($index + 1),
                'type' => $field['type'],
                'is_required' => $field['is_required'] ?? false,
                'sort_order' => $index + 1,
                'options' => array_values(array_filter($field['options'] ?? [])),
                'config' => $field['config'] ?? [],
            ]);
        });

        return $version;
    }

    private function fieldTypes(): array
    {
        return [
            ['value' => 'text', 'label' => 'Teks pendek'],
            ['value' => 'textarea', 'label' => 'Teks panjang'],
            ['value' => 'date', 'label' => 'Tanggal'],
            ['value' => 'select', 'label' => 'Dropdown'],
            ['value' => 'radio', 'label' => 'Radio'],
            ['value' => 'radio_other', 'label' => 'Radio + lainnya'],
            ['value' => 'checkbox', 'label' => 'Checkbox'],
            ['value' => 'file', 'label' => 'Upload file'],
        ];
    }
}
