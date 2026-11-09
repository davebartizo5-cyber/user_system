# UserHub - User Registration and Login System

PHP + MySQL + Bootstrap 5 CRUD application (Web Systems Technologies final project).

## Features
- **Create** - register with validation, duplicate checks and hashed passwords
- **Read** - dashboard (admin sees all users with search + pagination; users see their own profile)
- **Update** - edit profile / change password; admins can change role and status
- **Delete** - delete accounts with confirmation (CSRF-protected POST)
- Secure login (bcrypt `password_verify`, session regeneration, CSRF tokens, prepared statements)
- Login history table (related table with foreign key)
- Enhancements: password-strength meter, show/hide password, dark mode, search & pagination, admin stats

## Database
| Table | Purpose |
|---|---|
| `users` | id (PK), full_name, email (unique), username (unique), password (hash), role, status, created_at |
| `login_logs` | id (PK), user_id (FK -> users.id, ON DELETE CASCADE), login_time, ip_address, user_agent |

## Run locally (XAMPP)
1. Start **Apache** and **MySQL** in XAMPP.
2. Copy this folder to `C:\xampp\htdocs\user-system`.
3. Open `http://localhost/phpmyadmin`, create a database named `user_system`, select it, then **Import** `database.sql`.
4. Open `http://localhost/user-system`.

**Default admin:** username `admin` / password `Admin@12345` (change it after first login).

## Deploy on InfinityFree
1. Create a hosting account, then **MySQL Databases** -> create a database; note hostname, username, password, database name.
2. Edit `includes/db_connect.php` with those four values.
3. Upload the project contents into `htdocs/` using **File Manager**.
4. Open **phpMyAdmin** on InfinityFree, select your database, **Import** `database.sql`.
5. Visit your live link and test every feature.

## Project structure
```
user-system/
├── assets/            style.css, app.js
├── includes/          db_connect.php, functions.php, header.php, footer.php
├── database.sql
├── index.php  register.php  login.php  logout.php
├── dashboard.php  edit.php  delete.php
└── README.md
```
