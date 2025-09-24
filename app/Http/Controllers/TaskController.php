<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // GET /api/tasks
    public function index()
{
    $user = Auth::user();
    $tasks = Task::with(['assignee', 'creator'])->get();
    return response()->json($tasks);
}


    // POST /api/tasks
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'division'    => 'required|string|max:100',
            'team'        => 'nullable|string|max:100',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task = Task::create([
            'title'       => $request->title,
            'description' => $request->description,
            'division'    => $request->division,
            'team'        => $request->team,
            'status'      => 'todo',
            'created_by'  => Auth::id(),
            'assigned_to' => $request->assigned_to,
        ]);

        return response()->json($task, 201);
    }

    // GET /api/tasks/{task}
    public function show(Task $task)
    {
        return response()->json($task->load(['creator', 'assignee']));
    }

    // PUT /api/tasks/{task}
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'division'    => 'sometimes|required|string|max:100',
            'team'        => 'nullable|string|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'status'      => 'nullable|in:todo,in_progress,review,done',
        ]);

        $task->update($request->all());

        return response()->json($task);
    }

    // DELETE /api/tasks/{task}
    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }

    // PATCH /api/tasks/{task}/status
    public function updateStatus(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|in:todo,in_progress,review,done',
        ]);

        $task->status = $request->status;
        $task->save();

        return response()->json($task);
    }
}
