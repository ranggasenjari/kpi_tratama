<?php

namespace Database\Seeders;

use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\FormVersion;
use App\Models\JobRole;
use App\Models\KpiIndicator;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = collect([
            ['code' => 'TC01', 'name' => 'Tratama Center 01'],
            ['code' => 'TC02', 'name' => 'Tratama Center 02'],
            ['code' => 'TC03', 'name' => 'Tratama Center 03'],
        ])->mapWithKeys(fn ($data) => [$data['code'] => Outlet::firstOrCreate(['code' => $data['code']], $data)]);

        $roles = collect([
            ['code' => 'service', 'name' => 'Petugas Service'],
            ['code' => 'sales', 'name' => 'Sales Marketing'],
            ['code' => 'store', 'name' => 'Operasional Toko'],
            ['code' => 'admin', 'name' => 'Administrasi'],
        ])->mapWithKeys(fn ($data) => [$data['code'] => JobRole::firstOrCreate(['code' => $data['code']], $data)]);

        User::updateOrCreate(
            ['nik' => 'OWNER001'],
            [
                'name' => 'Owner Tratama',
                'email' => 'owner@tratama.local',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['nik' => 'ADM001'],
            [
                'name' => 'Admin KPI',
                'email' => 'admin@tratama.local',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'outlet_id' => $outlets['TC01']->id,
                'job_role_id' => $roles['admin']->id,
                'is_active' => true,
            ],
        );

        collect([
            ['EMP001', 'Edi Syahputra', 'TC01', 'service'],
            ['EMP002', 'Erdian Prastyo', 'TC01', 'store'],
            ['EMP003', 'Yuli', 'TC02', 'store'],
            ['EMP004', 'Intan Nur Syahra', 'TC02', 'admin'],
            ['EMP005', 'Ana Tasya Putri', 'TC03', 'sales'],
            ['EMP006', 'Ila Selvia', 'TC03', 'service'],
            ['EMP007', 'Wira Yunandistira', 'TC01', 'sales'],
        ])->each(function (array $employee) use ($outlets, $roles) {
            [$nik, $name, $outlet, $jobRole] = $employee;

            User::updateOrCreate(
                ['nik' => $nik],
                [
                    'name' => $name,
                    'email' => Str::slug($name).'.'.$nik.'@tratama.local',
                    'password' => Hash::make('password'),
                    'role' => 'karyawan',
                    'outlet_id' => $outlets[$outlet]->id,
                    'job_role_id' => $roles[$jobRole]->id,
                    'position_title' => $roles[$jobRole]->name,
                    'is_active' => true,
                ],
            );
        });

        $this->seedFormsFromMarkdown();
        $this->seedKpiIndicators();
    }

    private function seedFormsFromMarkdown(): void
    {
        $source = base_path('../gform_extracted');

        foreach (glob($source.'/*_Fields.md') ?: [] as $file) {
            $content = file_get_contents($file);
            $name = $this->titleFromMarkdown($content, $file);
            $slug = Str::slug(str_replace('_Fields', '', pathinfo($file, PATHINFO_FILENAME)));
            $lowerName = Str::lower($name);
            $category = str_starts_with($lowerName, 'kpi') ? 'kpi' : (str_contains($lowerName, 'cuti') || str_contains($lowerName, 'kesehatan') || str_contains($lowerName, 'rekening') ? 'hr' : 'operational');

            $template = FormTemplate::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $this->descriptionFromMarkdown($content),
                    'category' => $category,
                    'is_active' => true,
                ],
            );

            if ($template->currentVersion) {
                continue;
            }

            $version = FormVersion::create([
                'form_template_id' => $template->id,
                'version' => 1,
                'title' => $name,
                'description' => $template->description,
                'is_published' => true,
                'published_at' => now(),
            ]);

            foreach ($this->fieldsFromMarkdown($content) as $index => $field) {
                FormField::create([
                    'form_version_id' => $version->id,
                    'label' => $field['label'],
                    'slug' => Str::slug($field['label']).'-'.($index + 1),
                    'type' => $field['type'],
                    'is_required' => $field['is_required'],
                    'sort_order' => $index + 1,
                    'options' => $field['options'],
                    'config' => $field['config'],
                ]);
            }

            $template->update(['current_version_id' => $version->id]);
        }
    }

    private function seedKpiIndicators(): void
    {
        $configs = [
            'kpi-buka-tutup-kunci-toko' => ['frequency', 26, 15, 0, null, false],
            'kpi-jasa-service-tratama' => ['quantity_reward', 10, 20, 0, 'unit', true],
            'kpi-kebersihan-harian' => ['frequency', 26, 20, 0, null, false],
            'kpi-kebersihan-total' => ['fixed_threshold_reward', 1, 15, 0, null, false],
            'kpi-pdh-pdl-karyawan-tratama' => ['fixed_threshold_reward', 20, 15, 30000, 'bulan', false],
            'kpi-rental-mobil-lepas-kunci' => ['quantity_reward', 2, 15, 0, 'unit', true],
            'kpi-upload-sosmed' => ['quantity_reward', 30, 15, 500, 'upload', true],
        ];

        foreach ($configs as $slug => $config) {
            $template = FormTemplate::where('slug', $slug)->first();

            if (! $template) {
                continue;
            }

            [$type, $target, $weight, $reward, $rewardUnit, $allowOverachievement] = $config;

            $indicator = KpiIndicator::updateOrCreate(
                ['code' => $slug],
                [
                    'form_template_id' => $template->id,
                    'name' => $template->name,
                    'type' => $type,
                    'period' => 'monthly',
                    'target' => $target,
                    'weight' => $weight,
                    'reward_amount' => $reward,
                    'reward_unit' => $rewardUnit,
                    'allow_overachievement' => $allowOverachievement,
                    'is_active' => true,
                ],
            );

            $indicator->assignments()->firstOrCreate([
                'outlet_id' => null,
                'job_role_id' => null,
                'user_id' => null,
            ], ['is_active' => true]);
        }
    }

    private function titleFromMarkdown(string $content, string $file): string
    {
        $content = ltrim($content, "\xEF\xBB\xBF");

        if (preg_match('/#\s+(.+?)(?:\r?\n|$)/', $content, $matches)) {
            return trim(preg_replace('/\s+-\s+Field List\s*$/', '', $matches[1]));
        }

        return Str::headline(str_replace(['_Fields', '_'], ['', ' '], pathinfo($file, PATHINFO_FILENAME)));
    }

    private function descriptionFromMarkdown(string $content): ?string
    {
        if (! preg_match('/## Deskripsi(?: Form)?\R(.+?)(?:\R---|\R## )/s', $content, $matches)) {
            return null;
        }

        return trim(strip_tags(str_replace(['**', '> ', '*'], '', $matches[1])));
    }

    private function fieldsFromMarkdown(string $content): array
    {
        preg_match_all('/###\s+\d+\.\s+(.+?)\R(.+?)(?=\R---|\R##|\z)/s', $content, $matches, PREG_SET_ORDER);

        return collect($matches)->map(function (array $match) {
            $body = $match[2];
            preg_match('/\*\*Tipe\*\*:\s*(.+)/', $body, $typeMatch);
            preg_match('/\*\*Wajib Diisi\*\*:\s*(.+)/', $body, $requiredMatch);

            $rawType = trim($typeMatch[1] ?? 'Textbox (Teks Pendek)');
            $options = $this->optionsFromBody($body);

            return [
                'label' => trim($match[1]),
                'type' => $this->mapFieldType($rawType),
                'is_required' => str_contains($requiredMatch[1] ?? '', '✅'),
                'options' => $options,
                'config' => $this->configFromBody($rawType, $body),
            ];
        })->all();
    }

    private function mapFieldType(string $rawType): string
    {
        return match (true) {
            str_contains($rawType, 'Upload File') => 'file',
            str_contains($rawType, 'Date Picker') => 'date',
            str_contains($rawType, 'Dropdown') => 'select',
            str_contains($rawType, 'Radio Button + Textbox') => 'radio_other',
            str_contains($rawType, 'Radio Button') => 'radio',
            str_contains($rawType, 'Checkbox') => 'checkbox',
            str_contains($rawType, 'Teks Panjang') => 'textarea',
            default => 'text',
        };
    }

    private function optionsFromBody(string $body): array
    {
        if (! preg_match('/\*\*Opsi\*\*:\R(.+?)(?:\R\R|$)/s', $body, $matches)) {
            return [];
        }

        return collect(preg_split('/\R/', trim($matches[1])))
            ->map(fn (string $line) => trim(preg_replace('/^\-\s+/', '', trim($line))))
            ->reject(fn (string $option) => $option === '' || Str::lower($option) === 'pilih (default)')
            ->values()
            ->all();
    }

    private function configFromBody(string $rawType, string $body): array
    {
        $config = [];

        if (preg_match('/Maksimal\s+\*\*(\d+)\s+file/i', $body, $matches)) {
            $config['max_files'] = (int) $matches[1];
        }

        if (preg_match('/Ukuran maksimal:\s+\*\*(\d+)\s+MB/i', $body, $matches)) {
            $config['max_size_mb'] = (int) $matches[1];
        }

        if (str_contains($rawType, 'Upload File')) {
            $config['accepted_types'] = [
                ...(str_contains($rawType, 'Gambar') || str_contains($rawType, 'Image') || str_contains($rawType, 'Drawing') ? ['image'] : []),
                ...(str_contains($rawType, 'PDF') ? ['pdf'] : []),
            ] ?: ['image', 'pdf'];
            $config['max_files'] ??= 1;
            $config['max_size_mb'] ??= 10;
        }

        return $config;
    }
}
