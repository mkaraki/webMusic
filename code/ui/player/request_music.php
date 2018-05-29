<?php
require (dirname(__FILE__)."/../../connect_sql.php");
require_once(dirname(__FILE__).'/../../../lib/getid3/getid3.php');

$music_path=$sql_host->querySingle("SELECT path FROM song where id = ".$request_id);

$mime = "";

if ($music_path==""){die("e0");}
if (!file_exists(dirname(__FILE__)."/../../../".$music_path)){die("e1");}

$getID3 = new getID3();
$getID3->analyze(dirname(__FILE__)."/../../../$music_path");

if (isset($getID3->info['mime_type'])) {$mime = $getID3->info['mime_type'];}else{$mime = "application/octet-stream";}

header("Content-Type: $mime");
header("Content-Disposition: attachment; filename=".basename("./file/".$_GET['id']));
readfile(dirname(__FILE__)."/../../../".$music_path);
?>