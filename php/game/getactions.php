<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id)) {
   send_result('Parameter missing', 400);
}

$unitId = $params->id;
$actionQuery = $db->execute('SELECT ordering, type, parameters FROM actions WHERE unit_id = ? ORDER BY ordering', 'i', $unitId);
$actions = [];
foreach ($actionQuery as $action) {
   $type = $action[1];
   if ($type === 'move') {
      if (!$oldX) {
         $locationQuery = $db->first('SELECT x, y FROM units WHERE id = ?', 'i', $unitId);
         $oldX = (int)$locationQuery[0];
         $oldY = (int)$locationQuery[1];
      }

      $destination = explode(',', $action[2]);
      $newX = (int)$destination[0];
      $newY = (int)$destination[1];
      $parameter = get_path($oldX, $oldY, $newX, $newY);
      $oldX = $newX;
      $oldY = $newY;
   } else {
      $parameter = $action[2];
   }

   $actions[] = ['order' => (int)$action[0], 'type' => $type, 'parameter' => $parameter];
}

send_result($actions);
?>