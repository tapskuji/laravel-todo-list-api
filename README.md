# Todo list REST API

## Task

Build a REST API using laravel.

## Requirements

- Create, Read, Update and Delete a todo
- Provide a route for users to register (token based auth)
- Send one email when a todo is marked as incomplete after its deadline has passed.
- Write to a log file if an error occurs while trying to send an email
- Create an audit log to track any changes done to the todo
- Use rate limiting
- Write some tests
- Code coverage of tests
- Document how to use the API
- Use cache to increase performance
- Use database index to increase performance

## Technology stack used in this project

- Apache/2.4.29 (Ubuntu)
- php version 7.4.30
- composer version 2.1.9
- laravel version 8
- Ubuntu 18.04LTS
- cron
- Xdebug v3.1.5
- php unit
- PHPStorm (IDE)
- curl (cli)
- sqlite3

## Setup

The tools required to run this application are mentioned above.

## Set Environment

Copy .env.example to .env and make the following changes to the .env file:

- Environment
    - APP_NAME=laravel-todo-list-api
    - APP_ENV=production
    - APP_DEBUG=false
    - APP_URL=http://localhost

- Database (remove mysql settings)
    - DB_CONNECTION=mysql
    - DB_HOST=127.0.0.1
    - DB_PORT=3306
    - DB_DATABASE=laravel
    - DB_USERNAME=root
    - DB_PASSWORD=
- Database (add sqlite settings)
    - DB_CONNECTION=sqlite
    - DB_FOREIGN_KEYS=true

- Email (sign up for mailtrap.io and add the settings)
    - MAIL_MAILER=smtp
    - MAIL_HOST=smtp.mailtrap.io
    - MAIL_PORT=2525
    - MAIL_USERNAME=<YOUR_USERNAME>
    - MAIL_PASSWORD=<YOUR_PASSWORD>
    - MAIL_ENCRYPTION=null
    - MAIL_FROM_ADDRESS=null
    - MAIL_FROM_NAME="${APP_NAME}"

- Auth token name for personal access tokens
    - API_DEFAULT_TOKEN_NAME=todosapitoken
- Log file
    - MAX_FILE_SIZE_IN_BYTES=1048576
    - MAX_FILE_SIZE_IN_MEGABYTES=1

- Email address (senders email address)
    - ADMIN_EMAIL=admin@examples.com

- Rate limit max
    - RATE_LIMIT_ATTEMPTS=60

## Install dependencies

Your can run the following command to install the dependencies.

```bash
composer install
```

The run the migrations
```bash
php artisan migrate
```

Generate app key (a new value will be set for APP_KEY in the .env file)
```bash
php artisan key:generate
```
Run the tests.

```bash
php artisan test
```

Run code coverage. (make sure you configure xdebug for code coverage)
Open tests/coverage/index.html in the browser to check the results after running the command.

```bash
./vendor/bin/phpunit --coverage-html tests/coverage
```

Run the app.

```bash
php artisan serve
```

Check an overview of the scheduled tasks
```bash
php artisan schedule:list
```

Running The Scheduler Locally
```bash
php artisan schedule:work
```

## Configure schedule in production

1. Open crontab
```bash
crontab -e
```
2. Add the entry
`* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`

---
## API usage

Below are some usage examples, using [HTTPie](https://httpie.org/) as a client.

### Register user

Request:

```bash
curl -X POST http://localhost:8000/api/register \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -d '{"name": "John", "email": "john.doe@example.com", "password": "123456789", "password_confirmation": "123456789"}'
```

Response:

```json
{"message":"Registration successful","data":[{"name":"John","email":"john.doe@example.com","updated_at":"2022-09-01T11:21:18.000000Z","created_at":"2022-09-01T11:21:18.000000Z","id":2,"profile_image_url":"https:\/\/ui-avatars.com\/api\/?name=John"},{"token":"2|KoVzrBsYEm25uNJ1OGv4o6BucJXRDN..."}]}
```

### Login user

Request:

```bash
curl -X POST http://localhost:8000/api/login \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -d '{"email": "john.doe@example.com", "password": "123456789"}'
```

Response:

```json
{"message":"Login successful","data":[{"token":"6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf..."}]}
```

### Logout user

Request:

```bash
curl -X POST http://localhost:8000/api/logout \
    -H 'Accept: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...'
```

Response:

```json
{"message":"Logout successful","data":[]}
```

### Get user profile information

Request:

```bash
curl -X GET http://localhost:8000/api/user \
   -H 'Accept: application/json' \
   -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...'
```

Response:

```json
{"message":"User data","data":[{"id":2,"name":"John","email":"john.doe@example.com","email_verified_at":null,"created_at":"2022-09-01T11:21:18.000000Z","updated_at":"2022-09-01T11:21:18.000000Z","profile_photo":null,"profile_image_url":"https:\/\/ui-avatars.com\/api\/?name=John"}]}
```

### Change user's password

Request:

```bash
curl -X PUT http://localhost:8000/api/profile/change-password \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...' \
    -d '{"old_password": "123456789", "password": "9874563210", "password_confirmation": "9874563210"}'
```

Response:

```json
{"message":"Password update successful","data":[{"id":2,"name":"John","email":"john.doe@example.com","email_verified_at":null,"created_at":"2022-09-01T11:21:18.000000Z","updated_at":"2022-09-01T11:53:06.000000Z","profile_photo":null,"profile_image_url":"https:\/\/ui-avatars.com\/api\/?name=John"}]}
```

### Update profile

Example below shows how to update profile image by sending the base64 data.
Request:

```bash
curl -X PUT http://localhost:8000/api/profile \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...' \
    -d '{"profile_photo": "data:image/png;base64,abcd..."}'
```

Response:

```json
{"message":"successful","total":1,"data":[{"id":2,"name":"John","email":"john.doe@example.com","email_verified_at":null,"created_at":"2022-09-01T11:21:18.000000Z","updated_at":"2022-09-01T12:02:39.000000Z","profile_photo":"profile-image-1662033759.png","profile_image_url":"http:\/\/localhost:8000\/uploads\/profile_photos\/profile-image-1662033759.png"}]}
```

### Create a todo

NB* The route here is in plural form because PHPstorm will highlight any work called todo. REST requires a route resource to be in singular form.

Request:

```bash
curl -X POST http://localhost:8000/api/todos \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...' \
    -d '{"title": "Create Project", "description": "Create a laravel REST API", "is_complete": 0, "due_date": "2022-09-14 17:00:00"}'
```

Response:

```json
{"message":"successful","total":1,"data":[{"title":"Create Project","description":"Create a laravel REST API","is_complete":0,"due_date":"2022-09-14 17:00:00","user_id":2,"updated_at":"2022-09-01T12:12:27.000000Z","created_at":"2022-09-01T12:12:27.000000Z","id":8}]}
```

### Update a todo

Request:

```bash
curl -X PUT http://localhost:8000/api/todos/8 \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...' \
    -d '{"title": "Create a Laravel Project", "description": "Create a Laravel REST API"}'
```

Response:

```json
{"message":"successful","total":1,"data":[{"id":8,"title":"Create a Laravel Project","description":"Create a Laravel REST API","is_complete":"0","due_date":"2022-09-14 17:00:00","user_id":"2","created_at":"2022-09-01T12:12:27.000000Z","updated_at":"2022-09-01T12:15:23.000000Z"}]}
```

### Get a todo

Request:

```bash
curl -X GET http://localhost:8000/api/todos/8 \
    -H 'Accept: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...'
```

Response:

```json
{"message":"successful","total":1,"data":[{"id":8,"title":"Create a Laravel Project","description":"Create a Laravel REST API","is_complete":"0","due_date":"2022-09-14 17:00:00","user_id":"2","created_at":"2022-09-01T12:12:27.000000Z","updated_at":"2022-09-01T12:15:23.000000Z"}]}
```

### Delete a todo

Request:

```bash
curl -X DELETE http://localhost:8000/api/todos/8 \
    -H 'Accept: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...'
```

Response:

```json
{"message":"successful","total":0,"data":[]}
```

### Get all todos

Request:

```bash
curl -X GET http://localhost:8000/api/todos \
    -H 'Accept: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...'
```

Response:

```json
{"message":"successful","total":1,"data":[{"id":9,"title":"Create Frontend","description":"Create a frontend project this week","is_complete":"0","due_date":"2022-09-14 17:00:00","user_id":"2","created_at":"2022-09-01T12:18:48.000000Z","updated_at":"2022-09-01T12:18:48.000000Z"}]}
```

### Get changes (audit trail) of a todo

Request:

```bash
curl -X GET http://localhost:8000/api/audit-trail/8 \
    -H 'Accept: application/json' \
    -H 'Authorization: Bearer 6|MB0HwPIGL5x5ND8rL1tFly2skh4LrBf...'
```

Response:

```json
{"message":"successful","total":2,"data":[{"id":"11","old_id":"8","old_title":"Create Project","old_description":"Create a laravel REST API","old_is_complete":"0","user_id":"2","old_due_date":"2022-09-14 17:00:00","old_created_at":"2022-09-01 12:12:27","old_updated_at":"2022-09-01 12:12:27","created_at":"2022-09-01 12:12:27"},{"id":"12","old_id":"8","old_title":"Create a Laravel Project","old_description":"Create a Laravel REST API","old_is_complete":"0","user_id":"2","old_due_date":"2022-09-14 17:00:00","old_created_at":"2022-09-01 12:12:27","old_updated_at":"2022-09-01 12:15:23","created_at":"2022-09-01 12:15:23"}]}
```
