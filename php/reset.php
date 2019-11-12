<?php
require 'settings.php';
$db = new mysqli(settings::$dbhost, settings::$dbuser, settings::$dbpass);
$db->query(sprintf("CREATE DATABASE IF NOT EXISTS `%s` DEFAULT CHARACTER SET `utf8mb4` COLLATE `utf8mb4_unicode_ci`", settings::$dbname));
$db->select_db(settings::$dbname);
$query = 'DROP TABLE IF EXISTS `Users`;
DROP TABLE IF EXISTS `Tokens`;
CREATE TABLE `Users` (
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Email` VARCHAR(254) NOT NULL,
  `Password` VARCHAR(60) NOT NULL,
  `Name` VARCHAR(50) NOT NULL,
  `Verified` BOOLEAN NOT NULL,
  PRIMARY KEY(`Id`)
);
CREATE TABLE `Tokens` (
   `Timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `Value` VARCHAR(64) NOT NULL,
   `UserId` INT NOT NULL,
   `UserAgent` VARCHAR(255) NOT NULL
);';
$db->query($query);
?>