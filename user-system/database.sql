-- ============================================================
-- database.sql  |  User Registration & Login System
-- ------------------------------------------------------------
-- HOW TO USE
--   Local (XAMPP): in phpMyAdmin create a database named
--                  `user_system`, select it, open the Import tab
--                  and upload this file.
--   InfinityFree : create your database in Control Panel ->
--                  MySQL Databases, open phpMyAdmin, select that
--                  database, then Import this file.
-- (No CREATE DATABASE / USE lines so it imports anywhere.)
-- ============================================================

DROP TABLE IF EXISTS login_logs;
DROP TABLE IF EXISTS users;

-- ------------------------------------------------------------
-- Main table: users  (8 fields, primary key, unique keys)
-- ------------------------------------------------------------
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,                 -- bcrypt hash, never plain text
    role        ENUM('admin','user') NOT NULL DEFAULT 'user',
    status      ENUM('active','disabled') NOT NULL DEFAULT 'active',
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Related table: login_logs  (one user -> many logins)
-- ------------------------------------------------------------
CREATE TABLE login_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    login_time  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address  VARCHAR(45) DEFAULT NULL,
    user_agent  VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Default admin account  ->  username: admin   password: Admin@12345
-- CHANGE THIS PASSWORD after your first login!
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, username, password, role) VALUES
('System Administrator', 'admin@example.com', 'admin',
 '$2y$10$Wx/37/QSwWLTiCkCM0tJPO5LY0mE3gcjzGlcUvNrn4Y6a2mlXyLB2', 'admin');
