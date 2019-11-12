<?php
require 'init.php';

// Check if the user is still logged in
if (isset($_SESSION['userId'])) {
   $result = true;
}

// Check if a token cookie is set
elseif (isset($_COOKIE['token'])) {
   $db = new database();

   // Find the token
   $statement = $db->stmt_init();
   $statement->prepare('SELECT `Timestamp`, `UserId`, `UserAgent` FROM `Tokens` WHERE `Value` = ?');
   $statement->bind_param('s', $_COOKIE['token']);
   $statement->execute();
   $statement->bind_result($tokenTime, $userId, $userAgent);
   $result = $statement->fetch();
   $statement->close();

   // If the token is found...
   if ($result) {

      // ...delete it
      $statement = $db->stmt_init();
      $statement->prepare('DELETE FROM `Tokens` WHERE `Value` = ?');
      $statement->bind_param('s', $_COOKIE['token']);
      $statement->execute();
      $statement->close();

      // Check if the token from the same user agent (browser, OS) and is not outdated
      $tokenTime = strtotime($tokenTime);
      $result = $userAgent == $_SERVER['HTTP_USER_AGENT'] && $tokenTime > $timestamp - 34560000;
      if ($result) {

         // Set the main session variable
         $_SESSION['userId'] = $userId;

         // Create a new token
         $token = generate_token();
         $statement = $db->stmt_init();
         $statement->prepare('INSERT INTO `Tokens` (`UserId`, `Value`, `UserAgent`) VALUES (?, ?, ?)');
         $statement->bind_param('iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
         $statement->execute();
         $statement->close();
         setcookie('token', $token, $timestamp + 31622400, '/');
      }
   }

// No token cookie found
} else {
   $result = false;
}

send_result($result);
?>