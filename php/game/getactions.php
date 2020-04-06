<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id)) {
   send_result('Parameter missing', 400);
}

$query = $db->execute('SELECT ordering, type, parameters FROM actions WHERE unit_id = ? ORDER BY ordering', 'i', $params->id);
$actions = [];
foreach ($query as $action) {
   $actions[] = ['order' => (int)$action[0], 'type' => $action[1], 'parameter' => $action[2]];
}

send_result($actions);
?>