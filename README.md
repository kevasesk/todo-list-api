# Todo List API
## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

Before you begin, ensure you have the following software installed on your machine:

* [Docker](https://www.docker.com/products/docker-desktop)
* [Composer](https://getcomposer.org/)

### Installation

Follow these steps to set up your development environment.

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/kevasesk/todo-list-api.git](https://github.com/kevasesk/todo-list-api.git)
    cd todo-list-api
    ```

2.  **Build and start the Docker containers:**
    This command will build the necessary Docker images and start the services (like your web server, database, etc.) in detached mode.
    ```bash
    docker compose up -d --remove-orphans
    ```

3.  **Install Laravel in a new container:**
    If this is a brand new project setup, you'll need to use Composer to install the Laravel framework into the correct directory within your container.

    * First, ensure you have the Laravel installer available globally via Composer:
        ```bash
        composer global require laravel/installer
        ```
    * Then, execute the `create-project` command to install Laravel in the `/var/www/html` volume, which is mounted into your PHP container:
        ```bash
        docker compose exec php composer create-project --prefer-dist laravel/laravel .
        ```
    ***Note:*** *If you have cloned a repository that already contains a Laravel project, you can skip the two commands above and instead just install the dependencies:*
    ```bash
    docker compose exec php composer install
    ```


4.  **Set up the environment file:**
    Copy the example environment file. Laravel uses this file for all configuration variables.


    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=database
    DB_USERNAME=user
    DB_PASSWORD=password

5. **Run database migrations:**
    This will create all the necessary tables in your database as defined in your migration files.
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

### Running Artisan Commands

To run any `php artisan` command, you need to execute it inside the `app` container:

```bash
docker compose exec php php artisan <your-command-here>
