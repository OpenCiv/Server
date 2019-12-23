<?php

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
   function execute($query, $types = null, ...$parameters) {
      $statement = $this->prepare($query);
      if (!$statement) {
         send_result('Query failed: ' . $query, 500);
      }

      // Check if the query requires parameters to be bound
      if (!$types) {
         $statement->bind_param($types, ...$parameters);
      }

      $statement->execute();
      if ($statement->error) {
         send_result($statement->error, 500);
      }

      $result = $statement->get_result();

      // The result is a boolean in case of an INSERT, UPDATE or DELETE query
      if (gettype($result) === 'boolean') {
         $statement->close();
         return $result;
      }

      // Every SELECT query will return an array
      $data = [];
      while ($row = $result->fetch_row()) {
         $data[] = $row;
      }

      $statement->close();
      return $data;
   }

   /**
    * Returns the first result of a SELECT query, or null if there was none
    * @param query The SQL query
    * @param types The first argument of mysqli_stmt::bind_params
    * @param parameters The other arguments of mysqli_stmt::bind_params
    * @return any The first result of the query
    */
   function first($query, $types = null, ...$parameters) {
      $data = $this->execute($query, $types, ...$parameters);
      if (gettype($data) !== 'array') {
         send_result("Unexpected result '" . json_encode($data) . "' from query '$query'", 500);
      }

      return count($data) > 0 ? $data[0] : null;
   }
}

/**
 * Set the user's ID and returns a value indicating whether the user is verified
 * @param userId The user's ID his set to this parameter
 * @return bool True if the user is verified, otherwise false
 */
function verify_user_id(&$userId) {
   global $db;
   
   // Check if a session exists
   if ($_SESSION['user_id']) {
      $userId = $_SESSION['user_id'];
   }

   // The session may have expired
   if (!$_COOKIE['token']) {
      send_result('Not logged in', 401);
   }

   // Find the token
   $query = $db->first('SELECT timestamp, user_id, user_agent FROM tokens WHERE value = ?', 's', $_COOKIE['token']);

   // Check if the token is found...
   if (!$query) {
      setcookie('token', null, $_SERVER['REQUEST_TIME'] - 42000, '/');
      send_result('Token not found', 401);
   }

   // ...and delete it
   $db->execute('DELETE FROM tokens WHERE value = ?', 's', $_COOKIE['token']);

   // Check if the token from the same user agent (browser, OS) and is not outdated
   $tokenTime = strtotime($query[0]);
   if ($query[2] !== $_SERVER['HTTP_USER_AGENT'] || $tokenTime < $_SERVER['REQUEST_TIME'] - 31622400) {
      setcookie('token', null, $_SERVER['REQUEST_TIME'] - 42000, '/');
      send_result('Invalid token', 401);
   }

   $userId = (int)$query[1];
   $query = $db->execute('SELECT verified FROM users WHERE id = ?', 'i', $_SESSION['user_id']);
   if (!$query) {
      logoff();
      send_result('User not found', 401);
   }

   $token = generate_token();
   $db->execute('INSERT INTO tokens (user_id, value, user_agent) VALUES (?, ?, ?)', 'iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
   setcookie('token', $token, $_SERVER['REQUEST_TIME'] + 31622400, '/');

   // Set the main session variable
   $_SESSION['user_id'] = $userId;

   // Return whether the user is verified
   return (bool)$query[0][0];
}

/**
 * Sends an e-mail to the user containing a link to verify the e-mail address
 * @param userId The user's ID
 * @param email The user's e-mail address
 */
function send_verification_email($userId, $email, $name) {
   $token = generate_token();
   $db->execute("INSERT INTO tokens (user_id, value, user_agent) VALUES (?, ?, 'verify')", 'is', $userId, $token);
   $to = "$name <$email>";
   $subject = 'OpenCiv: verification';
   $body = "Hello $name," . PHP_EOL . PHP_EOL .
      'Please follow this link to verify your account:' . PHP_EOL .
      settings::$origin . "/verify?token=$token" . PHP_EOL . PHP_EOL .
      'Kind regards,' . PHP_EOL .
      'The Open Civ team';
   $header = 'From: Open Civ <gamemaster@openciv.com>' . PHP_EOL .
      'Content-Type: text/plain;charset=utf-8' . PHP_EOL .
      'X-Mailer: PHP/' . phpversion();
   if (!mail($to, $subject, $body, $headers)) {
      $db->execute("DELETE FROM tokens WHERE value = ?", 's', $token);
      throw new Exception('Cannot send e-mail');
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
      $db->execute('DELETE FROM tokens WHERE value = ?', 's', $_COOKIE['token']);
      setcookie('token', null, $_SERVER['REQUEST_TIME'] - 42000, '/');
   }

   // Terminate the session
   $_SESSION = [];
   setcookie(session_name(), null, $_SERVER['REQUEST_TIME'] - 42000, '/');
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