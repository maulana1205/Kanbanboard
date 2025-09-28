<?php

namespace App\Exports;

use App\Models\Task;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TasksExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Task::with(['creator', 'assignee'])->get();
    }

    public function map($task): array
    {
        // Hitung SLA Status
        $slaStatus = $this->getSlaStatus($task);

        // Remark fix: tampilkan remark asli + tambahan jika in_progress & Over SLA
        $remark = $task->remark ?? '';
        if ($task->status === 'in_progress' && $slaStatus === 'Over SLA') {
            $remark = '' . ($remark ? ' ' . $remark : '');
        }

        // Hitung task_progress otomatis berdasarkan status
        $taskProgress = match($task->status) {
            'todo' => 0,
            'in_progress', 'review' => 50,
            'done' => 100,
            default => 0,
        };

        return [
            $task->id,
            $task->creator?->Name ?? '-',
            $task->assignee?->Name ?? '-',
            $task->title,
            $task->description,
            $task->status,
            $task->division,
            $task->team,
            $task->Function,
            $task->priority,
            $task->start_date ? Carbon::parse($task->start_date)->format('Y-m-d') : null,
            $task->finish_date ? Carbon::parse($task->finish_date)->format('Y-m-d') : null,
            $task->sla,
            $slaStatus, // SLA Status
            $taskProgress, // Progress otomatis
            $task->uom,
            $task->qty,
            $task->vendor,
            $task->fdt_id,
            $task->created_at ? Carbon::parse($task->created_at)->format('Y-m-d H:i:s') : null,
            $task->updated_at ? Carbon::parse($task->updated_at)->format('Y-m-d H:i:s') : null,
            $remark,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Created By',
            'Assigned To',
            'Title',
            'Description',
            'Status',
            'Division',
            'Team',
            'Function',
            'Priority',
            'Start Date',
            'Finish Date',
            'SLA',
            'SLA Status',  
            'Task Progress',
            'UOM',
            'Qty',
            'Vendor',
            'FDT ID',
            'Created At',
            'Updated At',
            'Remark',
        ];
    }

    /**
     * Hitung SLA Status (On SLA / Over SLA / No SLA)
     */
    private function getSlaStatus($task): string
    {
        if (!$task->start_date || !$task->sla) {
            return 'No SLA';
        }

        $start = Carbon::parse($task->start_date);
        $due = $start->copy()->addDays($task->sla);
        $today = Carbon::now();

        if ($task->finish_date) {
            $finish = Carbon::parse($task->finish_date);
            return $finish->gt($due) ? 'Over SLA' : 'On SLA';
        }

        return $today->gt($due) ? 'Over SLA' : 'On SLA';
    }
}
