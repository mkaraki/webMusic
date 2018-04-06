<?php
require (dirname(__FILE__)."/connect_sql.php");

global $sql_host;
$albumlist=$sql_host->query("SELECT * FROM album");
while ($meat = $albumlist->fetchArray()) {
  echo $meat['id']."  --  ".$meat['name']."\n";
}
?>
