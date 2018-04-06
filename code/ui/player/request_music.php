<?php
require (dirname(__FILE__)."/../../connect_sql.php");

$music_path=$sql_host->querySingle("SELECT path FROM song where id = ".$request_id);

if ($music_path==""){die("e0");}
if (!file_exists(dirname(__FILE__)."/../../../".$music_path)){die("e1");}
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=".basename("./file/".$_GET['id']));
readfile(dirname(__FILE__)."/../../../".$music_path);
?>