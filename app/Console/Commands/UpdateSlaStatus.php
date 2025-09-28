<?php

// app/Console/Commands/UpdateTaskSLAStatus.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class UpdateTaskSLAStatus extends Command
{
    protected $signature = 'tasks:update-sla-status';
    protected $description = 'Update SLA status for tasks';

    public function handle()
    {
        $tasks = Task::all();

        foreach ($tasks as $task) {
            if ($task->start_date && $task->sla) {
                $deadline = Carbon::parse($task->start_date)->addDays($task->sla);

                if (Carbon::now()->greaterThan($deadline) && $task->status !== 'done') {
                    $task->sla_status = 'overdue';
                } else {
                    $task->sla_status = 'on_time';
                }

                $task->save();
            }
        }

        $this->info('SLA status updated successfully.');
    }
}
