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
const editError = ref('');

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
    editingTask.value = {
        id: task.id,
        title: task.title,
        description: task.description,
        status: task.status,
        due_date: task.due_date,
    };
}
async function saveEditedTask() {
    editError.value = '';

    if (!editingTask.value.due_date) {
        editError.value = 'Due date is required';
        return;
    }

    try {
        await updateTask(editingTask.value.id, {
            description: editingTask.value.description,
            status: editingTask.value.status,
            due_date: editingTask.value.due_date,
        });

        await fetchTasks();

        editingTask.value = null;
    } catch (err) {
        editError.value = err.message;
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
        error.value = err.message;
    }
}

onMounted(() => {
    fetchTasks();
});
</script>

<template>
    <main>
        <h1>Task Manager</h1>
        <p v-if="loading">
            Loading tasks...
        </p>
        <p v-else-if="loadingError">
            {{ loadingError }}
        </p>
        <p v-else-if="tasks.length === 0">
            No tasks found.
        </p>
        <ul v-else>
            <li
                v-for="task in tasks"
                :key="task.id">
                <strong>{{ task.title }}</strong>
                -
                {{ task.description }}
                -
                {{ task.status }}
                -
                {{ task.due_date }}
                -
                <button @click="startEdit(task)"> Edit </button>
            
                <button @click="handleDeleteTask(task)"> Delete </button>
            </li>
        </ul>
        <div v-if="editingTask">
            <h3>Edit Task</h3>
            <select v-model="editingTask.status">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Done</option>
            </select>
            <input v-model="editingTask.due_date" type="date" >
            <button @click="saveEditedTask"> Save </button>
            <button @click="editingTask = null"> Cancel </button>
        </div>
        <form @submit.prevent="createNewTask">
            <div>   
                <label>Title:</label>
                <input
                v-model="taskForm.title" type="text"
                placeholder="Task Title"
            />
            </div>
            <textarea
                v-model="taskForm.description"
                placeholder="Task Description"
            ></textarea>
            <select v-model="taskForm.status">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Completed</option>
            </select>
            <input
                v-model="taskForm.due_date" type="date"
            />
            <button type="submit">Create Task</button>
            <p v-if="formError" style="color: red;">{{ formError }}</p>
        </form>
    </main>
</template>