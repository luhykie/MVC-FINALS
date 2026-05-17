# Student Records MVC

Plain PHP MVC final examination project using Composer autoloading and PDO.

## Features

- Login and logout with PHP sessions
- Dashboard with student statistics and recent activity
- Student CRUD: create, read, update, delete
- Deleted student history
- Search and pagination
- Server-side validation and CSRF protection
- SQLite setup by default, with MySQL schema included

## Default Login

- Email: `admin@usjr.edu.ph`
- Password: `password`

## Setup

1. Install Composer dependencies:

   ```bash
   composer install
   ```

2. Create and seed the SQLite database:

   ```bash
   php scripts/setup.php
   ```

3. Start the local PHP server:

   ```bash
   php -S localhost:8000 -t public
   ```

4. Open:

   ```text
   http://localhost:8000
   ```

## MySQL Option

If your instructor requires MySQL, create a database named `mvc_finals`, import `database/mysql_schema.sql`, insert seed data using the same values from `database/seed.sql`, then update `config/config.php`:

```php
'driver' => 'mysql',
```

Set the MySQL host, username, and password in the same config file.

## Routes

| Method | Route | Purpose |
| --- | --- | --- |
| GET | `/` | Dashboard |
| GET | `/dashboard` | Dashboard |
| GET | `/login` | Login form |
| POST | `/login` | Login submit |
| POST | `/logout` | Logout |
| GET | `/students` | Student list with search and pagination |
| GET | `/students/create` | Create student form |
| POST | `/students` | Save new student |
| GET | `/students/{id}` | Student details |
| GET | `/students/{id}/edit` | Edit student form |
| POST | `/students/{id}/update` | Save student changes |
| POST | `/students/{id}/delete` | Move student to deleted history |
| GET | `/students/history` | Deleted student history |

## MVC And SOLID Notes

- `public/index.php` is the front controller. It registers routes and delegates requests to controllers.
- `App\Core\Router` owns route matching, including `{id}` parameter support.
- `App\Controllers\StudentController`, `DashboardController`, and `AuthController` handle request flow only.
- `App\Models\Student` and `User` contain database operations through PDO.
- `App\Core\View` renders templates from `views/`, keeping HTML out of controllers.
- Single Responsibility: routing, sessions, authentication, validation, database connection, rendering, controllers, and models are separated into focused classes.
- Open/Closed: new pages can be added by registering new routes and controller actions without changing router internals.
- Liskov Substitution: controllers share common behavior through `App\Core\Controller` and can be used consistently by the router.
- Interface Segregation: each core helper exposes a small purpose-specific API instead of one large utility class.
- Dependency Inversion: controllers depend on model/core abstractions and PDO access is centralized in `App\Core\Database`.
