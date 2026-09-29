-- Task Tracker database schema (MySQL 5.7+ / MariaDB 10.3+).
--
-- LOCAL (XAMPP / php -S): import this whole file. The first two statements create and
-- select the database.
--
-- SHARED HOSTING (InfinityFree): SQL cannot create databases there. Create the database
-- in the control panel, open it in phpMyAdmin, then import this file AFTER deleting the
-- CREATE DATABASE and USE lines below (keep the two CREATE TABLE statements).

CREATE DATABASE IF NOT EXISTS task_tracker
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_tracker;

CREATE TABLE users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100)  NOT NULL,
  email         VARCHAR(150)  NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tasks (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT           NOT NULL,
  title       VARCHAR(150)  NOT NULL,
  description TEXT          NULL,
  priority    ENUM('low','medium','high')            NOT NULL DEFAULT 'medium',
  status      ENUM('pending','in_progress','done')   NOT NULL DEFAULT 'pending',
  due_date    DATE          NULL,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_tasks_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_status (user_id, status)
) ENGINE=InnoDB;
