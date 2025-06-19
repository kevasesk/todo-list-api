const API_BASE_URL = 'http://localhost/api';

const tasksList = document.getElementById('tasks');

const createForm = document.querySelector('[data-create-form]');
const formTitle = document.querySelector('[data-form-title]');

const buttonActionCreate = document.querySelector('[data-action-create]');
const buttonActionUpdate = document.querySelector('[data-action-update]');
const buttonActionCancel = document.querySelector('[data-action-cancel]');
const buttonActionChild = document.querySelector('[data-action-child]');

const taskIdField = document.getElementById('task-id');
const taskParentIdField = document.getElementById('task-parent-id');
const taskTitleField = document.getElementById('task-title');
const taskDescriptionField = document.getElementById('task-description');
const taskPriorityField = document.getElementById('task-priority');

const taskApp = document.getElementById('tasks-app');
const taskAppError = document.getElementById('tasks-app-error');
const userNameElement = document.querySelector('[data-user-name]');

let currentFilters = {
    status: '',
    title: '',
    description: '',
    priority: ''
};

let currentSorts = [
    {
        field: 'priority',
        direction: 'desc'
    }
];

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerText = message;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 2000);
}

function handleTaskCancel() {
    formTitle.innerHTML = 'Create New';

    buttonActionCreate.classList.remove('hidden');
    buttonActionCancel.classList.add('hidden');
    buttonActionUpdate.classList.add('hidden');

    taskTitleField.value = '';
    taskParentIdField.value = '';
    taskDescriptionField.value = '';
    taskPriorityField.value = '5';
}

async function handleTaskAddChild(event) {
    const parentId = event.target.dataset.id;
    taskParentIdField.value = parentId;
    taskTitleField.focus();
}

async function handleTaskEdit(event) {
    const taskId = event.target.dataset.id;

    await fetch(`${API_BASE_URL}/tasks/${taskId}`, {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'Accept': 'application/json'
        }
    }).then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Unknown error');
            });
        }

        return response.json();
    }).then(data => {
        taskIdField.value = data.id;
        taskParentIdField.value = data.parent_id;
        taskTitleField.value = data.title;
        taskDescriptionField.value = data.description;
        taskPriorityField.value = data.priority;

        buttonActionCreate.classList.add('hidden');
        buttonActionCancel.classList.remove('hidden');
        buttonActionUpdate.classList.remove('hidden');
        formTitle.innerHTML = 'Edit Task:';

    }).catch(error => {
        showNotification('There was a problem with the operation: ' + error.message);
    });
}

async function handleTaskUpdate(event) {
    event.stopPropagation();
    const formData = new FormData(createForm);
    const taskId = formData.get('id');
    const data = Object.fromEntries(formData.entries());

    await fetch(`${API_BASE_URL}/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    }).then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Unknown error');
            });
        }
        initTasks();
        handleTaskCancel();

        return response.json();
    }).catch(error => {
        console.log(error);
        showNotification('There was a problem with the fetch operation: ' + error.message);
    });
}

async function handleTaskCreate(event) {
    event.stopPropagation();
    const formData = new FormData(createForm);
    await fetch(`${API_BASE_URL}/tasks`, {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'Accept': 'application/json'
        },
        body: formData
    }).then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Unknown error');
            });
        }
        initTasks();

        taskTitleField.value = '';
        taskParentIdField.value = '';
        taskDescriptionField.value = '';
        taskPriorityField.value = '5';

        return response.json();
    }).catch(error => {
        showNotification('There was a problem with the fetch operation: ' + error.message);
    });
}

async function handleTaskCompletion(event) {
    const checkbox = event.target;
    const taskId = checkbox.dataset.id;
    const isCompleted = checkbox.checked;

    await fetch(`${API_BASE_URL}/tasks/${taskId}/complete`, {
        method: 'PATCH',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            status: isCompleted
        })
    }).then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Unknown error');
            });
        }
        initTasks();

        return response.json();
    }).then(data => {
        console.log('Task updated successfully:', data);
    }).catch(error => {
        showNotification('There was a problem with the fetch operation: ' + error.message);
        checkbox.checked = !isCompleted;
    });
}

async function handleTaskRemove(event) {
    const button = event.target;
    const taskId = button.dataset.id;

    await fetch(`${API_BASE_URL}/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
            'Accept': 'application/json'
        }
    }).then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Unknown error');
            });
        }
        initTasks();

        return response.json();
    }).catch(error => {
        showNotification('There was a problem with the fetch operation: ' + error.message);
    });
}

function handleTaskFilter(event) {
    const filter = event.target;
    currentFilters[filter.dataset.filter] = filter.value;
    initTasks();
}

function handleSort(event) {
    const field = event.target.dataset.sortableField;
    if (!field) return;

    const existingSortIndex = currentSorts.findIndex(s => s.field === field);

    if (existingSortIndex !== -1) {
        currentSorts[existingSortIndex].direction = currentSorts[existingSortIndex].direction === 'asc' ? 'desc' : 'asc';
    } else {
        if (!event.shiftKey) {
            currentSorts = [];
        }

        currentSorts.push({
            field,
            direction: 'asc'
        });
    }

    initTasks();
}

function renderTasks(allTasks) {
    let rowsHTML = '';

    if (allTasks.length === 0) {
        rowsHTML += '<tr><td colspan="11">No tasks found.</td></tr>';
    } else {
        allTasks.forEach(task => {
            let isChecked = task.status === 'done' ? 'checked' : '';
            let doneStatusClass = task.status === 'done' ? 'task-done' : '';

            rowsHTML += `<tr class="${doneStatusClass}">
                <td><input type="checkbox" data-action-complete data-id="${task.id}" ${isChecked}/></td>
                <td>${task.id}</td>
                <td>${task.parent_id || ''}</td>
                <td>${task.title}</td>
                <td>${task.description || ''}</td>
                <td>${task.priority}</td>
                <td data-completed-at>${task.completed_at || ''}</td>
                <td>${task.created_at || ''}</td>
                <td><button type="button" class="btn-icon" data-action-remove data-id="${task.id}" title="Remove">❌</button></td>
                <td><button type="button" class="btn-icon" data-action-edit data-id="${task.id}" title="Edit">✏️</button></td>
                <td><button type="button" class="btn-icon" data-action-child data-id="${task.id}" title="Add Child Task">➕</button></td>
            </tr>`;
        });
    }

    document.getElementById('tasks-rows').innerHTML = rowsHTML;

    // Update sort indicators
    document.querySelectorAll('.sort-indicator').forEach(indicator => {
        const field = indicator.parentElement.dataset.sortableField;
        const sort = currentSorts.find(s => s.field === field);
        if (sort) {
            const index = currentSorts.findIndex(s => s.field === field) + 1;
            const direction = sort.direction === 'asc' ? '▲' : '▼';
            indicator.textContent = `${direction} ${currentSorts.length > 1 ? index : ''}`;
        } else {
            indicator.textContent = '';
        }
    });

    userNameElement.innerHTML = localStorage.getItem('name');
    taskApp.classList.remove('hidden');
    taskAppError.classList.add('hidden');
}

async function initTasks() {
    const apiToken = localStorage.getItem('api_token');

    if (!apiToken) {
        console.warn('No API token found in localStorage. Redirecting to login.');
        window.location.href = '/';
    }

    const url = new URL(`${API_BASE_URL}/tasks`);

    for (const key in currentFilters) {
        if (currentFilters[key]) {
            url.searchParams.set(key, currentFilters[key]);
        }
    }

    if (currentSorts.length > 0) {
        const sortByString = currentSorts.map(s => `${s.field}:${s.direction}`).join(',');
        url.searchParams.set('sort_by', sortByString);
    }

    try {
        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + apiToken,
                'Accept': 'application/json'
            }
        });

        if (response.status === 401) {
            showNotification('Fetch tasks failed: 401 Unauthorized. Token might be invalid or expired.');
            localStorage.removeItem('api_token');
            window.location.href = '/';
        }

        if (!response.ok) {
            const errorBody = await response.json().catch(() => ({ message: 'Unknown error', status: response.status }));
            showNotification(`Fetch tasks failed with status ${response.status}: ${errorBody.message || errorBody}`);
            return;
        }

        const tasks = await response.json();
        renderTasks(tasks);

    } catch (error) {
        showNotification('There was a problem with the fetch operation (network or CORS): ' + error.message);
    }
}

initTasks();

document.addEventListener('click', function(event) {
    if (event.target.matches('[data-action-complete]')) {
        handleTaskCompletion(event);
    }
    if (event.target.matches('[data-action-remove]')) {
        handleTaskRemove(event);
    }
    if (event.target.matches('[data-action-create]')) {
        handleTaskCreate(event);
    }
    if (event.target.matches('[data-action-edit]')) {
        handleTaskEdit(event);
    }
    if (event.target.matches('[data-action-child]')) {
        handleTaskAddChild(event);
    }
    if (event.target.matches('[data-action-update]')) {
        handleTaskUpdate(event);
    }
    if (event.target.matches('[data-action-cancel]')) {
        handleTaskCancel(event);
    }
    if (event.target.matches('[data-sortable-field]')) {
        handleSort({ target: event.target, shiftKey: event.shiftKey });
    }
});

document.addEventListener('input', function(event) {
    if (event.target.matches('[data-filter]')) {
        handleTaskFilter(event);
    }
});



