<?php

// Settings contain credentials and security variables
require 'settings.php';

// We only need to request the time once, at the start, and will be the same throughout the script
$timestamp = time();

/**
 * A preset mysqli extension
 */
class database extends mysqli {
   function __construct() {
      parent::__construct(settings::$dbhost, settings::$dbuser, settings::$dbpass, settings::$dbname);
      if ($this->error) {
         send_result([
            'message' => 'Could not connect to the database',
            'success' => false
         ], 500);
      }

      $this->set_charset('utf8mb4');
   }

   function query($query, $types = '', ...$parameters) {
      if (strlen($types) !== count($parameters)) {
         throw new Exception('The number of query parameters do not match with the parameter types');
      }

      $statement = $this->stmt_init();
      $statement->prepare($query);
      if ($types !== '') {
         $statement->bind_param($types, ...$parameters);
      }

      $result = $statement->get_result();
      $data = [];
      while ($row = $result->fetch_assoc()) {
         $data[] = $row;
      }

      $statement->close();
      return $data;
   }
}

/**
 * The function returns results to the client
 */
function send_result($result, $code = 200) {
   global $db;
   $db->close();

   if ($code !== 200) {
      http_response_code($code);
   }

   header('Access-Control-Allow-Origin: ' . settings::$origin);
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
}

/**
 * Returns a string with 64 random characters
 */
function generate_token() {
   $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXY1Z234567890';
   $pass = [];
   $max = strlen($alphabet) - 1;
   for ($character = 0; $character < 64; $character++) {
      $random = rand(0, $max);
      $pass[] = $alphabet[$random];
   }

   return implode($pass);
}

// Start a session, open database, and obtain post parameters
session_start();
$db = new database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $params = json_decode(trim(file_get_contents('php://input')));
}
?>