<?php
require (dirname(__FILE__)."/connect_sql.php");

$songlist=$sql_host->query("SELECT * FROM song");
while ($meat = $songlist->fetchArray()) {
  if ($meat['artist_id'] == 0){$artnm="Unknown Artist";}else{
    $artnm=$sql_host->querySingle("SELECT * FROM artist where id = ".$meat['artist_id']);
  }
  if ($meat['album_id'] == 0){$albmnm="Unknown Artist";}else{
    $albmnm=$sql_host->querySingle("SELECT * FROM album where id = ".$meat['album_id']);
  }
  echo $meat['id']."  --  ".$meat['name']."  --  $albmnm  --  ".$meat['tnum']."  --  $artnm\n";
}
?>
