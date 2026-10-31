-- ============================================================
-- Game studios (one-to-many) + USER module with "who did it" tracking
--   users (1) ──< studios (1) ──< games
--   studios.added_by / last_updated_by and games.added_by / last_updated_by -> users.user_id
--   activity_log keeps WHO inserted, updated or deleted something (deleted rows cannot store it)
-- Import with phpMyAdmin (Import tab) or: mysql -u root < sql/schema.sql
-- ============================================================
CREATE DATABASE IF NOT EXISTS game_studio_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE game_studio_db;

DROP TABLE IF EXISTS activity_log;   -- drop tables that depend on others first
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS studios;
DROP TABLE IF EXISTS users;

-- USER entity: personal information + login credentials
CREATE TABLE users (
    user_id         INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(50)   NOT NULL,
    last_name       VARCHAR(50)   NOT NULL,
    username        VARCHAR(30)   NOT NULL UNIQUE,
    email           VARCHAR(100)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255)  NOT NULL,        -- bcrypt hash, NEVER the plain password
    address         VARCHAR(255)  NOT NULL,
    age             TINYINT UNSIGNED NOT NULL,
    date_registered DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login      DATETIME      NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- PARENT table (now with who added / last updated it, and when)
CREATE TABLE studios (
    studio_id        INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    studio_name      VARCHAR(100)  NOT NULL UNIQUE,
    founder_name     VARCHAR(100)  NOT NULL,
    email            VARCHAR(100)  NOT NULL UNIQUE,
    country          VARCHAR(60)   NOT NULL,
    year_founded     SMALLINT UNSIGNED NOT NULL,
    studio_type      VARCHAR(30)   NOT NULL,
    website          VARCHAR(255)  NULL,
    date_added       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by         INT UNSIGNED  NULL,            -- user who created the record
    last_updated_by  INT UNSIGNED  NULL,            -- user who changed it last
    last_updated     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_studios_added_by   FOREIGN KEY (added_by)        REFERENCES users (user_id) ON DELETE SET NULL,
    CONSTRAINT fk_studios_updated_by FOREIGN KEY (last_updated_by) REFERENCES users (user_id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- CHILD table (same tracking columns)
CREATE TABLE games (
    game_id          INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    studio_id        INT UNSIGNED  NOT NULL,
    title            VARCHAR(100)  NOT NULL,
    genre            VARCHAR(30)   NOT NULL,
    platform         VARCHAR(30)   NOT NULL,
    game_engine      VARCHAR(30)   NOT NULL,
    release_status   VARCHAR(20)   NOT NULL DEFAULT 'In Development',
    price            DECIMAL(8,2)  NOT NULL DEFAULT 0.00,
    release_date     DATE          NULL,
    date_added       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by         INT UNSIGNED  NULL,
    last_updated_by  INT UNSIGNED  NULL,
    last_updated     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_studio_title UNIQUE (studio_id, title),
    CONSTRAINT fk_games_studio     FOREIGN KEY (studio_id)       REFERENCES studios (studio_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_games_added_by   FOREIGN KEY (added_by)        REFERENCES users (user_id) ON DELETE SET NULL,
    CONSTRAINT fk_games_updated_by FOREIGN KEY (last_updated_by) REFERENCES users (user_id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Audit trail: one row per action, so deletions are traceable to a user too
CREATE TABLE activity_log (
    log_id      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,
    username    VARCHAR(30)  NOT NULL,              -- copy of the name, kept even if the user is removed
    action      VARCHAR(20)  NOT NULL,              -- REGISTER, LOGIN, LOGOUT, INSERT, UPDATE, DELETE
    entity      VARCHAR(20)  NOT NULL,              -- user, studio, game
    entity_id   INT UNSIGNED NULL,
    details     VARCHAR(255) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
