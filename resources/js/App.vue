<script setup>
import { ref, onMounted } from 'vue';
import { getTasks, createTask } from './api/tasks';

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
                {{ task.status }}
                -
                {{ task.due_date }}
            </li>
        </ul>

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