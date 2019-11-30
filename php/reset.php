<?php
require 'settings.php';
$db = new mysqli(settings::$dbhost, settings::$dbuser, settings::$dbpass);
$success = $db->query(sprintf("CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`", settings::$dbname));
$success &= $db->select_db(settings::$dbname);
$query = 'DROP TABLE IF EXISTS `users`;';
$success &= $db->query($query);
$query = 'DROP TABLE IF EXISTS `tokens`;';
$success &= $db->query($query);
$query = 'CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(254) NOT NULL,
  `password` VARCHAR(60) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `verified` BOOLEAN NOT NULL,
  PRIMARY KEY(`id`)
);';
$success &= $db->query($query);
$query = 'CREATE TABLE `tokens` (
   `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `value` VARCHAR(64) NOT NULL,
   `user_id` INT NOT NULL,
   `user_agent` VARCHAR(255) NOT NULL
);';
$success &= $db->query($query);
echo $success ? 'Database reset' : 'Something went wrong';
?>