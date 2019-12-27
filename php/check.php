<?php
require 'init.php';
get_user();
$gameId = get_game();
send_result($gameId);
?>