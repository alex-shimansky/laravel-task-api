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

- PHP 8.1+
- Laravel 12+
- MySQL 8+ (with full-text index support)
- Composer
- Node.js & npm (optional, if frontend or mix assets are needed)

---

## Installation

1. **Clone repository**

```bash
git clone https://github.com/your-username/laravel-task-api.git
cd laravel-task-api
```

2. **Install dependencies**

```bash
composer install
```

3. **Create .env file**

```bash
cp .env.example .env
```

Edit .env to set your database credentials and other settings.

4. **Generate application key**

```bash
php artisan key:generate
```

5. **Run migrations and seeders**

```bash
php artisan migrate --seed
```

This will create the necessary tables and seed initial users and tasks.

6. **Run development server**

```bash
php artisan serve
```

The API will be available at http://localhost:8000.

---

## Authentication

Register and login endpoints are available via AuthController (not shown in snippet).

Use Sanctum tokens for API authentication.

Send the token in the header as:
Authorization: Bearer {token}

---

## API Endpoints

Method	URI	                    Description
POST	/api/login              User login
POST	/api/logout             User logout
GET	    /api/tasks              List tasks with filters & sort
POST	/api/tasks              Create new task
GET	    /api/tasks/{id}         Get task by ID with subtasks
PUT	    /api/tasks/{id}         Update task
DELETE	/api/tasks/{id}         Delete task
POST	/api/tasks/{id}/done    Mark task as done

---

## Filtering and Sorting (Tasks List)

Filters:

status: Task status enum (e.g. todo, done)
priority: Task priority enum integer
search: Full-text search on title or description

Sorting:

Pass sort query parameter with multiple fields, e.g.
?sort=priority:desc,created_at:asc

Supported fields: created_at, completed_at, priority

---

## Data Models

Task

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

---

## Important Notes

Subtasks are recursively loaded as a tree.

Only the authenticated user's tasks are accessible.

Validation disallows updating or deleting completed tasks.

Marking a task as done requires all subtasks to be done.

parent_id can be null for root tasks.

Validation in controller converts parent_id = 0 to null for convenience.
