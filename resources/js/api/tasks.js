export async function getTasks() {
    const response = await fetch('/api/tasks');

    if (!response.ok) {
        throw new Error('Failed to fetch tasks');
    }
    return response.json();
}

export async function createTask(taskdata) {
    const response = await fetch('/api/tasks', {
        method : 'POST',
        headers : {
            'Content-Type' : 'application/json',
            'Accept' : 'application/json'
        },
        body : JSON.stringify(taskdata)
    });
    if (!response.ok) {
        throw new Error('Failed to create task');
    }
    return response.json();
}