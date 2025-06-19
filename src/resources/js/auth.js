
function handleUserRegister(event) {
    event.preventDefault();

    const form = document.querySelector('[data-user-register-form]');

    const name = form.querySelector('#name').value;
    const password = form.querySelector('#password_register').value;
    const passwordConfirmation = form.querySelector('#password_confirmation').value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (password !== passwordConfirmation) {
        console.error('Registration error: Passwords do not match.');
        alert('Passwords do not match. Please try again.');
        return;
    }

    fetch('/api/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            name: name,
            password: password,
            password_confirmation: passwordConfirmation // Laravel's default expects this for validation
        })
    }).then(response => {
        if (response.ok) {
            return response.json();
        }
        // If the response is not OK, parse the error and throw it
        return response.json().then(errorData => {
            // Check for specific Laravel validation errors or a general message
            const errorMessage = errorData.message || (errorData.errors ? Object.values(errorData.errors).flat().join('\n') : 'Registration failed.');
            throw new Error(errorMessage);
        });
    }).then(data => {
        console.log('Registration successful:', data);

        if (data.token) {
            localStorage.setItem('api_token', data.token);
            localStorage.setItem('name', data.name);
            console.log('API token stored:', data.token);
        }

        window.location.href = '/tasks';
    }).catch(error => {
        console.error('Registration error:', error.message);
        alert('Registration failed: ' + error.message);
    });
}

function handleUserLogin(event) {
    event.preventDefault();

    const form = document.querySelector('[data-user-login-form]');

    const name = form.querySelector('#name_login').value;
    const password = form.querySelector('#password_login').value;
    const remember = form.querySelector('#remember').checked;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/api/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            name: name,
            password: password,
            remember: remember
        })
    }).then(response => {
        if (response.ok) {
            return response.json();
        }

        return response.json().then(errorData => {
            throw new Error(errorData.message || 'Login failed.');
        });
    }).then(data => {
        console.log('Login successful:', data);

        if (data.token) {
            localStorage.setItem('api_token', data.token);
            localStorage.setItem('name', data.name);
            console.log('API token stored:', data.token);
        }

        window.location.href = '/tasks';
    }).catch(error => {
        console.error('Login error:', error.message);
    });
}

function handleUserLogout() {
    fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
        }
    })
    localStorage.removeItem('api_token');
    localStorage.removeItem('name');
    window.location.href = '/';
}

document.addEventListener('click', function(event) {
    if (event.target.matches('[data-user-register-button]')) {
        handleUserRegister(event);
    }

    if (event.target.matches('[data-user-login-button]')) {
        handleUserLogin(event);
    }

    if (event.target.matches('[data-user-logout-button]')) {
        handleUserLogout(event);
    }
});
