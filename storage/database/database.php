<?php

return "
-- Users
CREATE TABLE IF NOT EXISTS `users` (
   `id` INT PRIMARY KEY AUTO_INCREMENT,
   `username` VARCHAR(255) NOT NULL UNIQUE,
   `password_hash` VARCHAR(255) NOT NULL,
   `email` VARCHAR(255),
   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sessions (for authorization)
CREATE TABLE IF NOT EXISTS `sessions` (
   `token` VARCHAR(255) PRIMARY KEY,
   `user_id` INT NOT NULL,
   `expires_at` DATETIME NOT NULL,
   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Folders
CREATE TABLE IF NOT EXISTS `folders` (
   `id` INT PRIMARY KEY AUTO_INCREMENT,
   `user_id` INT NOT NULL,
   `parent_id` INT,
   `name` VARCHAR(255) NOT NULL,
   `icon` VARCHAR(16) DEFAULT '📁',
   `position` INT DEFAULT 0,
   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
   FOREIGN KEY (`parent_id`) REFERENCES `folders`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX `idx_folders_user` ON `folders`(`user_id`, `parent_id`);

-- Notes
CREATE TABLE IF NOT EXISTS `notes` (
   `id` INT PRIMARY KEY AUTO_INCREMENT,
   `user_id` INT NOT NULL,
   `folder_id` INT,
   `title` VARCHAR(255) NOT NULL,
   `content` TEXT NOT NULL,
   `preview` VARCHAR(255),
   `is_pinned` TINYINT(1) DEFAULT 0,
   `is_archived` TINYINT(1) DEFAULT 0,
   `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
   `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
   FOREIGN KEY (`folder_id`) REFERENCES `folders`(`id`) ON DELETE SET NULL,
   FULLTEXT (`title`, `content`)
) ENGINE=InnoDB;

CREATE INDEX `idx_notes_user_folder` ON `notes`(`user_id`, `folder_id`);
CREATE INDEX `idx_notes_pinned` ON `notes`(`user_id`, `is_pinned`, `updated_at`);

-- Tags
CREATE TABLE IF NOT EXISTS `tags` (
   `id` INT PRIMARY KEY AUTO_INCREMENT,
   `name` VARCHAR(255) NOT NULL UNIQUE,
   `color` VARCHAR(7) DEFAULT '#3B82F6'
) ENGINE=InnoDB;

-- Note-Tags link (many-to-many)
CREATE TABLE IF NOT EXISTS `note_tags` (
   `note_id` INT NOT NULL,
   `tag_id` INT NOT NULL,
   PRIMARY KEY (`note_id`, `tag_id`),
   FOREIGN KEY (`note_id`) REFERENCES `notes`(`id`) ON DELETE CASCADE,
   FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX `idx_note_tags_tag` ON `note_tags`(`tag_id`);

-- Files (images + documents)
CREATE TABLE IF NOT EXISTS `files` (
   `id` INT PRIMARY KEY AUTO_INCREMENT,
   `note_id` INT NOT NULL,
   `user_id` INT NOT NULL,
   `type` VARCHAR(50) NOT NULL,
   `filename` VARCHAR(255) NOT NULL,
   `stored_name` VARCHAR(255) NOT NULL,
   `size` INT NOT NULL,
   `mime_type` VARCHAR(255) NOT NULL,
   `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (`note_id`) REFERENCES `notes`(`id`) ON DELETE CASCADE,
   FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX `idx_files_note` ON `files`(`note_id`);
";
