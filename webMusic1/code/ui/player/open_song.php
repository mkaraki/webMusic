<?php
require (dirname(__FILE__)."/../../connect_sql.php");

function table_song_list($type,$filt){
  global $sql_host;
  echo "<table border=1 id=sltable class=tablesorter><thead><tr>\n";
  if ($type == "album"){$songlist=$sql_host->query("SELECT * FROM song where album_id = ".$filt);
  echo "<th>Track No.</th><th>Title</th><th>Artist</th></tr></thead><tbody>";$tp=1;}
  elseif($type == "artist"){$songlist=$sql_host->query("SELECT * FROM song where artist_id = ".$filt);
  echo "<th>Title</th><th>Album</th><th>Track No.</th></tr></thead><tbody>";$tp=2;}
  else{$songlist=$sql_host->query("SELECT * FROM song");
  echo "<th>Title</th><th>Album</th><th>Track No.</th><th>Artist</th></tr></thead><tbody>";$tp=3;}

  while ($meat = $songlist->fetchArray()) {
    if ($tp == 1){
      if ($meat['artist_id'] == 0){$artnm="Unknown Artist";}else{
        $artnm=$sql_host->querySingle("SELECT name FROM artist where id = ".$meat['artist_id']);
      }
      echo "<tr><td>".$meat['tnum'].'</td><td><a href="/ui/player/player.php?id='.$meat['id'].'">'.$meat['name'].'</a></td><td><a href="/ui/player/open_song.php?artist='.$meat['artist_id'].'">'.$artnm."</a></td></tr>\n";
      echo '<script>jQuery(function(){jQuery( "#sltable" ).tablesorter({sortList: [[ 0, 0 ]]});});</script>';
    }elseif ($tp == 2){
      if ($meat['album_id'] == 0){$albmnm="Unknown Artist";}else{
        $albmnm=$sql_host->querySingle("SELECT name FROM album where id = ".$meat['album_id']);
      }
      echo '<tr><td><a href="/ui/player/player.php?id='.$meat['id'].'">'.$meat['name'].'</a></td><td><a href="/ui/player/open_song.php?album='.$meat['album_id'].'">'.$albmnm."</td><td>".$meat['tnum']."</td></tr>\n";
      echo '<script>jQuery(function(){jQuery( "#sltable" ).tablesorter({sortList: [[ 1, 0 ],[ 2, 0 ]]});});</script>';
    }else{
      if ($meat['artist_id'] == 0){$artnm="Unknown Artist";}else{
        $artnm=$sql_host->querySingle("SELECT name FROM artist where id = ".$meat['artist_id']);
      }
      if ($meat['album_id'] == 0){$albmnm="Unknown Artist";}else{
        $albmnm=$sql_host->querySingle("SELECT name FROM album where id = ".$meat['album_id']);
      }
      echo '<tr><td><a href="/ui/player/player.php?id='.$meat['id'].'">'.$meat['name'].'</a></td><td><a href="/ui/player/open_song.php?album='.$meat['album_id'].'">'.$albmnm."</td><td>".$meat['tnum'].'</td><td><a href="/ui/player/open_song.php?artist='.$meat['artist_id'].'">'.$artnm."</a></td></tr>\n";
      echo '<script>jQuery(function(){jQuery( "#sltable" ).tablesorter({sortList: [[ 1, 0 ],[ 2, 0 ]]});});</script>';
    }
  }
  echo "</tbody></table>\n";
}
?>
