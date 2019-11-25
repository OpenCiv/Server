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
   $result = $db->query('SELECT `Timestamp`, `UserId`, `UserAgent` FROM `Tokens` WHERE `Value` = ?', 's', $_COOKIE['token']);

   // If the token is found...
   if (!empty($result)) {

      // ...delete it
      $db->query('DELETE FROM `Tokens` WHERE `Value` = ?', 's', $_COOKIE['token']);

      // Check if the token from the same user agent (browser, OS) and is not outdated
      $tokenTime = strtotime($tokenTime);
      $result = $userAgent == $_SERVER['HTTP_USER_AGENT'] && $tokenTime > $timestamp - 34560000;
      if ($result) {

         // Set the main session variable
         $_SESSION['userId'] = $userId;

         // Create a new token
         $token = generate_token();
         $db->query('INSERT INTO `Tokens` (`UserId`, `Value`, `UserAgent`) VALUES (?, ?, ?)', 'iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
         setcookie('token', $token, $timestamp + 31622400, '/');
      }
   }

// No token cookie found
} else {
   $result = false;
}

send_result($result);
?>