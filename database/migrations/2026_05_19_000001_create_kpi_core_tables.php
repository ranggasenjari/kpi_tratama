<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('job_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nik')->nullable()->unique()->after('name');
            $table->string('role')->default('karyawan')->after('email_verified_at');
            $table->foreignId('outlet_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->foreignId('job_role_id')->nullable()->after('outlet_id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('job_role_id');
            $table->string('position_title')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('position_title');
        });

        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->default('operational');
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('form_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['form_template_id', 'version']);
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_version_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('slug');
            $table->string('type');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('options')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_version_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->decimal('manual_score', 5, 2)->nullable();
            $table->decimal('approved_units', 8, 2)->default(1);
            $table->timestamps();
        });

        Schema::create('submission_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_field_id')->constrained()->restrictOnDelete();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_field_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('collection')->default('evidence');
            $table->timestamps();
        });

        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type');
            $table->string('period')->default('monthly');
            $table->decimal('target', 10, 2)->default(1);
            $table->decimal('weight', 8, 2)->default(1);
            $table->decimal('reward_amount', 12, 2)->default(0);
            $table->string('reward_unit')->nullable();
            $table->boolean('allow_overachievement')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_indicator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('job_role_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_monthly_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('period_month', 7);
            $table->decimal('score', 6, 2)->default(0);
            $table->decimal('reward_total', 12, 2)->default(0);
            $table->json('breakdown')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_monthly_results');
        Schema::dropIfExists('review_logs');
        Schema::dropIfExists('kpi_assignments');
        Schema::dropIfExists('kpi_indicators');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('submission_answers');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_versions');
        Schema::dropIfExists('form_templates');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('job_role_id');
            $table->dropConstrainedForeignId('outlet_id');
            $table->dropColumn([
                'nik',
                'role',
                'phone',
                'position_title',
                'is_active',
            ]);
        });

        Schema::dropIfExists('job_roles');
        Schema::dropIfExists('outlets');
    }
};
