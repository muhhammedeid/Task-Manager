<?php

use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('can create a task', function () {
    $response = postJson('/api/tasks', [
        'title' => 'Test Task',
        'description' => 'Testing task creation',
        'status' => 'pending',
        'due_date' => '2026-09-30',
    ]);
    $response->assertCreated();
});