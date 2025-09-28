<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TaskController extends Controller
{
    // GET /api/tasks
    public function index()
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'leader', 'manager'])) {
            $tasks = Task::with(['creator', 'assignee'])->get();
        } else {
            $tasks = Task::with(['creator', 'assignee'])
                ->where('assigned_to', $user->id)
                ->get();
        }

        // hitung sla_status tiap task
        $tasks->transform(function ($task) {
            $task->sla_status = $this->getSlaStatus($task);
            return $task;
        });

        return response()->json($tasks);
    }

    // POST /api/tasks
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'division'      => 'required|string|max:100',
            'team'          => 'nullable|string|max:100',
            'task_function' => 'nullable|string|max:100',
            'priority'      => 'nullable|string|in:low,medium,high',
            'start_date'    => 'nullable|date',
            'finish_date'   => 'nullable|date|after_or_equal:start_date',
            'sla'           => 'nullable|integer',
            'status'        => 'nullable|string|in:todo,in_progress,review,done',
            'progress'      => 'nullable|integer|min:0|max:100',
            'uom'           => 'nullable|string|max:50',
            'qty'           => 'nullable|integer',
            'vendor'        => 'nullable|string|max:255',
            'fdt_id'        => 'nullable|string|max:100',
            'assigned_to'   => 'nullable|exists:users,id',
            'remark'        => 'nullable|string|max:500',
        ]);

        $task = Task::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'division'      => $request->division,
            'team'          => $request->team,
            'Function'      => $request->task_function,
            'priority'      => $request->priority,
            'start_date'    => $request->start_date,
            'finish_date'   => $request->finish_date,
            'sla'           => $request->sla,
            'status'        => $request->status ?? 'todo',
            'progress'      => $request->progress ?? 0,
            'uom'           => $request->uom,
            'qty'           => $request->qty,
            'vendor'        => $request->vendor,
            'fdt_id'        => $request->fdt_id,
            'created_by'    => Auth::id(),
            'assigned_to'   => $request->assigned_to,
            'remark'        => $request->remark ?? null,
        ]);

        // hitung dan simpan sla_status
        $task->sla_status = $this->getSlaStatus($task);
        $task->save();

        return response()->json([
            'message' => 'Task created successfully',
            'task'    => $task->load(['creator', 'assignee']),
        ], 201);
    }

    // GET /api/tasks/{task}
    public function show(Task $task)
    {
        $task->sla_status = $this->getSlaStatus($task);
        return response()->json($task->load(['creator', 'assignee']));
    }

    // PUT /api/tasks/{task}
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'division'      => 'required|string|max:100',
            'team'          => 'nullable|string|max:100',
            'task_function' => 'nullable|string|max:100',
            'priority'      => 'nullable|string|in:low,medium,high',
            'start_date'    => 'nullable|date',
            'finish_date'   => 'nullable|date|after_or_equal:start_date',
            'sla'           => 'nullable|integer',
            'status'        => 'nullable|string|in:todo,in_progress,review,done',
            'progress'      => 'nullable|integer|min:0|max:100',
            'uom'           => 'nullable|string|max:50',
            'qty'           => 'nullable|integer',
            'vendor'        => 'nullable|string|max:255',
            'fdt_id'        => 'nullable|string|max:100',
            'assigned_to'   => 'nullable|exists:users,id',
            'remark'        => 'nullable|string|max:500',
        ]);

        $task->update([
            'title'         => $request->title,
            'description'   => $request->description,
            'division'      => $request->division,
            'team'          => $request->team,
            'Function'      => $request->task_function,
            'priority'      => $request->priority,
            'start_date'    => $request->start_date,
            'finish_date'   => $request->finish_date,
            'sla'           => $request->sla,
            'status'        => $request->status,
            'progress'      => $request->progress,
            'uom'           => $request->uom,
            'qty'           => $request->qty,
            'vendor'        => $request->vendor,
            'fdt_id'        => $request->fdt_id,
            'assigned_to'   => $request->assigned_to,
            'remark'        => $request->remark ?? $task->remark,
        ]);

        // hitung dan simpan ulang sla_status
        $task->sla_status = $this->getSlaStatus($task);
        $task->save();

        return response()->json([
            'message' => 'Task updated successfully',
            'task'    => $task->load(['creator', 'assignee']),
        ]);
    }

    // DELETE /api/tasks/{task}
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully']);
    }

    // PATCH /api/tasks/{task}/status
    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status'   => 'required|string|in:todo,in_progress,review,done',
            'progress' => 'nullable|integer|min:0|max:100',
            'remark'   => 'nullable|string|max:500',
        ]);

        $task->update([
            'status'   => $request->status,
            'progress' => $request->progress ?? $task->progress,
            'remark'   => $request->remark ?? $task->remark,
        ]);

        // hitung ulang sla_status
        $task->sla_status = $this->getSlaStatus($task);
        $task->save();

        return response()->json([
            'message' => 'Task status updated successfully',
            'task'    => $task->load(['creator', 'assignee']),
        ]);
    }

    /**
     * Hitung SLA Status
     */
    private function getSlaStatus(Task $task)
    {
        if (!$task->start_date || !$task->sla) {
            return 'unknown'; // kalau belum ada data SLA
        }

        $start = Carbon::parse($task->start_date);
        $due = $start->copy()->addDays($task->sla);

        // kalau sudah selesai
        if ($task->finish_date) {
            $finish = Carbon::parse($task->finish_date);
            return $finish->gt($due) ? 'overdue' : 'on_time';
        }

        // kalau belum selesai, cek hari ini
        return Carbon::now()->gt($due) ? 'overdue' : 'on_time';
    }
    public function updateRemark(Request $request, Task $task)
{
    // Validasi remark
    $request->validate([
        'remark' => 'nullable|string|max:255', // remark boleh kosong
    ]);

    $task->remark = $request->remark ?? '';
    $task->save();

    return response()->json([
        'message' => 'Remark updated successfully',
        'task' => $task
    ]);
}

}
