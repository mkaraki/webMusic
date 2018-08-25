<?php 
if (!isset($_GET['id'])){die("Unknown Request");}
$request_id=$_GET['id'];
require(dirname(__FILE__).'/../../code/ui/player/request_music.php'); 

//header("Content-Type: application/octet-stream");
header("Content-Type: $mime");
header("Content-Disposition: attachment; filename=stream");
readfile($music_full_path);
?>