-- Grading and files library schema

-- Add grading flags to courses
ALTER TABLE courses
    ADD COLUMN has_grading TINYINT(1) NOT NULL DEFAULT 1 AFTER default_lecture_duration_minutes,
    ADD COLUMN gpa_formula ENUM('average','weighted_second') NOT NULL DEFAULT 'average' AFTER has_grading;

-- Add status to classes (open/closed for grading)
ALTER TABLE classes
    ADD COLUMN status ENUM('open','closed') NOT NULL DEFAULT 'open' AFTER auto_lectures_count;

-- Global grading settings (scale and passing threshold)
INSERT INTO settings (`key`, `value`, created_at, updated_at)
VALUES
    ('grading_scale', '0_10', NOW(), NOW()),
    ('passing_threshold', '6.0', NOW(), NOW())
ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = VALUES(updated_at);

-- Grade assessments per class
CREATE TABLE IF NOT EXISTS assessments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    position INT UNSIGNED NOT NULL DEFAULT 1,
    max_score DECIMAL(5,2) NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_assessments_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_assessments_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_assessments_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grades per assessment & student
CREATE TABLE IF NOT EXISTS assessment_grades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    recorded_by INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_assessment_grades_assessment FOREIGN KEY (assessment_id) REFERENCES assessments(id),
    CONSTRAINT fk_assessment_grades_student FOREIGN KEY (student_id) REFERENCES users(id),
    CONSTRAINT fk_assessment_grades_recorded_by FOREIGN KEY (recorded_by) REFERENCES users(id),
    CONSTRAINT uq_assessment_grade UNIQUE (assessment_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Files (course-level or class-level)
CREATE TABLE IF NOT EXISTS files (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NULL,
    class_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    storage_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NULL,
    size_bytes BIGINT UNSIGNED NULL,
    available_from DATETIME NULL,
    available_until DATETIME NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_files_course FOREIGN KEY (course_id) REFERENCES courses(id),
    CONSTRAINT fk_files_class FOREIGN KEY (class_id) REFERENCES classes(id),
    CONSTRAINT fk_files_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_files_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File download logs
CREATE TABLE IF NOT EXISTS file_downloads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    downloaded_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_file_downloads_file FOREIGN KEY (file_id) REFERENCES files(id),
    CONSTRAINT fk_file_downloads_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

