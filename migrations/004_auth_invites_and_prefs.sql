-- Auth-related extensions: password reset, per-user language, invite codes, lockout

-- 1) Per-user language preference
ALTER TABLE users
    ADD COLUMN preferred_language VARCHAR(10) NULL AFTER role;

-- 2) Login security: failed attempts and lockout
ALTER TABLE users
    ADD COLUMN failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0 AFTER status,
    ADD COLUMN locked_until DATETIME NULL AFTER failed_login_attempts;

-- 3) Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT uq_password_resets_token UNIQUE (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Invite codes (optionally scoped to course/class) for registration/enrollment
CREATE TABLE IF NOT EXISTS invite_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(64) NOT NULL,
    course_id INT UNSIGNED NULL,
    class_id INT UNSIGNED NULL,
    auto_approve TINYINT(1) NOT NULL DEFAULT 0,
    max_uses INT UNSIGNED NULL,
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME NULL,
    note VARCHAR(255) NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT uq_invite_codes_code UNIQUE (code),
    CONSTRAINT fk_invite_codes_course FOREIGN KEY (course_id) REFERENCES courses(id),
    CONSTRAINT fk_invite_codes_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_invite_codes_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

