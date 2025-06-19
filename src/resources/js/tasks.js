const API_BASE_URL = 'http://localhost/api';

// DOM Elements
const tasksList = document.getElementById('tasks');
const createForm = document.querySelector('[data-create-form]');
const formTitle = document.querySelector('[data-form-title]');

const buttons = {
    create: document.querySelector('[data-action-create]'),
    update: document.querySelector('[data-action-update]'),
    cancel: document.querySelector('[data-action-cancel]'),
    child: document.querySelector('[data-action-child]')
};

const fields = {
    id: document.getElementById('task-id'),
    parentId: document.getElementById('task-parent-id'),
    title: document.getElementById('task-title'),
    description: document.getElementById('task-description'),
    priority: document.getElementById('task-priority')
};

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

// Utility Functions
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerText = message;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 2000);
}

function getAuthHeaders() {
    return {
        'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
        'Accept': 'application/json'
    };
}

function getJsonHeaders() {
    return {
        ...getAuthHeaders(),
        'Content-Type': 'application/json'
    };
}

function clearForm() {
    fields.title.value = '';
    fields.parentId.value = '';
    fields.description.value = '';
    fields.priority.value = '5';
}

function setFormMode(mode) {
    const modes = {
        create: {
            title: 'Create New',
            showButtons: ['create'],
            hideButtons: ['update', 'cancel']
        },
        edit: {
            title: 'Edit Task:',
            showButtons: ['update', 'cancel'],
            hideButtons: ['create']
        }
    };

    const config = modes[mode];
    formTitle.innerHTML = config.title;

    config.showButtons.forEach(btn => buttons[btn].classList.remove('hidden'));
    config.hideButtons.forEach(btn => buttons[btn].classList.add('hidden'));
}

function populateForm(task) {
    fields.id.value = task.id;
    fields.parentId.value = task.parent_id;
    fields.title.value = task.title;
    fields.description.value = task.description;
    fields.priority.value = task.priority;
}

// Generic API function
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, options);

        if (response.status === 401) {
            showNotification('Authentication failed. Token might be invalid or expired.');
            localStorage.removeItem('api_token');
            window.location.href = '/';
            return;
        }

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
            throw new Error(errorData.message || 'Unknown error');
        }

        return response.json();
    } catch (error) {
        showNotification('There was a problem with the operation: ' + error.message);
        throw error;
    }
}

// Task Operations
function handleTaskCancel() {
    setFormMode('create');
    clearForm();
}

async function handleTaskAddChild(event) {
    const parentId = event.target.dataset.id;
    fields.parentId.value = parentId;
    fields.title.focus();
}

async function handleTaskEdit(event) {
    const taskId = event.target.dataset.id;

    try {
        const task = await apiRequest(`${API_BASE_URL}/tasks/${taskId}`, {
            method: 'GET',
            headers: getAuthHeaders()
        });

        populateForm(task);
        setFormMode('edit');
    } catch (error) {
        // Error already handled in apiRequest
    }
}

async function handleTaskUpdate(event) {
    event.stopPropagation();
    const formData = new FormData(createForm);
    const taskId = formData.get('id');
    const data = Object.fromEntries(formData.entries());

    try {
        await apiRequest(`${API_BASE_URL}/tasks/${taskId}`, {
            method: 'PUT',
            headers: getJsonHeaders(),
            body: JSON.stringify(data)
        });

        initTasks();
        handleTaskCancel();
    } catch (error) {
        // Error already handled in apiRequest
    }
}

async function handleTaskCreate(event) {
    event.stopPropagation();
    const formData = new FormData(createForm);

    try {
        await apiRequest(`${API_BASE_URL}/tasks`, {
            method: 'POST',
            headers: getAuthHeaders(),
            body: formData
        });

        initTasks();
        clearForm();
    } catch (error) {
        // Error already handled in apiRequest
    }
}

async function handleTaskCompletion(event) {
    const checkbox = event.target;
    const taskId = checkbox.dataset.id;
    const isCompleted = checkbox.checked;

    try {
        await apiRequest(`${API_BASE_URL}/tasks/${taskId}/complete`, {
            method: 'PATCH',
            headers: getJsonHeaders(),
            body: JSON.stringify({ status: isCompleted })
        });

        initTasks();
    } catch (error) {
        checkbox.checked = !isCompleted; // Revert on error
    }
}

async function handleTaskRemove(event) {
    const taskId = event.target.dataset.id;

    try {
        await apiRequest(`${API_BASE_URL}/tasks/${taskId}`, {
            method: 'DELETE',
            headers: getAuthHeaders()
        });

        initTasks();
    } catch (error) {
        // Error already handled in apiRequest
    }
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
        currentSorts[existingSortIndex].direction =
            currentSorts[existingSortIndex].direction === 'asc' ? 'desc' : 'asc';
    } else {
        if (!event.shiftKey) {
            currentSorts = [];
        }
        currentSorts.push({ field, direction: 'asc' });
    }

    initTasks();
}

function createTaskRow(task) {
    const isChecked = task.status === 'done' ? 'checked' : '';
    const doneStatusClass = task.status === 'done' ? 'task-done' : '';

    return `<tr class="${doneStatusClass}">
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
}

function updateSortIndicators() {
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
}

function renderTasks(allTasks) {
    const rowsHTML = allTasks.length === 0
        ? '<tr><td colspan="11">No tasks found.</td></tr>'
        : allTasks.map(createTaskRow).join('');

    document.getElementById('tasks-rows').innerHTML = rowsHTML;
    updateSortIndicators();

    userNameElement.innerHTML = localStorage.getItem('name');
    taskApp.classList.remove('hidden');
    taskAppError.classList.add('hidden');
}

function buildTasksUrl() {
    const url = new URL(`${API_BASE_URL}/tasks`);

    // Add filters
    Object.entries(currentFilters).forEach(([key, value]) => {
        if (value) url.searchParams.set(key, value);
    });

    // Add sorting
    if (currentSorts.length > 0) {
        const sortByString = currentSorts.map(s => `${s.field}:${s.direction}`).join(',');
        url.searchParams.set('sort_by', sortByString);
    }

    return url.toString();
}

async function initTasks() {
    const apiToken = localStorage.getItem('api_token');

    if (!apiToken) {
        console.warn('No API token found in localStorage. Redirecting to login.');
        window.location.href = '/';
        return;
    }

    try {
        const tasks = await apiRequest(buildTasksUrl(), {
            method: 'GET',
            headers: getAuthHeaders()
        });

        renderTasks(tasks);
    } catch (error) {
        // Error already handled in apiRequest
    }
}

// Event Handlers Map
const actionHandlers = {
    'data-action-complete': handleTaskCompletion,
    'data-action-remove': handleTaskRemove,
    'data-action-create': handleTaskCreate,
    'data-action-edit': handleTaskEdit,
    'data-action-child': handleTaskAddChild,
    'data-action-update': handleTaskUpdate,
    'data-action-cancel': handleTaskCancel,
    'data-sortable-field': (event) => handleSort({ target: event.target, shiftKey: event.shiftKey })
};

// Event Listeners
document.addEventListener('click', function(event) {
    Object.entries(actionHandlers).forEach(([selector, handler]) => {
        if (event.target.matches(`[${selector}]`)) {
            handler(event);
        }
    });
});

document.addEventListener('input', function(event) {
    if (event.target.matches('[data-filter]')) {
        handleTaskFilter(event);
    }
});

// Initialize
initTasks();
