<?php
require 'init.php';
get_user();
if (!$params || !$params->name) {
   send_result('Parameter missing', 400);
}

// Create a new game
$db->execute('INSERT INTO games (x, y, name) VALUES (?, ?, ?)', 'iis', 32, 16, $params->name);
$query = $db->first('SELECT LAST_INSERT_ID()');
$gameId = (int)$query[0];
$_SESSION['game_id'] = $gameId;

// Add the user as player
$db->execute('INSERT INTO players (user_id, game_id, name) VALUES (?, ?, (SELECT name from users where id = ?))', 'iii', $userId, $gameId, $userId);
$query = $db->first('SELECT LAST_INSERT_ID()');
$playerId = (int)$query[0];
$_SESSION['player_id'] = $playerId;

// The map is from a template
$map = [
   [0, 0, 'water'], [1, 0, 'grass'], [2, 0, 'grass'], [3, 0, 'grass'], [4, 0, 'grass'], [5, 0, 'grass'], [6, 0, 'grass'], [7, 0, 'water'], [8, 0, 'water'], [9, 0, 'water'], [10, 0, 'water'], [11, 0, 'water'], [12, 0, 'water'], [13, 0, 'water'], [14, 0, 'water'], [15, 0, 'water'], [16, 0, 'water'], [17, 0, 'water'], [18, 0, 'water'], [19, 0, 'water'], [20, 0, 'water'], [21, 0, 'water'], [22, 0, 'water'], [23, 0, 'water'], [24, 0, 'water'], [25, 0, 'water'], [26, 0, 'water'], [27, 0, 'water'], [28, 0, 'water'], [29, 0, 'water'], [30, 0, 'water'], [31, 0, 'water'], 
   [0, 1, 'water'], [1, 1, 'grass'], [2, 1, 'water'], [3, 1, 'grass'], [4, 1, 'grass'], [5, 1, 'water'], [6, 1, 'grass'], [7, 1, 'water'], [8, 1, 'water'], [9, 1, 'water'], [10, 1, 'water'], [11, 1, 'water'], [12, 1, 'water'], [13, 1, 'water'], [14, 1, 'water'], [15, 1, 'water'], [16, 1, 'water'], [17, 1, 'water'], [18, 1, 'water'], [19, 1, 'water'], [20, 1, 'water'], [21, 1, 'water'], [22, 1, 'water'], [23, 1, 'water'], [24, 1, 'water'], [25, 1, 'water'], [26, 1, 'water'], [27, 1, 'water'], [28, 1, 'water'], [29, 1, 'water'], [30, 1, 'water'], [31, 1, 'water'], 
   [0, 2, 'water'], [1, 2, 'water'], [2, 2, 'water'], [3, 2, 'grass'], [4, 2, 'grass'], [5, 2, 'water'], [6, 2, 'water'], [7, 2, 'water'], [8, 2, 'water'], [9, 2, 'water'], [10, 2, 'grass'], [11, 2, 'grass'], [12, 2, 'grass'], [13, 2, 'grass'], [14, 2, 'water'], [15, 2, 'water'], [16, 2, 'water'], [17, 2, 'grass'], [18, 2, 'grass'], [19, 2, 'water'], [20, 2, 'grass'], [21, 2, 'grass'], [22, 2, 'water'], [23, 2, 'water'], [24, 2, 'grass'], [25, 2, 'grass'], [26, 2, 'water'], [27, 2, 'grass'], [28, 2, 'grass'], [29, 2, 'grass'], [30, 2, 'water'], [31, 2, 'water'], 
   [0, 3, 'water'], [1, 3, 'water'], [2, 3, 'water'], [3, 3, 'grass'], [4, 3, 'grass'], [5, 3, 'water'], [6, 3, 'water'], [7, 3, 'water'], [8, 3, 'water'], [9, 3, 'grass'], [10, 3, 'grass'], [11, 3, 'water'], [12, 3, 'water'], [13, 3, 'grass'], [14, 3, 'grass'], [15, 3, 'water'], [16, 3, 'grass'], [17, 3, 'grass'], [18, 3, 'grass'], [19, 3, 'grass'], [20, 3, 'grass'], [21, 3, 'grass'], [22, 3, 'grass'], [23, 3, 'water'], [24, 3, 'water'], [25, 3, 'grass'], [26, 3, 'grass'], [27, 3, 'water'], [28, 3, 'water'], [29, 3, 'grass'], [30, 3, 'grass'], [31, 3, 'water'], 
   [0, 4, 'water'], [1, 4, 'water'], [2, 4, 'water'], [3, 4, 'grass'], [4, 4, 'grass'], [5, 4, 'water'], [6, 4, 'water'], [7, 4, 'water'], [8, 4, 'water'], [9, 4, 'grass'], [10, 4, 'grass'], [11, 4, 'grass'], [12, 4, 'grass'], [13, 4, 'grass'], [14, 4, 'grass'], [15, 4, 'water'], [16, 4, 'grass'], [17, 4, 'grass'], [18, 4, 'water'], [19, 4, 'grass'], [20, 4, 'water'], [21, 4, 'grass'], [22, 4, 'grass'], [23, 4, 'water'], [24, 4, 'water'], [25, 4, 'grass'], [26, 4, 'grass'], [27, 4, 'water'], [28, 4, 'water'], [29, 4, 'grass'], [30, 4, 'grass'], [31, 4, 'water'], 
   [0, 5, 'water'], [1, 5, 'water'], [2, 5, 'water'], [3, 5, 'grass'], [4, 5, 'grass'], [5, 5, 'water'], [6, 5, 'water'], [7, 5, 'water'], [8, 5, 'water'], [9, 5, 'grass'], [10, 5, 'grass'], [11, 5, 'water'], [12, 5, 'water'], [13, 5, 'water'], [14, 5, 'water'], [15, 5, 'water'], [16, 5, 'grass'], [17, 5, 'grass'], [18, 5, 'water'], [19, 5, 'grass'], [20, 5, 'water'], [21, 5, 'grass'], [22, 5, 'grass'], [23, 5, 'water'], [24, 5, 'water'], [25, 5, 'grass'], [26, 5, 'grass'], [27, 5, 'grass'], [28, 5, 'grass'], [29, 5, 'grass'], [30, 5, 'water'], [31, 5, 'water'], 
   [0, 6, 'water'], [1, 6, 'water'], [2, 6, 'grass'], [3, 6, 'grass'], [4, 6, 'grass'], [5, 6, 'grass'], [6, 6, 'water'], [7, 6, 'water'], [8, 6, 'water'], [9, 6, 'water'], [10, 6, 'grass'], [11, 6, 'grass'], [12, 6, 'grass'], [13, 6, 'grass'], [14, 6, 'water'], [15, 6, 'water'], [16, 6, 'grass'], [17, 6, 'grass'], [18, 6, 'water'], [19, 6, 'water'], [20, 6, 'water'], [21, 6, 'grass'], [22, 6, 'grass'], [23, 6, 'water'], [24, 6, 'water'], [25, 6, 'grass'], [26, 6, 'grass'], [27, 6, 'water'], [28, 6, 'water'], [29, 6, 'water'], [30, 6, 'water'], [31, 6, 'water'], 
   [0, 7, 'water'], [1, 7, 'water'], [2, 7, 'water'], [3, 7, 'water'], [4, 7, 'water'], [5, 7, 'water'], [6, 7, 'water'], [7, 7, 'water'], [8, 7, 'water'], [9, 7, 'water'], [10, 7, 'water'], [11, 7, 'water'], [12, 7, 'water'], [13, 7, 'water'], [14, 7, 'water'], [15, 7, 'water'], [16, 7, 'water'], [17, 7, 'water'], [18, 7, 'water'], [19, 7, 'water'], [20, 7, 'water'], [21, 7, 'water'], [22, 7, 'water'], [23, 7, 'water'], [24, 7, 'grass'], [25, 7, 'grass'], [26, 7, 'grass'], [27, 7, 'grass'], [28, 7, 'water'], [29, 7, 'water'], [30, 7, 'water'], [31, 7, 'water'], 
   [0, 8, 'water'], [1, 8, 'water'], [2, 8, 'grass'], [3, 8, 'grass'], [4, 8, 'grass'], [5, 8, 'water'], [6, 8, 'water'], [7, 8, 'water'], [8, 8, 'water'], [9, 8, 'water'], [10, 8, 'water'], [11, 8, 'water'], [12, 8, 'water'], [13, 8, 'water'], [14, 8, 'water'], [15, 8, 'water'], [16, 8, 'water'], [17, 8, 'water'], [18, 8, 'water'], [19, 8, 'water'], [20, 8, 'water'], [21, 8, 'water'], [22, 8, 'water'], [23, 8, 'water'], [24, 8, 'water'], [25, 8, 'water'], [26, 8, 'water'], [27, 8, 'water'], [28, 8, 'water'], [29, 8, 'water'], [30, 8, 'water'], [31, 8, 'water'], 
   [0, 9, 'water'], [1, 9, 'water'], [2, 9, 'water'], [3, 9, 'grass'], [4, 9, 'grass'], [5, 9, 'water'], [6, 9, 'water'], [7, 9, 'water'], [8, 9, 'water'], [9, 9, 'water'], [10, 9, 'water'], [11, 9, 'water'], [12, 9, 'water'], [13, 9, 'water'], [14, 9, 'water'], [15, 9, 'water'], [16, 9, 'water'], [17, 9, 'water'], [18, 9, 'grass'], [19, 9, 'grass'], [20, 9, 'water'], [21, 9, 'water'], [22, 9, 'water'], [23, 9, 'water'], [24, 9, 'water'], [25, 9, 'water'], [26, 9, 'water'], [27, 9, 'water'], [28, 9, 'water'], [29, 9, 'water'], [30, 9, 'water'], [31, 9, 'water'], 
   [0, 10, 'water'], [1, 10, 'water'], [2, 10, 'water'], [3, 10, 'grass'], [4, 10, 'grass'], [5, 10, 'water'], [6, 10, 'water'], [7, 10, 'water'], [8, 10, 'water'], [9, 10, 'grass'], [10, 10, 'grass'], [11, 10, 'grass'], [12, 10, 'grass'], [13, 10, 'water'], [14, 10, 'water'], [15, 10, 'water'], [16, 10, 'water'], [17, 10, 'grass'], [18, 10, 'grass'], [19, 10, 'grass'], [20, 10, 'grass'], [21, 10, 'grass'], [22, 10, 'water'], [23, 10, 'water'], [24, 10, 'water'], [25, 10, 'water'], [26, 10, 'grass'], [27, 10, 'grass'], [28, 10, 'grass'], [29, 10, 'grass'], [30, 10, 'water'], [31, 10, 'water'], 
   [0, 11, 'water'], [1, 11, 'water'], [2, 11, 'water'], [3, 11, 'grass'], [4, 11, 'grass'], [5, 11, 'water'], [6, 11, 'water'], [7, 11, 'water'], [8, 11, 'water'], [9, 11, 'water'], [10, 11, 'water'], [11, 11, 'water'], [12, 11, 'grass'], [13, 11, 'grass'], [14, 11, 'water'], [15, 11, 'water'], [16, 11, 'water'], [17, 11, 'water'], [18, 11, 'grass'], [19, 11, 'grass'], [20, 11, 'water'], [21, 11, 'water'], [22, 11, 'water'], [23, 11, 'water'], [24, 11, 'water'], [25, 11, 'grass'], [26, 11, 'grass'], [27, 11, 'water'], [28, 11, 'water'], [29, 11, 'grass'], [30, 11, 'grass'], [31, 11, 'water'], 
   [0, 12, 'water'], [1, 12, 'water'], [2, 12, 'water'], [3, 12, 'grass'], [4, 12, 'grass'], [5, 12, 'water'], [6, 12, 'water'], [7, 12, 'water'], [8, 12, 'water'], [9, 12, 'grass'], [10, 12, 'grass'], [11, 12, 'grass'], [12, 12, 'grass'], [13, 12, 'grass'], [14, 12, 'water'], [15, 12, 'water'], [16, 12, 'water'], [17, 12, 'water'], [18, 12, 'grass'], [19, 12, 'grass'], [20, 12, 'water'], [21, 12, 'water'], [22, 12, 'water'], [23, 12, 'water'], [24, 12, 'water'], [25, 12, 'grass'], [26, 12, 'grass'], [27, 12, 'grass'], [28, 12, 'grass'], [29, 12, 'grass'], [30, 12, 'grass'], [31, 12, 'water'], 
   [0, 13, 'water'], [1, 13, 'water'], [2, 13, 'water'], [3, 13, 'grass'], [4, 13, 'grass'], [5, 13, 'water'], [6, 13, 'water'], [7, 13, 'water'], [8, 13, 'grass'], [9, 13, 'grass'], [10, 13, 'water'], [11, 13, 'water'], [12, 13, 'grass'], [13, 13, 'grass'], [14, 13, 'water'], [15, 13, 'water'], [16, 13, 'water'], [17, 13, 'water'], [18, 13, 'grass'], [19, 13, 'grass'], [20, 13, 'water'], [21, 13, 'grass'], [22, 13, 'grass'], [23, 13, 'water'], [24, 13, 'water'], [25, 13, 'grass'], [26, 13, 'grass'], [27, 13, 'water'], [28, 13, 'water'], [29, 13, 'water'], [30, 13, 'water'], [31, 13, 'water'], 
   [0, 14, 'water'], [1, 14, 'water'], [2, 14, 'grass'], [3, 14, 'grass'], [4, 14, 'grass'], [5, 14, 'grass'], [6, 14, 'water'], [7, 14, 'water'], [8, 14, 'water'], [9, 14, 'grass'], [10, 14, 'grass'], [11, 14, 'grass'], [12, 14, 'water'], [13, 14, 'grass'], [14, 14, 'grass'], [15, 14, 'water'], [16, 14, 'water'], [17, 14, 'water'], [18, 14, 'water'], [19, 14, 'grass'], [20, 14, 'grass'], [21, 14, 'grass'], [22, 14, 'water'], [23, 14, 'water'], [24, 14, 'water'], [25, 14, 'water'], [26, 14, 'grass'], [27, 14, 'grass'], [28, 14, 'grass'], [29, 14, 'grass'], [30, 14, 'water'], [31, 14, 'water'], 
   [0, 15, 'water'], [1, 15, 'water'], [2, 15, 'water'], [3, 15, 'water'], [4, 15, 'water'], [5, 15, 'water'], [6, 15, 'water'], [7, 15, 'water'], [8, 15, 'water'], [9, 15, 'water'], [10, 15, 'water'], [11, 15, 'water'], [12, 15, 'water'], [13, 15, 'water'], [14, 15, 'water'], [15, 15, 'water'], [16, 15, 'water'], [17, 15, 'water'], [18, 15, 'water'], [19, 15, 'water'], [20, 15, 'water'], [21, 15, 'water'], [22, 15, 'water'], [23, 15, 'water'], [24, 15, 'water'], [25, 15, 'water'], [26, 15, 'water'], [27, 15, 'water'], [28, 15, 'water'], [29, 15, 'water'], [30, 15, 'water'], [31, 15, 'water']
];
/*
$db->prepare_transaction("INSERT INTO terrain (game_id, x, y, type) VALUES ($gameId, ?, ?, ?)", 'iis');
foreach ($map as $tile) {
   $db->add_transaction($tile[0], $tile[1], $type[2]);
}

$db->commit_transaction();
*/
$db->begin_transaction();
$statement = $db->prepare("INSERT INTO terrain (game_id, x, y, type) VALUES ($gameId, ?, ?, ?)");
$statement->bind_param('iis', $x, $y, $type);
foreach ($map as $tile) {
   $x = $tile[0];
   $y = $tile[1];
   $type = $tile[2];
   if (!$statement->execute()) {
      $db->rollback();
      send_result('Query failed: ' . $query, 500);
   }
}

$statement->close();
$db->commit();

$db->execute("INSERT INTO units (player_id, x, y) VALUES ($playerId, 1, 0)");

send_result($gameId);
?>