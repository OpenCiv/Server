<?php
// Settings contain credentials and security variables
require 'settings.php';

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
}

function send_result($result, $code = 200) {
   global $db;

   if ($db != null) {
      $db->close();
   }

   if ($code !== 200) {
      http_response_code($code);
   }

   header('Access-Control-Allow-Origin: ' . settings::$origin);
   header('Content-Type: application/json');
   echo json_encode($result);
   exit;
}

function generate_token() {
   $alphabet = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
   $pass = [];
   $max = strlen($alphabet) - 1;
   for ($character = 0; $character < 64; $character++) {
      $random = rand(0, $max);
      $pass[] = $alphabet[$random];
   }

   return implode($pass);
}

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $params = json_decode(trim(file_get_contents('php://input')));
}
?>