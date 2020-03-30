<?php

// Settings contain credentials and security variables
require 'settings.php';

/**
 * A preset mysqli extension
 */
class database extends mysqli {

   /**
    * The parameters are bound to transaction executions
    */
   private $transaction_parameters = [];

   /**
    * The statement is saved for transactions
    */
   private $transaction_statement;

   /**
    * Initializes a new instance of the database class
    */
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
      if ($types) {
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
      $query .= ' LIMIT 1';
      $data = $this->execute($query, $types, ...$parameters);
      if (gettype($data) !== 'array') {
         send_result("Unexpected result '" . json_encode($data) . "' from query '$query'", 500);
      }

      return count($data) > 0 ? $data[0] : $query;
   }

   /**
    * Prepares a batch of transactions
    * @param query The SQL query
    * @param types The first argument of mysqli_stmt::bind_params
    */
   function prepare_transaction($query, $types) {
      $this->begin_transaction();
      $this->transaction_statement = $this->prepare($query);
      if (!$this->transaction_statement) {
         send_result('Query failed: ' . $query, 500);
      }

      $this->transaction_statement->bind_param($types, ...$this->transaction_parameters);
   }

   /**
    * Adds a SQL statement for later execution
    * @param parameters An array containing the SQL binding values
    */
   function add_transaction(...$parameters) {
      $this->transaction_parameters = $parameters;
      if (!$this->transaction_statement->execute()) {
         $this->rollback();
         send_result('Query failed: ' . $query, 500);
      }
   }

   /**
    * Commits the transactions
    */
   function commit_transaction() {
      $this->transaction_statement->close();
      $this->commit();
      $this->transaction_parameters = [];
   }

   /**
    * Executes a transaction on the database
    * @param query The SQL query
    * @param types The first argument of mysqli_stmt::bind_params
    * @param array An array of arrays containing the SQL binding values
    */
   function transaction($query, $types = null, $array) {
      $this->begin_transaction();
      $statement = $this->prepare($query);
      if (!$statement) {
         send_result('Query failed: ' . $query, 500);
      }

      $statement->bind_param($types, ...$parameters);
      foreach ($array as $parameters) {
         if (!$statement->execute()) {
            $db->rollback();
            send_result('Query failed: ' . $query, 500);
         }
      }

      $statement->close();
      $this->commit();
   }
}

/**
 * Sets the user ID
 */
function get_user() {
   global $db;
   global $userId;

   /* Circumventing actual verification... */
   $userId = 1;
   return;

   // Check if a session exists
   if (isset($_SESSION['user_id']) && isset($_SESSION['verified'])) {
      $userId = $_SESSION['user_id'];
      return;
   }

   // Check for a cookie token if no sessions is active
   if (!isset($_COOKIE['token'])) {
      send_result('Not logged in', 401);
   }

   // Find the token...
   $query = $db->first('SELECT user_id, timestamp, user_agent, ip_address FROM tokens WHERE value = ?', 's', $_COOKIE['token']);
   if (!$query) {
      setcookie('token', null, $_SERVER['REQUEST_TIME'] - 42000, '/');
      send_result('Token not found', 401);
   }

   // ...and delete it
   $db->execute('DELETE FROM tokens WHERE value = ?', 's', $_COOKIE['token']);

   // Check if the token from the same user agent and IP address and is not outdated
   $tokenTime = strtotime($query[1]);
   if ($tokenTime < $_SERVER['REQUEST_TIME'] - 31622400 || $query[2] !== $_SERVER['HTTP_USER_AGENT'] || $query[3] !== $_SERVER['REMOTE_ADDR']) {
      setcookie('token', null, $_SERVER['REQUEST_TIME'] - 42000, '/');
      send_result('Invalid token', 401);
   }

   // Check if the user exists and is verified
   $userId = (int)$query[0];
   $query = $db->first('SELECT verified FROM users WHERE id = ?', 'i', $userId);
   if (!$query) {
      logoff();
      send_result('User not found', 401);
   }

   // Set the main session variable
   $_SESSION['user_id'] = $userId;
   $_SESSION['verified'] = (bool)$query[0];

   // Generate a new token
   set_token();
}

/**
 * Sets the game and player ID's
 */
function get_player() {
   global $db;
   global $params;
   global $userId;
   global $gameId;
   global $playerId;

   get_user();

   /* Circumventing actual verification... */
   $gameId = 1;
   $playerId = 1;
   return;

   if (!$_SESSION['verified']) {
      send_result('The e-mail address has not yet been verified', 403);
   }

   // Check the game
   if (!$_SESSION['game_id']) {

      // Check if the game ID is passed as a parameter
      if (!$params->game) {
         send_result('No game set', 403);
      }

      // Check if the game exists
      $query = $db->first('SELECT EXISTS (SELECT * FROM games WHERE id = ?)', 'i', $params->game);
      if (!$query) {
         send_result('Game not found', 403);
      }

      $_SESSION['game_id'] = $params->game;
   }

   // Retrieve the player ID
   if (!$_SESSION['player_id']) {
      $query = $db->first('SELECT id FROM players WHERE game_id = ? AND user_id = ?', 'ii', $_SESSION['game_id'], $userId);
      if (!$query) {
         send_result('Not a player in this game', 403);
      }

      $_SESSION['player_id'] = (int)$query[0];
   }

   // Set global variables
   $gameId = $_SESSION['game_id'];
   $playerId = $_SESSION['player_id'];
}

/**
 * Creates a new token and stores is as cookie and in the database
 */
function set_token() {
   global $db;

   if (!$_SESSION['user_id']) {
      throw new Exception('Error creating token: user unknown');
   }

   $token = generate_token();
   $db->execute(
      'INSERT INTO tokens (user_id, value, user_agent, ip_address) VALUES (?, ?, ?, ?)',
      'isss',
      $_SESSION['user_id'],
      $token,
      $_SERVER['HTTP_USER_AGENT'],
      $_SERVER['REMOTE_ADDR']
   );
   setcookie('token', $token, $_SERVER['REQUEST_TIME'] + 31622400, '/');
}

/**
 * Sends an e-mail to the user containing a link to verify the e-mail address
 * @param userId The user's ID
 * @param email The user's e-mail address
 */
function send_verification_email($userId, $email, $name) {
   global $db;

   $token = generate_token();
   $db->execute("INSERT INTO tokens (user_id, value, user_agent) VALUES (?, ?, ?)", 'iss', $userId, $token, 'verify');
   $body = "Hello $name," . PHP_EOL . PHP_EOL .
      'Please follow this link to verify your account:' . PHP_EOL .
      settings::$origin . "/account?token=$token" . PHP_EOL . PHP_EOL .
      'Kind regards,' . PHP_EOL .
      'The Open Civ team';
   if (!send_mail($email, $name, 'OpenCiv: verification', $body)) {
      $db->execute("DELETE FROM tokens WHERE value = ?", 's', $token);
      throw new Exception('Cannot send e-mail');
   }
}

/**
 * Send an e-mail and returns whether it succeeded
 * @param email The e-mail is sent to this e-mail address
 * @param name The name of the receiver
 * @param subject The title of the e-mail
 * @param body The content of the e-mail
 * @return boolean A value indicating whether the e-mail was sent successfully
 */
function send_mail($email, $name, $subject, $body) {
   $headers = 'From: ' . settings::$email . PHP_EOL .
      'Content-Type: text/plain;charset=utf-8' . PHP_EOL .
      'X-Mailer: PHP/' . phpversion();
   return mail("$name <$email>", $subject, $body, $headers);
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

/**
 * Destroys the session and token
 */
function logoff() {
   global $db;

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

// Start a session, open the database, and obtain post parameters
session_start();
$db = new database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $params = json_decode(trim(file_get_contents('php://input')));
}
?>