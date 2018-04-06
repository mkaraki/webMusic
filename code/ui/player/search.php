<?php
require (dirname(__FILE__)."/../../connect_sql.php");

$s_str="";
$s_tar="";

function set_sstr($a1){
  global $s_str;
  $s_str = mb_convert_encoding($a1,"UTF-8","auto");
}
function set_star($a1){
  global $s_tar;
  $s_tar="$a1";
}

function get_album_res($sstr,$star){
  global $sql_host;
  echo "<table id=altable class=tablesorter>";
  echo "<thead><tr><th>Name</th></tr>\n";
  echo "</thead><tbody>\n";
  $albumlist=$sql_host->query("SELECT * FROM album where name like '%$sstr%'");
  while ($meat = $albumlist->fetchArray()) {
    echo '<tr><td><a href="/ui/player/open_song.php?album='.$meat['id'].'">'.$meat['name']."</a></td></tr>\n";
  }
  echo "</tbody></table>\n";
}
function get_artist_res($sstr,$star){
  global $sql_host;
  echo "<table id=artable class=tablesorter>";
  echo "<thead><tr><th>Name</th></tr>\n";
  echo "</thead><tbody>\n";
  $artistlist=$sql_host->query("SELECT * FROM artist where name like '%$sstr%'");
  while ($meat = $artistlist->fetchArray()) {
    echo '<tr><td><a href="/ui/player/open_song.php?artist='.$meat['id'].'">'.$meat['name']."</a></td></tr>\n";
  }
  echo "</tbody></table>\n";
}
function get_song_res($sstr,$star){
  global $sql_host;
  echo "<table id=sltable class=tablesorter>";
  echo "<thead><tr><th>Name</th><th>Album</th><th>Track No.</th><th>Artist</th></tr>\n";
  echo "</thead><tbody>\n";
  $songlist=$sql_host->query("SELECT * FROM song where name like '%$sstr%'");
  while ($meat = $songlist->fetchArray()) {
    if ($meat['artist_id'] == 0){$artnm="Unknown Artist";}else{
      $artnm=$sql_host->querySingle("SELECT name FROM artist where id = ".$meat['artist_id']);
    }
    if ($meat['album_id'] == 0){$albmnm="Unknown Artist";}else{
      $albmnm=$sql_host->querySingle("SELECT name FROM album where id = ".$meat['album_id']);
    }
    echo '<tr><td><a href="/ui/player/player.php?id='.$meat['id'].'">'.$meat['name'].'</a></td><td><a href="/ui/player/open_song.php?album='.$meat['album_id'].'">'.$albmnm."</td><td>".$meat['tnum'].'</td><td><a href="/ui/player/open_song.php?artist='.$meat['artist_id'].'">'.$artnm."</a></td></tr>\n";
  }
  echo '<script>jQuery(function(){jQuery( "#sltable" ).tablesorter({sortList: [[ 1, 0 ],[ 2, 0 ]]});});</script>';
  echo "</tbody></table>\n";
}

function put_toggleoc($a1,$a2){
  if ($a1 == 0){
    echo '<div class="hide"><h3 class="trigger">'.$a2.'</h3><div class="target">'."\n";
  }elseif($a1==1){
    echo "</div></div>\n";
  }
}

function res(){
  global $s_tar;
  global $s_str;

  switch($s_tar){
    case "all":
      put_toggleoc(0,"Album");
      get_album_res($s_str,$s_tar);
      put_toggleoc(1,"Album");
      put_toggleoc(0,"Artist");
      get_artist_res($s_str,$s_tar);
      put_toggleoc(1,"Artist");
      put_toggleoc(0,"Song");
      get_song_res($s_str,$s_tar);
      put_toggleoc(1,"Song");
      break;
    case "album";
      echo "<h3>Album</h3>\n";
      get_album_res($s_str,$s_tar);
      break;
    case "artist";
      echo "<h3>Artist</h3>\n";
      get_artist_res($s_str,$s_tar);
      break;
    case "song";
      echo "<h3>Song</h3>\n";
      get_song_res($s_str,$s_tar);
      break;
  }
}
?>
