-- ============================================
-- CodeSnip Vault — Database Schema
-- ============================================

CREATE DATABASE IF NOT EXISTS codesnip_vault
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE codesnip_vault;

-- --------------------------------------------
-- 1. users
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username   VARCHAR(50)     NOT NULL,
    email      VARCHAR(255)    NOT NULL,
    password   VARCHAR(255)    NOT NULL,
    role       ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    dark_mode  TINYINT(1)      NOT NULL DEFAULT 0,
    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email    (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 2. snippets
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS snippets (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    title       VARCHAR(255)    NOT NULL,
    description TEXT            NULL,
    code        TEXT            NOT NULL,
    language    VARCHAR(50)     NOT NULL,
    is_public   TINYINT(1)      NOT NULL DEFAULT 0,
    share_token VARCHAR(64)     NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_snippets_share_token (share_token),
    CONSTRAINT fk_snippets_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 3. tags
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS tags (
    id   INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    name VARCHAR(100)   NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 4. snippet_tags (composite primary key)
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS snippet_tags (
    snippet_id INT UNSIGNED NOT NULL,
    tag_id     INT UNSIGNED NOT NULL,
    PRIMARY KEY (snippet_id, tag_id),
    CONSTRAINT fk_snippet_tags_snippet
        FOREIGN KEY (snippet_id) REFERENCES snippets (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_snippet_tags_tag
        FOREIGN KEY (tag_id) REFERENCES tags (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 5. login_attempts
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    email        VARCHAR(255)   NOT NULL,
    ip_address   VARCHAR(45)    NOT NULL,
    attempted_at DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

