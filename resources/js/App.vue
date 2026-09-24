<script setup>
import { ref, onMounted } from 'vue';
import { getTasks, createTask , updateTask , deleteTask } from './api/tasks';

const tasks = ref([]);
const loading = ref(true);
const loadingError = ref('');
const taskForm = ref({
    title: '',
    description: '',
    status: 'pending',
    due_date: ''
});
const formError = ref('');
const editingTask = ref(null);
const actionError = ref('');

async function fetchTasks() {
    loading.value = true;
    loadingError.value = '';

    try {
        const response = await getTasks();
        tasks.value = response.data;
    } catch (err) {
        loadingError.value = err.message;
    } finally {
        loading.value = false;
    }
}

async function createNewTask() {
    formError.value = '';

    if (!taskForm.value.title.trim()) {
        formError.value = 'Title is required';
        return;
    }
    if(!taskForm.value.due_date) {
        formError.value = 'Due date is required';
        return;
    }
    if(taskForm.value.due_date < new Date().toISOString().split('T')[0]) {
        formError.value = 'Due date cannot be in the past';
        return;
    }
    try {
        await createTask(taskForm.value);
        await fetchTasks();
        taskForm.value = {
            title: '',
            description: '',
            status: 'pending',
            due_date: ''
        };
    } catch (err) {
        formError.value = err.message;
    }
}
function startEdit(task) {
    actionError.value = '';
    editingTask.value = {
        id: task.id,
        title: task.title,
        description: task.description,
        status: task.status,
        due_date: task.due_date,
    };
    if(editingTask.value.status === 'done') {
        actionError.value = 'Cannot edit a task that is marked as done';
        editingTask.value = null;
    }
}
async function saveEditedTask() {
    actionError.value = '';

     if(editingTask.value.due_date < new Date().toISOString().split('T')[0]) {
        actionError.value = 'Due date cannot be in the past';
        return;
    }

    try {
        await updateTask(editingTask.value.id, {
            status: editingTask.value.status,
            due_date: editingTask.value.due_date,
        });
        await fetchTasks();
        editingTask.value = null;
        actionError.value = null;
    } 
    catch (err) {
        actionError.value = err.message;
    }
}
async function handleDeleteTask(task) {
    const confirmed = window.confirm(
        `Are you sure you want to delete "${task.title}"?`
    );

    if (!confirmed) {
        return;
    }

    try {
        await deleteTask(task.id);
        await fetchTasks();
    } catch (err) {
        actionError.value = err.message;
    }
}

onMounted(() => {
    fetchTasks();
});
</script>

<template>
    <main class="page">
        <div class="container">
            <header class="page-header">
                <div>
                    <h1>Task Manager</h1>
                    <p>Manage your team's tasks in one place.</p>
                </div>
            </header>

            <p v-if="loading" class="state-message">
                Loading tasks...
            </p>

            <p v-else-if="loadingError" class="error-message">
                {{ loadingError }}
            </p>

            <section v-else class="content-grid">
                <!-- Tasks -->
                <div class="card">
                    <div class="card-header">
                        <h2>Tasks</h2>
                        <span class="task-count">{{ tasks.length }}</span>
                    </div>

                    <p v-if="tasks.length === 0" class="empty-state">
                        No tasks found. Create your first task.
                    </p>

                    <div v-else class="task-list">
                        <article
                            v-for="task in tasks"
                            :key="task.id"
                            class="task-item"
                        >
                            <div class="task-main">
                                <div class="task-top">
                                    <h3>{{ task.title }}</h3>

                                    <span
                                        class="status"
                                        :class="`status-${task.status}`"
                                    >
                                        {{ task.status.replace('_', ' ') }}
                                    </span>
                                </div>

                                <p class="description">
                                    {{ task.description || 'No description' }}
                                </p>

                                <p class="due-date">
                                    Due: {{ task.due_date }}
                                </p>
                            </div>

                            <div class="task-actions">
                                <button
                                    class="btn btn-secondary"
                                    @click="startEdit(task)"
                                >
                                    Edit
                                </button>

                                <button
                                    class="btn btn-danger"
                                    @click="handleDeleteTask(task)"
                                >
                                    Delete
                                </button>
                            </div>
                        </article>
                    </div>

                    <p v-if="actionError" class="error-message">
                        {{ actionError }}
                    </p>
                </div>

                <!-- Create -->
                <div class="card">
                    <h2>Create Task</h2>

                    <form
                        class="task-form"
                        @submit.prevent="createNewTask"
                    >
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input
                                id="title"
                                v-model="taskForm.title"
                                type="text"
                                placeholder="Task title"
                            >
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea
                                id="description"
                                v-model="taskForm.description"
                                placeholder="Task description"
                                rows="4"
                            ></textarea>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select
                                id="status"
                                v-model="taskForm.status"
                            >
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Done</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="due_date">Due Date</label>
                            <input
                                id="due_date"
                                v-model="taskForm.due_date"
                                type="date"
                            >
                        </div>

                        <p v-if="formError" class="error-message">
                            {{ formError }}
                        </p>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Create Task
                        </button>
                    </form>
                </div>
            </section>

            <!-- Edit -->
            <div v-if="editingTask" class="modal-backdrop">
                <div class="modal">
                    <div class="modal-header">
                        <h2>Edit Task</h2>

                        <button
                            class="close-button"
                            @click="editingTask = null"
                        >
                            ×
                        </button>
                    </div>

                    <div class="task-form">
                        <div class="form-group">
                            <label>Status</label>

                            <select v-model="editingTask.status">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="done">Done</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Due Date</label>

                            <input
                                v-model="editingTask.due_date"
                                type="date"
                            >
                        </div>

                        <p v-if="actionError" class="error-message">
                            {{ actionError }}
                        </p>

                        <div class="modal-actions">
                            <button
                                class="btn btn-secondary"
                                @click="editingTask = null"
                            >
                                Cancel
                            </button>

                            <button
                                class="btn btn-primary"
                                @click="saveEditedTask"
                            >
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>