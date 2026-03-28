-- Database creation
CREATE DATABASE IF NOT EXISTS `aqpg_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aqpg_db`;

-- Admin table
CREATE TABLE IF NOT EXISTS `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Professors table
CREATE TABLE IF NOT EXISTS `professors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subjects table (professor-owned for filtering)
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `professor_id` INT DEFAULT NULL,
  CONSTRAINT fk_subject_prof FOREIGN KEY (`professor_id`) REFERENCES professors(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subject codes
CREATE TABLE IF NOT EXISTS `subject_codes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_id` INT NOT NULL,
  `code` VARCHAR(100) NOT NULL,
  `professor_id` INT NOT NULL,
  CONSTRAINT fk_sc_subject FOREIGN KEY (`subject_id`) REFERENCES subjects(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_sc_prof FOREIGN KEY (`professor_id`) REFERENCES professors(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Question papers
CREATE TABLE IF NOT EXISTS `question_papers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `instructions` TEXT,
  `subject_code_id` INT NOT NULL,
  `professor_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_qp_code FOREIGN KEY (`subject_code_id`) REFERENCES subject_codes(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_qp_prof FOREIGN KEY (`professor_id`) REFERENCES professors(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Questions
CREATE TABLE IF NOT EXISTS `questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `paper_id` INT NOT NULL,
  `question_text` TEXT NOT NULL,
  `type` ENUM('MCQ','Fill','Short','Long') NOT NULL DEFAULT 'MCQ',
  `marks` INT NOT NULL DEFAULT 1,
  `co` VARCHAR(50),
  `bloom_level` VARCHAR(50),
  CONSTRAINT fk_q_paper FOREIGN KEY (`paper_id`) REFERENCES question_papers(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Choices for MCQ
CREATE TABLE IF NOT EXISTS `choices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT NOT NULL,
  `choice_text` TEXT NOT NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_c_question FOREIGN KEY (`question_id`) REFERENCES questions(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed admin
INSERT INTO admin (username, password) VALUES
('admin', '$2y$10$lsRR.gYOeXKjMERzrnhwz.v2/iRCmN1fAfvluvsWR9nwTDHgy/ngS')
ON DUPLICATE KEY UPDATE username=VALUES(username);

-- Seed professor
INSERT INTO professors (name, email, password, status) VALUES
('Default Professor', 'professor@example.com', '$2y$10$r6Bf3.kjIEUvKN.bzIl9muuuP05bkTy9vJcaQBcn82rIxmM3o3sme', 'active')
ON DUPLICATE KEY UPDATE email=VALUES(email);
