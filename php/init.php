<?php

// We only need to request the time once, at the start, and will be the same throughout the script
$timestamp = time();

// Settings contain credentials and security variables
require 'settings.php';

/**
 * A preset mysqli extension
 */
class database extends mysqli {
   function __construct() {
      parent::__construct(settings::$dbhost, settings::$dbuser, settings::$dbpass, settings::$dbname);
      if ($this->connect_error) {
         send_result($this->connect_error, 500);
      }

      $this->set_charset('utf8mb4');
   }

   /**
    * Executes a query and returns the result
    * @param query The SQL query
    * @param types The first argument of mysqli_stmt::bind_params
    * @param parameters The other arguments of mysqli_stmt::bind_params
    * @return array The results of the query
    */
   function execute($query, $types = '', ...$parameters) {
      $statement = $this->prepare($query);
      if (!$statement) {
         send_result('Query failed: ' . $query, 500);
      }

      if ($types !== '') {
         $statement->bind_param($types, ...$parameters);
      }

      $statement->execute();
      $result = $statement->get_result();
      if (gettype($result) === 'boolean') {
         $statement->close();
         return $result;
      }

      $data = [];
      while ($row = $result->fetch_row()) {
         $data[] = $row;
      }

      $statement->close();
      return $data;
   }
}

/**
 * The function returns results to the client
 * @param result The parameter will be encoded into JSON and sent back to the client
 * @param code The HTTP status code
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
 * Destroys the session and token
 */
function logoff() {

   // Delete the existing token
   if (isset($_COOKIE['token'])) {
      $db->execute('DELETE FROM `tokens` WHERE `value` = ?', 's', $_COOKIE['token']);
      setcookie('token', null, $timestamp - 42000, '/');
   }

   // Terminate the session
   $_SESSION = [];
   setcookie(session_name(), null, $timestamp - 42000, '/');
   session_destroy();
}

/**
 * Returns a token string
 * @return string A string with 64 random characters
 */
function generate_token() {
   $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
   $max = strlen($alphabet) - 1;
   $pass = [];
   for ($character = 0; $character < 64; $character++) {
      $random = rand(0, $max);
      $pass[] = $alphabet[$random];
   }

   return implode($pass);
}

// Start a session, open the database, and obtain post parameters
session_start();
$db = new database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $params = json_decode(trim(file_get_contents('php://input')));
}
?>