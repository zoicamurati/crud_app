# Symfony User CRUD API
A simple REST API built with Symfony for managing user data with full CRUD operations.

User management (Create, Read, Update, Delete)
- RESTful API design
- Service layer architecture
- Password encryption
- Soft delete functionality
- Input validation

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL or PostgreSQL
- Symfony CLI (optional)


## Installation
1. Clone the repository
```bash  
   https://github.com/zoicamurati/crud_app.git
   ```

2. Install dependencies
```bash  
   composer install 
 ```
3. Configure environment variables
```bash
   ###> DATABASE CONNECTION ###
   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0"
   ###< DATABASE CONNECTION ###
   ```
4. Create the database
```bash
   php bin/console doctrine:database:create
```
5. Run migrations
```bash
   php bin/console doctrine:migrations:migrate
```
6. Start the development server
```bash symfony server:start
   # or, without Symfony CLI
   php -S localhost:8000 -t public/
```
