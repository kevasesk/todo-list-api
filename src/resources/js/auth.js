// DOM Elements
const forms = {
    login: document.querySelector('[data-user-login-form]'),
    register: document.querySelector('[data-user-register-form]')
};

const switches = {
    toRegister: document.querySelector('[data-switch-to-register]'),
    toLogin: document.querySelector('[data-switch-to-login]')
};

// Utility Functions
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
}

function getAuthHeaders() {
    return {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken()
    };
}

function getBearerHeaders() {
    return {
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + localStorage.getItem('api_token')
    };
}

function showError(message) {
    console.error(message);
    alert(message);
}

function storeUserData(data) {
    if (data.token) {
        localStorage.setItem('api_token', data.token);
        localStorage.setItem('name', data.name);
        console.log('API token stored:', data.token);
    }
}

function clearUserData() {
    localStorage.removeItem('api_token');
    localStorage.removeItem('name');
}

function redirectToTasks() {
    window.location.href = '/tasks';
}

function redirectToHome() {
    window.location.href = '/';
}

function getFormData(form) {
    const formData = new FormData(form);
    return Object.fromEntries(formData.entries());
}

function extractErrorMessage(errorData) {
    if (errorData.message) {
        return errorData.message;
    }

    if (errorData.errors) {
        return Object.values(errorData.errors).flat().join('\n');
    }

    return 'Operation failed.';
}

// Generic API request function
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, options);

        if (response.ok) {
            return response.json();
        }

        const errorData = await response.json().catch(() => ({ message: 'Unknown error' }));
        throw new Error(extractErrorMessage(errorData));

    } catch (error) {
        throw error;
    }
}

// Form validation
function validateRegistration(data) {
    if (data.password !== data.password_confirmation) {
        throw new Error('Passwords do not match. Please try again.');
    }
}

// Authentication handlers
async function handleUserRegister(event) {
    event.preventDefault();

    try {
        const data = getFormData(forms.register);
        validateRegistration(data);

        const response = await apiRequest('/api/register', {
            method: 'POST',
            headers: getAuthHeaders(),
            body: JSON.stringify(data)
        });

        console.log('Registration successful:', response);
        storeUserData(response);
        redirectToTasks();

    } catch (error) {
        showError('Registration failed: ' + error.message);
    }
}

async function handleUserLogin(event) {
    event.preventDefault();

    try {
        const data = getFormData(forms.login);

        const response = await apiRequest('/api/login', {
            method: 'POST',
            headers: getAuthHeaders(),
            body: JSON.stringify(data)
        });

        console.log('Login successful:', response);
        storeUserData(response);
        redirectToTasks();

    } catch (error) {
        showError('Login failed: ' + error.message);
    }
}

async function handleUserLogout() {
    try {
        // Attempt to logout on server (don't wait for response)
        fetch('/api/logout', {
            method: 'POST',
            headers: getBearerHeaders()
        }).catch(() => {
            // Ignore logout API errors - clear local data anyway
        });

    } finally {
        // Always clear local data and redirect
        clearUserData();
        redirectToHome();
    }
}

// Form switching utilities
function switchToForm(showForm, hideForm, focusSelector) {
    hideForm.classList.add('hidden');
    showForm.classList.remove('hidden');

    const focusElement = showForm.querySelector(focusSelector);
    if (focusElement) {
        focusElement.focus();
    }
}

function initFormSwitching() {
    if (!forms.login || !forms.register || !switches.toRegister || !switches.toLogin) {
        return;
    }

    switches.toRegister.addEventListener('click', () => {
        switchToForm(forms.register, forms.login, 'input[name="name"]');
    });

    switches.toLogin.addEventListener('click', () => {
        switchToForm(forms.login, forms.register, 'input[name="name"]');
    });
}

// Event handlers map
const authHandlers = {
    'data-user-register-button': handleUserRegister,
    'data-user-login-button': handleUserLogin,
    'data-user-logout-button': handleUserLogout
};

// Event listeners
document.addEventListener('click', function(event) {
    Object.entries(authHandlers).forEach(([selector, handler]) => {
        if (event.target.matches(`[${selector}]`)) {
            handler(event);
        }
    });
});

document.addEventListener('DOMContentLoaded', initFormSwitching);
