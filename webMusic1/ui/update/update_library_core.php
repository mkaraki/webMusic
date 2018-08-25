<?php
if (!isset($_GET["session_core"])&&$_GET["session_core"]==""){
    die("Failed : No official session");
}

$session_id = $_GET['session_core'];

require(dirname(__FILE__).'/../../code/ui/update/update_library_core.php');

echo $log;
?>