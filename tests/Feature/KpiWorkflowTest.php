<?php

namespace Tests\Feature;

use App\Models\FormTemplate;
use App\Models\Submission;
use App\Models\User;
use App\Services\KpiCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class KpiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_login_with_nik(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'nik' => 'EMP001',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_dynamic_form_submission_is_pending_review(): void
    {
        $this->seed();

        $employee = User::where('nik', 'EMP001')->firstOrFail();
        $template = FormTemplate::where('slug', 'kpi-upload-sosmed')->with('currentVersion.fields')->firstOrFail();

        $response = $this
            ->actingAs($employee)
            ->post("/forms/{$template->id}/submissions", $this->uploadSosmedPayload($template));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/submissions');

        $submission = Submission::with(['answers', 'attachments'])->firstOrFail();

        $this->assertSame('pending', $submission->status);
        $this->assertSame(2.0, (float) $submission->approved_units);
        $this->assertCount(2, $submission->answers);
        $this->assertCount(1, $submission->attachments);
    }

    public function test_approved_submission_contributes_to_kpi_report_and_reward(): void
    {
        $this->seed();

        $employee = User::where('nik', 'EMP001')->firstOrFail();
        $admin = User::where('nik', 'ADM001')->firstOrFail();
        $template = FormTemplate::where('slug', 'kpi-upload-sosmed')->with('currentVersion.fields')->firstOrFail();

        $this
            ->actingAs($employee)
            ->post("/forms/{$template->id}/submissions", $this->uploadSosmedPayload($template))
            ->assertSessionHasNoErrors();

        $submission = Submission::firstOrFail();

        $this
            ->actingAs($admin)
            ->patch("/admin/reviews/{$submission->id}", [
                'status' => 'approved',
                'review_note' => 'Bukti sesuai.',
                'manual_score' => null,
                'approved_units' => 2,
            ])
            ->assertSessionHasNoErrors();

        $report = app(KpiCalculator::class)->reportForUser($employee, now(config('app.timezone'))->format('Y-m'));
        $upload = collect($report['breakdown'])->firstWhere('name', 'KPI Upload Sosmed');

        $this->assertSame(2.0, $upload['achieved']);
        $this->assertSame(1000.0, $upload['reward']);
        $this->assertGreaterThan(0, $report['score']);
    }

    private function uploadSosmedPayload(FormTemplate $template): array
    {
        $fields = $template->currentVersion->fields->keyBy('type');
        $nameField = $template->currentVersion->fields->firstWhere('label', 'Nama Karyawan');
        $checkboxField = $fields['checkbox'];
        $fileField = $fields['file'];

        return [
            'answers' => [
                (string) $nameField->id => 'Edi Syahputra',
                (string) $checkboxField->id => ['Facebook', 'Instagram'],
            ],
            'files' => [
                (string) $fileField->id => [
                    UploadedFile::fake()->image('upload-sosmed.jpg')->size(128),
                ],
            ],
        ];
    }
}
