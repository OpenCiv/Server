<?php
require 'init.php';

// Check if the user is still logged in
if (isset($_SESSION['user_id'])) {
   $result = true;
}

// Check if a token cookie is set
elseif (isset($_COOKIE['token'])) {

   // Find the token
   $query = $db->execute('SELECT `timestamp`, `user_id`, `user_agent` FROM `tokens` WHERE `value` = ?', 's', $_COOKIE['token']);

   // If the token is found...
   if (!empty($query)) {

      // ...delete it
      $db->execute('DELETE FROM `tokens` WHERE `value` = ?', 's', $_COOKIE['token']);

      // Check if the token from the same user agent (browser, OS) and is not outdated
      $tokenTime = strtotime($tokenTime);
      $result = $userAgent == $_SERVER['HTTP_USER_AGENT'] && $tokenTime > $timestamp - 31622400;
      if ($result) {
         $userId = $query['user_id'];

         // Set the main session variable
         $_SESSION['user_id'] = $userId;

         // Create a new token
         $token = generate_token();
         $db->execute('INSERT INTO `tokens` (`user_id`, `value`, `user_agent`) VALUES (?, ?, ?)', 'iss', $userId, $token, $_SERVER['HTTP_USER_AGENT']);
         setcookie('token', $token, $timestamp + 31622400, '/');
      }
   }

// No token cookie found
} else {
   $result = false;
}

// Send response
send_result($result);
?>