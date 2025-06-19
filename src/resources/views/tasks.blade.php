<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Todo App</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite([
                'resources/css/tasks.css',
                'resources/css/notification.css',

                'resources/js/auth.js',
                'resources/js/tasks.js',
            ])
        @endif
    </head>
    <body>
        <div id="tasks-app" class="hidden">
            <div class="head">
                <h1>Welcome, <span data-user-name></span>!</h1>
                <button type="button" data-user-logout-button>Logout</button>
            </div>


            <form data-create-form method="POST">
                <h2 data-form-title>Add new task:</h2>

                <input type="text" id="task-id" name="id" class="hidden">
                <input type="text" id="task-parent-id" name="parent_id" class="hidden">

                <label for="task-title">Title:</label>
                <input type="text" id="task-title" name="title">

                <label for="task-priority">Priority:</label>
                <input type="number" id="task-priority" name="priority" value="5" min="1" max="5">

                <label for="task-description">Description:</label>
                <textarea id="task-description" name="description" rows="5"></textarea>

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

            <h2>Tasks:</h2>
            <div id="tasks">
                <table>
                    <thead>
                    <tr class="table-header">
                        <th>Status</th>
                        <th>Id</th>
                        <th>Parent</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th data-sortable-field="priority" class="sortable">Priority <span class="sort-indicator"></span></th>
                        <th data-sortable-field="completed_at" class="sortable">Completed At <span class="sort-indicator"></span></th>
                        <th data-sortable-field="created_at" class="sortable">Created At <span class="sort-indicator"></span></th>
                        <th colspan="3">Actions</th>
                    </tr>
                    <tr>
                        <th>
                            <select data-filter="status">
                                <option value=""></option>
                                <option value="todo">Todo</option>
                                <option value="done">Done</option>
                            </select>
                        </th>
                        <th></th>
                        <th></th>
                        <th><input type="text" data-filter="title" /></th>
                        <th><input type="text" data-filter="description" /></th>
                        <th><input type="number" min="1" max="5" data-filter="priority" /></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="tasks-rows"></tbody>
                </table>
            </div>
        </div>
        <p id="tasks-app-error">Please login to continue.</p>
    </body>
</html>
