<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasAuditNote = Schema::hasColumn('transactions', 'audit_note');

        Schema::table('transactions', function (Blueprint $table) use ($hasAuditNote) {
            if (!Schema::hasColumn('transactions', 'audit_note')) {
                $table->text('audit_note')->nullable()->after('status');
            }

            if (!Schema::hasColumn('transactions', 'needs_reaudit')) {
                $column = $table->boolean('needs_reaudit')->default(false)->index();

                if ($hasAuditNote) {
                    $column->after('audit_note');
                } else {
                    $column->after('status');
                }
            }
        });
    }

    public function down(): void
    {
        //
    }
};
