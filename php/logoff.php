<?php
require 'init.php';

// Remove the token and terminate the session
logoff();

// Send response
send_result('Logged off');
?>