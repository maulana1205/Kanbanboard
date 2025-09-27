<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('Function')->nullable()->after('team'); 
            $table->text('detail_task')->nullable()->after('Function');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('detail_task');
            $table->date('start_date')->nullable()->after('priority');
            $table->date('finish_date')->nullable()->after('start_date');
            $table->integer('sla')->nullable()->after('finish_date');
            $table->integer('task_progress')->default(0)->after('sla'); // 0–100
            $table->string('uom', 50)->nullable()->after('task_progress');
            $table->integer('qty')->nullable()->after('uom');
            $table->string('vendor')->nullable()->after('qty');
            $table->string('fdt_id')->nullable()->after('vendor');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'Function',
                'detail_task',
                'priority',
                'start_date',
                'finish_date',
                'sla',
                'task_progress',
                'uom',
                'qty',
                'vendor',
                'fdt_id',
            ]);
        });
    }
};
