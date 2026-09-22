<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }

    public function store(StoreTaskRequest $request)
    {
        // Code to create a new task
    }

    public function show(Task $task)
    {
        // Code to retrieve and return a specific task
    }

    public function update(StoreTaskRequest $request, Task $task)
    {
        // Code to update a specific task
    }

    public function destroy(Task $task)
    {
        // Code to delete a specific task
    }
}
