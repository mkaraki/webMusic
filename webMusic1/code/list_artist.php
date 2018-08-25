<?php
require (dirname(__FILE__)."/connect_sql.php");

$artistlist=$sql_host->query("SELECT * FROM artist");
while ($meat = $artistlist->fetchArray()) {
  echo $meat['id']."  --  ".$meat['name']."\n";
}
?>
