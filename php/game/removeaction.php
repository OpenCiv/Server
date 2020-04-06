<?php
require '../init.php';
get_player();

if (!$params || !isset($params->id) || !isset($params->order)) {
   send_result('Parameter missing', 400);
}

$db->execute('DELETE FROM actions WHERE unit_id = ? AND ordering = ?', 'ii', $params->id, $params->order);
$db->execute('UPDATE actions SET ordering = ordering - 1 WHERE ordering > ?', 'i', $params->order);
send_result(true);
?>