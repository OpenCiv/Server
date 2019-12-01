<?php
require 'settings.php';
$db = new mysqli(settings::$dbhost, settings::$dbuser, settings::$dbpass);
$success = $db->query(sprintf("CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`", settings::$dbname));
$success &= $db->select_db(settings::$dbname);
/*
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
*/
$success &= $db->query('ALTER TABLE `improvements` DROP CONSTRAINT `fk_improvement_game`;');
$success &= $db->query('ALTER TABLE `improvements` DROP CONSTRAINT `fk_improvement_owner`;');
$success &= $db->query('ALTER TABLE `units` DROP CONSTRAINT `fk_unit_player`;');
$success &= $db->query('ALTER TABLE `players` DROP CONSTRAINT `fk_player_game`;');
$success &= $db->query('ALTER TABLE `players` DROP CONSTRAINT `fk_player_user`;');
$success &= $db->query('ALTER TABLE `terrain` DROP CONSTRAINT `fk_terrain_game`;');
$query = 'DROP TABLE IF EXISTS `games`;';
$success &= $db->query('DROP TABLE IF EXISTS `improvements`;');
$success &= $db->query('DROP TABLE IF EXISTS `units`;');
$success &= $db->query('DROP TABLE IF EXISTS `players`;');
$success &= $db->query('DROP TABLE IF EXISTS `terrain`;');
$success &= $db->query('DROP TABLE IF EXISTS `games`;');
$query = 'DROP TABLE IF EXISTS `terrain`;';
$success &= $db->query($query);
$query = 'DROP TABLE IF EXISTS `players`;';
$success &= $db->query($query);
$query = 'DROP TABLE IF EXISTS `units`;';
$success &= $db->query($query);
$query = 'DROP TABLE IF EXISTS `improvements`;';
$success &= $db->query($query);

$query = 'CREATE TABLE `games` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `x` SMALLINT NOT NULL,
   `y` SMALLINT NOT NULL,
   `name` VARCHAR(50) NOT NULL,
   PRIMARY KEY(`id`)
   );';
$success &= $db->query($query);
$query = 'CREATE TABLE `terrain` (
   `game_id` INT NOT NULL,
   `x` SMALLINT NOT NULL,
   `y` SMALLINT NOT NULL,
   `type` VARCHAR(20) NOT NULL,
   PRIMARY KEY(`game_id`, `x`, `y`),
   CONSTRAINT `fk_terrain_game` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`)
   );';
$success &= $db->query($query);
$query = 'CREATE TABLE `players` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `user_id` INT NOT NULL,
   `game_id` INT NOT NULL,
   `name` VARCHAR(50) NOT NULL,
   PRIMARY KEY(`id`),
   CONSTRAINT `fk_player_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
   CONSTRAINT `fk_player_game` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`)
   );';
$success &= $db->query($query);
$query = 'CREATE TABLE `units` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `player_id` INT NOT NULL,
   `x` SMALLINT NOT NULL,
   `y` SMALLINT NOT NULL,
   `features` BIGINT NOT NULL,
   `action` VARCHAR(20) NULL,
   PRIMARY KEY(`id`),
   CONSTRAINT `fk_unit_player` FOREIGN KEY (`player_id`) REFERENCES `players`(`id`)
   );';
$success &= $db->query($query);
$query = 'CREATE TABLE `improvements` (
   `game_id` INT NOT NULL,
   `x` SMALLINT NOT NULL,
   `y` SMALLINT NOT NULL,
   `type` VARCHAR(20) NOT NULL,
   `owner_id` INT NULL,
   PRIMARY KEY(`game_id`, `x`, `y`),
   CONSTRAINT `fk_improvement_game` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`),
   CONSTRAINT `fk_improvement_owner` FOREIGN KEY (`owner_id`) REFERENCES `players`(`id`)
   );';
$success &= $db->query($query);
echo $success ? 'Database reset' : 'Something went wrong';
?>