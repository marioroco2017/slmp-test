# Laravel JSONPlaceholder API

A Laravel 12 REST API that fetches all data from [JSONPlaceholder](https://jsonplaceholder.typicode.com/), stores it in a normalized MySQL database, and exposes full CRUD endpoints protected by Sanctum token authentication.

---

## Tech Stack

| Component       | Version        |
|-----------------|----------------|
| PHP             | 8.4            |
| Laravel         | 12.x           |
| MySQL           | 8.0            |
| Nginx           | Alpine (latest)|
| Laravel Sanctum | 4.x            |
| Docker          | Compose v2     |

---

## Quick Start with Docker

```bash
# 1. Clone and enter the backend directory
git clone <repo-url>
cd slmp-test/backend

# 2. Copy environment file
cp .env.example .env

# 3. Build and start containers
docker-compose up -d --build

# 4. Run migrations
docker-compose exec app php artisan migrate

# 5. Seed database from JSONPlaceholder
docker-compose exec app php artisan app:fetch-jsonplaceholder
```

The API will be available at `http://localhost:8000/api/v1`.

### Running Locally (WAMP/XAMPP)

```bash
composer install
cp .env.example .env
# Configure DB credentials in .env
php artisan key:generate
php artisan migrate
php artisan app:fetch-jsonplaceholder
php artisan serve
```

---

## Database Schema

Data is normalized to 3NF. The nested `address`, `geo`, and `company` objects from JSONPlaceholder users are extracted into dedicated tables.

```
users
 ├── addresses  (1:1)
 │    └── geos  (1:1)
 └── companies  (1:1)
 ├── posts      (1:N)
 │    └── comments (1:N)
 ├── albums     (1:N)
 │    └── photos   (1:N)
 └── todos      (1:N)
```

### Seeded Record Counts

| Table    | Records |
|----------|--------:|
| users    |      10 |
| posts    |     100 |
| comments |     500 |
| albums   |     100 |
| photos   |   5,000 |
| todos    |     200 |

---

## Authentication Guide

The API uses **Laravel Sanctum** bearer token authentication.

### Register

```http
POST /api/v1/register
Content-Type: application/json

{
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "1-770-736-8031",
    "website": "hildegard.org",
    "address": {
        "street": "Kulas Light",
        "suite": "Apt. 556",
        "city": "Gwenborough",
        "zipcode": "92998-3874",
        "geo": {
            "lat": -37.3159,
            "lng": 81.1496
        }
    },
    "company": {
        "name": "Romaguera-Crona",
        "catchPhrase": "Multi-layered client-server neural-net",
        "bs": "harness real-time e-markets"
    }
}
```

**Response 201**
```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com",
        "phone": "1-770-736-8031",
        "website": "hildegard.org",
        "address": {
            "street": "Kulas Light",
            "suite": "Apt. 556",
            "city": "Gwenborough",
            "zipcode": "92998-3874",
            "geo": { "lat": "-37.315900", "lng": "81.149600" }
        },
        "company": {
            "name": "Romaguera-Crona",
            "catch_phrase": "Multi-layered client-server neural-net",
            "bs": "harness real-time e-markets"
        }
    }
}
```

### Login

```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response 200**
```json
{
    "token": "2|xyz789...",
    "user": {
        "id": 1,
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@example.com"
    }
}
```

### Using Your Token

Include the token in all protected requests:

```http
Authorization: Bearer <your-token-here>
```

### Get Authenticated User

```http
GET /api/v1/user
Authorization: Bearer <token>
```

### Logout

```http
POST /api/v1/logout
Authorization: Bearer <token>
```

**Response 200**
```json
{
    "message": "Logged out successfully."
}
```

> **Note:** Auth users (API consumers) are separate from the seeded JSONPlaceholder data users. Register a new account to get a token, then use it to query all data.

---

## API Reference

Base URL: `http://localhost:8000/api/v1`

All endpoints marked *(auth)* require `Authorization: Bearer <token>`.

### Auth Endpoints

| Method | Endpoint    | Auth | Description               |
|--------|-------------|------|---------------------------|
| POST   | `/register` |      | Register & receive token  |
| POST   | `/login`    |      | Login & receive token     |
| POST   | `/logout`   | Yes  | Revoke current token      |
| GET    | `/user`     | Yes  | Get authenticated user    |

### Resource Endpoints (all require auth)

| Method | Endpoint                  | Description                |
|--------|---------------------------|----------------------------|
| GET    | `/users`                  | List users (paginated)     |
| GET    | `/users/{id}`             | Get single user            |
| POST   | `/users`                  | Create user                |
| PUT    | `/users/{id}`             | Update user                |
| DELETE | `/users/{id}`             | Delete user                |
| GET    | `/users/{id}/posts`       | Get user's posts           |
| GET    | `/users/{id}/albums`      | Get user's albums          |
| GET    | `/users/{id}/todos`       | Get user's todos           |
| GET    | `/posts`                  | List posts (paginated)     |
| GET    | `/posts/{id}`             | Get single post            |
| POST   | `/posts`                  | Create post                |
| PUT    | `/posts/{id}`             | Update post                |
| DELETE | `/posts/{id}`             | Delete post                |
| GET    | `/posts/{id}/comments`    | Get post's comments        |
| GET    | `/comments`               | List comments (paginated)  |
| GET    | `/comments/{id}`          | Get single comment         |
| POST   | `/comments`               | Create comment             |
| PUT    | `/comments/{id}`          | Update comment             |
| DELETE | `/comments/{id}`          | Delete comment             |
| GET    | `/albums`                 | List albums (paginated)    |
| GET    | `/albums/{id}`            | Get single album           |
| POST   | `/albums`                 | Create album               |
| PUT    | `/albums/{id}`            | Update album               |
| DELETE | `/albums/{id}`            | Delete album               |
| GET    | `/albums/{id}/photos`     | Get album's photos         |
| GET    | `/photos`                 | List photos (paginated)    |
| GET    | `/photos/{id}`            | Get single photo           |
| POST   | `/photos`                 | Create photo               |
| PUT    | `/photos/{id}`            | Update photo               |
| DELETE | `/photos/{id}`            | Delete photo               |
| GET    | `/todos`                  | List todos (paginated)     |
| GET    | `/todos/{id}`             | Get single todo            |
| POST   | `/todos`                  | Create todo                |
| PUT    | `/todos/{id}`             | Update todo                |
| DELETE | `/todos/{id}`             | Delete todo                |

For full request/response examples see [example-api.md](example-api.md).

### Error Responses

**Validation Error (422)**
```json
{
    "message": "The email field is required.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

**Unauthenticated (401)**
```json
{
    "message": "Unauthenticated."
}
```

---

## Artisan Commands

```bash
# Fetch and seed all JSONPlaceholder data (idempotent — safe to re-run)
php artisan app:fetch-jsonplaceholder

# Inside Docker
docker-compose exec app php artisan app:fetch-jsonplaceholder
```

---

## Running Tests

```bash
# Run full test suite (inside Docker)
docker-compose exec app php artisan test

# Run locally
php artisan test

# With coverage report
php artisan test --coverage
```

Test coverage includes:
- Auth: registration, login, token revocation, protected route access (401)
- All resource APIs: index/show/create/update/delete + nested routes
- Validation errors (422)
- Fetch command: data integrity, idempotency, HTTP error handling

---

## Docker Services

| Service   | Container      | Port | Description  |
|-----------|----------------|------|--------------|
| app       | laravel_app    | —    | PHP 8.4-FPM  |
| webserver | laravel_nginx  | 8000 | Nginx        |
| db        | laravel_db     | 3306 | MySQL 8.0    |

### Useful Docker Commands

```bash
# View application logs
docker-compose logs -f app

# Access MySQL shell
docker-compose exec db mysql -u laravel -proot laravel_jsonplaceholder_api

# Verify seeded record counts
docker-compose exec db mysql -u laravel -proot laravel_jsonplaceholder_api -e "
  SELECT 'users'    as tbl, COUNT(*) as cnt FROM users
  UNION ALL SELECT 'posts',    COUNT(*) FROM posts
  UNION ALL SELECT 'comments', COUNT(*) FROM comments
  UNION ALL SELECT 'albums',   COUNT(*) FROM albums
  UNION ALL SELECT 'photos',   COUNT(*) FROM photos
  UNION ALL SELECT 'todos',    COUNT(*) FROM todos;
"
# Expected: users=10, posts=100, comments=500, albums=100, photos=5000, todos=200
```

---

## Project Structure

```
backend/
├── app/
│   ├── Console/Commands/
│   │   └── FetchJsonPlaceholderData.php
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   ├── PostController.php
│   │   │   ├── CommentController.php
│   │   │   ├── AlbumController.php
│   │   │   ├── PhotoController.php
│   │   │   └── TodoController.php
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       ├── PostResource.php
│   │       ├── CommentResource.php
│   │       ├── AlbumResource.php
│   │       ├── PhotoResource.php
│   │       └── TodoResource.php
│   └── Models/
│       ├── User.php
│       ├── Address.php
│       ├── Geo.php
│       ├── Company.php
│       ├── Post.php
│       ├── Comment.php
│       ├── Album.php
│       ├── Photo.php
│       └── Todo.php
├── database/migrations/
├── docker/
│   ├── nginx/default.conf
│   └── entrypoint.sh
├── routes/api.php
├── tests/Feature/
│   ├── AuthTest.php
│   ├── FetchDataCommandTest.php
│   └── Api/
│       ├── UserApiTest.php
│       ├── PostApiTest.php
│       ├── CommentApiTest.php
│       ├── AlbumApiTest.php
│       ├── PhotoApiTest.php
│       └── TodoApiTest.php
├── docker-compose.yml
├── Dockerfile

```

---

## License

MIT
