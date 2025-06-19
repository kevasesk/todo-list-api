<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Todo App</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite([
                'resources/css/app.css',
                'resources/js/auth.js',
                'resources/js/tasks.js',
            ])
        @endif
    </head>
    <body>
        <div id="tasks-app" class="hidden">
            <p>Welcome, <span data-user-name></span>!</p>
            <button type="button" data-user-logout-button>Logout</button>

            <form data-create-form method="POST">
                <h4 data-form-title>Add new task:</h4>

                <input type="text" id="task-id" name="id" class="hidden"><br>
                <input type="text" id="task-parent-id" name="parent_id" class="hidden"><br>

                <label for="task-title">Title:</label><br>
                <input type="text" id="task-title" name="title"><br/>

                <label for="task-priority">Priority:</label><br>
                <input type="number" id="task-priority" name="priority" value="5" min="1" max="5"><br/>

                <label for="task-description">Description:</label><br>
                <textarea id="task-description" name="description" rows="5"></textarea><br/>

                <button type="button" data-action-create>
                    Create New
                </button>
                <button type="button" data-action-update class="hidden">
                    Update
                </button>
                <button type="button" data-action-cancel class="hidden">
                    Cancel
                </button>
            </form>

            Tasks:<div id="tasks"></div><br/>
        </div>
        <p id="tasks-app-error">Please login to continue.</p>
    </body>
</html>
