# Task Management API (Laravel)

A simple Laravel-based REST API for managing hierarchical tasks with subtasks, filtering, sorting, and status management.

---

## Features

- User authentication via Laravel Sanctum (token-based).
- CRUD operations for tasks with unlimited subtask nesting.
- Filtering tasks by status, priority, and full-text search by title/description.
- Sorting tasks by multiple fields (`created_at`, `completed_at`, `priority`).
- Task status management, including marking tasks as done with validation that all subtasks are done.
- Authorization checks to ensure users can only access their own tasks.
- API documentation with OpenAPI (Swagger) annotations.
- Recursive tree building of tasks with subtasks included in responses.

---

## Requirements

- Docker
- Docker Compose

---

## Setup (with Docker Compose)

1. **Clone the repository**

```bash
git clone https://github.com/alex-shimansky/laravel-task-api.git
cd laravel-task-api
```

2. **Configure environment variables**

Copy .env.example to .env:

```bash
cp .env.example .env
```

Set database credentials in .env:
<pre lang="md">
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=drum
DB_USERNAME=drum
DB_PASSWORD=drum
</pre>

3. **Start Docker containers**

```bash
docker compose up -d --build
```

This will start:

Laravel app (laravel_app)  
Nginx (nginx_web) → http://localhost:8000  
MySQL (mysql_db)  

5. **Install dependencies, run migrations and seeders**

```bash
docker compose exec app composer install
docker compose exec app migrate --seed
```

This will create the necessary tables and seed initial users and tasks.

---

## API Documentation

The Swagger/OpenAPI documentation is available at:

`http://localhost:8000/api/documentation`

You can open this URL in the browser to explore and test all API endpoints interactively.

---

## Authentication

Login endpoint is available.

Use Sanctum tokens for API authentication.

---

## API Endpoints

| Method | URI                   | Description                    |
|--------|------------------------|--------------------------------|
| POST   | /api/login             | User login                     |
| POST   | /api/logout            | User logout                    |
| GET    | /api/tasks             | List tasks with filters & sort |
| POST   | /api/tasks             | Create new task                |
| GET    | /api/tasks/{id}        | Get task by ID with subtasks   |
| PUT    | /api/tasks/{id}        | Update task                    |
| DELETE | /api/tasks/{id}        | Delete task                    |
| POST   | /api/tasks/{id}/done   | Mark task as done              |

---

## Filtering and Sorting (Tasks List)

Filters:

status: Task status enum (e.g. todo, done)
priority: Task priority enum integer
search: Full-text search on title or description

Sorting:

Pass sort query parameter with multiple fields, e.g. ?sort=priority:desc,created_at:asc

Supported fields: created_at, completed_at, priority

---

## Data Models

Task

<pre lang="md">
id (int)
user_id (int) — owner
assignee_id (int|null)
parent_id (int|null) — for subtasks hierarchy
title (string)
description (text|null)
priority (int)
status (enum: todo, done, etc.)
completed_at (timestamp|null)
Timestamps
</pre>

---

## Important Notes

Subtasks are recursively loaded as a tree.

Only the authenticated user's tasks are accessible.

Validation disallows updating or deleting completed tasks.

Marking a task as done requires all subtasks to be done.

parent_id can be null for root tasks.

Validation in controller converts parent_id = 0 to null for convenience.
