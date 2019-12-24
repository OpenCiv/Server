<?php
require 'init.php';
verify_user_id($userId);
if (!$params || !$params->request || !$userId) {
   send_result(false, 400);
}

switch ($params->request) {

   // Returns the user
   case 'getuser':
      $query = $db->first('SELECT email, name, verified FROM users WHERE id = ?', 'i', $userId);
      $user = ['email' => $query[0], 'name' => $query[1], 'verified' => (bool)$query[2]];
      send_result($user);

   // Verifies the old password and replaces it with a new password the user has inputted
   case 'changepassword':
      $query = $db->first('SELECT password FROM users WHERE id = ?', 'i', $userId);
      if (!password_verify($params->oldpass, $query[0])) {
         send_result(false);
      }

      $db->execute('UPDATE users SET password = ? WHERE id = ?', 'si', password_hash($params->newpass, PASSWORD_BCRYPT), $userId);
      send_result(true);

   // Replaces the user's password with eight random characters and sends it by e-mail
   case 'newpassword':
      $password = substr(generate_token(), 0, 8);
      $db->execute('UPDATE users SET password = ? WHERE id = ?', 'si', password_hash($password, PASSWORD_BCRYPT), $userId);
      $query = $db->first('SELECT email, name FROM users WHERE id = ?', 'i', $userId);
      $email = $query[0];
      $name = $query[1];
      $body = "Hello $name," . PHP_EOL . PHP_EOL .
      'As requested, here is your new password: ' . $password . PHP_EOL . PHP_EOL .
      'Kind regards,' . PHP_EOL .
      'The Open Civ team';
      send_mail($email, $name, 'Open Civ: new password');
      send_result(true);

   case 'resend':
      $query = $db->first('SELECT email, name FROM users WHERE id = ?', 'i', $userId);
      send_verification_email($userId, $query[0], $query[1]);
      send_result(true);

   case 'getgames':
      $query = $db->execute('SELECT game.id, game.name FROM games game INNER JOIN players player ON (game.id = player.game_id) WHERE player.user_id = ?', 'i', $userId);
      $games = [];
      foreach ($query as $game) {
         $games[] = ['id' => (int)$game[0], 'name' => $game[1]];
      }

      send_result($games);

   default:
      send_result(false, 400);
}
?>