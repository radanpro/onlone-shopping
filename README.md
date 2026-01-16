# Online Shopping System (PHP & PDO)

## Project Overview
A complete web-based shopping system built with PHP, MySQL (PDO), and Bootstrap 5.

## Features
- **Visitor Interface:** Browse products and view details without registration.
- **User Interface:** Create accounts, login, manage profile (with image upload), and session/cookie management.
- **Admin Interface:** Dashboard with statistics, user management (activate/deactivate/delete), and product management (CRUD).
- **Security:** Password encryption (password_hash), data sanitization, and unauthorized access prevention.
- **Database:** Relational schema with Users, Categories, Products, Orders, and Order Items.

## Setup Instructions
1. Import the `db.sql` file into your MySQL database.
2. Update `config.php` with your database credentials.
3. Run `seed.php` to create the initial admin user and categories.
   - **Admin Login:** admin@shop.com / admin123
4. Ensure the `uploads/` directory is writable.

## Technologies Used
- PHP 8.x
- MySQL (PDO)
- Bootstrap 5
- FontAwesome
