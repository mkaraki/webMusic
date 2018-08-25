<?php
require (dirname(__FILE__)."/../../connect_sql.php");
require_once(dirname(__FILE__).'/../../../lib/getid3/getid3.php');

$music_path=$sql_host->querySingle("SELECT path FROM song where id = ".$request_id);

$mime = "";

if ($music_path==""){die("e0");}
if (!file_exists(dirname(__FILE__)."/../../../".$music_path)){die("e1");}

$getID3 = new getID3();
$fI = $getID3->analyze(dirname(__FILE__)."/../../../$music_path");
getid3_lib::CopyTagsToComments($fI);

if (isset($fI['mime_type'])) {$mime = $fI['mime_type'];}else{$mime = "application/octet-stream";}

$music_full_path = dirname(__FILE__)."/../../../".$music_path;
?>