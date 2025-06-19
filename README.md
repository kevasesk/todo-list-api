# Todo List API
## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

Before you begin, ensure you have the following software installed on your machine:

* [Docker](https://www.docker.com/products/docker-desktop)
* [Composer](https://getcomposer.org/)
* I used Docker version 28.2.2, build e6534b4
* I used Ubuntu 22.04.5 LTS

### Installation

Follow these steps to set up your development environment.

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/kevasesk/todo-list-api.git
    cd todo-list-api
    ```

2.  **Build and start the Docker containers:**
    ```bash
    docker compose up -d --remove-orphans
    ```


3.  **Install the dependencies:**
    ```bash
    docker compose exec php composer install
    docker compose exec php npm install
    docker compose exec php npm run build
    ```

5. **Run database migrations:**
    ```bash
    docker compose exec php php artisan migrate
    ```

7.  **Access the application:**
    You should now be able to access your application in your web browser. Typically, this will be at [http://localhost](http://localhost).

---

## Usage

### Starting and Stopping the Environment

* **To start the Docker containers** in detached (background) mode:
    ```bash
    docker compose up -d
    ```

* **To stop the Docker containers:**
    ```bash
    docker compose stop
    ```

On the main page http://localhost there is a two forms: Login and Registration.
If you have already login - you can just login using credentials.
If not, you can register user.

After successed Login or Register - you will redirect to '/tasks' dashboard.
On this page there is a name of user, Logout button, form to create/edit task 
and a table with filters and sorts for tasks of the user.

For creating a Child of task - click "Add Child Task" button, it will set Parent ID to form, then create a new task.
For multi-sort, click on column with holding Shift.


### License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
