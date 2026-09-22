<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return response()->json(['data' => $tasks], 200);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->validated());
        return response()->json(
            ['data' => $task ,
             'message' => 'Task created successfully'
            ], 201);
    }

    public function show(Task $task)
    {
        return response()->json(['data' => $task], 200);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());
        return response()->json(['data' => $task, 'message' => 'Task updated successfully'], 200);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
