<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_file = __DIR__ . '/../database/notes.db';
$pdo = new PDO('sqlite:' . $db_file);

// Create the tables if they don't exist
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `notes` (
      `id` INTEGER PRIMARY KEY AUTOINCREMENT,
      `title` TEXT NOT NULL,
      `content` TEXT NOT NULL,
      `folder_id` INTEGER DEFAULT NULL,
      `created_at` TEXT NOT NULL DEFAULT (datetime('now')),
      `updated_at` TEXT NOT NULL DEFAULT (datetime('now')),
      `is_pinned` INTEGER NOT NULL DEFAULT 0
    );

    CREATE TABLE IF NOT EXISTS `folders` (
      `id` INTEGER PRIMARY KEY AUTOINCREMENT,
      `name` TEXT NOT NULL,
      `parent_id` INTEGER DEFAULT NULL
    );

    CREATE TABLE IF NOT EXISTS `tags` (
      `id` INTEGER PRIMARY KEY AUTOINCREMENT,
      `name` TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS `note_tags` (
      `note_id` INTEGER NOT NULL,
      `tag_id` INTEGER NOT NULL,
      PRIMARY KEY (`note_id`,`tag_id`)
    );

    CREATE TABLE IF NOT EXISTS `images` (
      `id` INTEGER PRIMARY KEY AUTOINCREMENT,
      `note_id` INTEGER NOT NULL,
      `filename` TEXT NOT NULL,
      `path` TEXT NOT NULL,
      `size` INTEGER NOT NULL,
      `uploaded_at` TEXT NOT NULL DEFAULT (datetime('now'))
    );
");
