<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('status', 'tickets_status_index');
            $table->index('priority', 'tickets_priority_index');
            $table->index('created_at', 'tickets_created_at_index');

            // Leading user columns cover scope filters and the default created_at sort for ticket lists.
            $table->index(['created_by', 'created_at'], 'tickets_created_by_created_at_index');
            $table->index(['assigned_to', 'created_at'], 'tickets_assigned_to_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_assigned_to_created_at_index');
            $table->dropIndex('tickets_created_by_created_at_index');
            $table->dropIndex('tickets_created_at_index');
            $table->dropIndex('tickets_priority_index');
            $table->dropIndex('tickets_status_index');
        });
    }
};
