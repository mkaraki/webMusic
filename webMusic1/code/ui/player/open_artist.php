<?php
require (dirname(__FILE__)."/../../connect_sql.php");
require_once(dirname(__FILE__).'/../../../lib/getid3/getid3.php');

function link_artist(){
  global $sql_host;
  echo "<table border=1 id=artable class=tablesorter><thead><tr><th>Artist</th><th>Search</th></tr>\n";
  echo "</thead><tbody>\n";
  $albumlist=$sql_host->query("SELECT * FROM artist");
  while ($meat = $albumlist->fetchArray()) {
    $gsearch_probider=urlencode($meat['name']);
    echo '<tr><td><a href="/ui/player/open_song.php?artist='.$meat['id'].'">'.$meat['name'].'</a></td><td><a href="https://www.google.com/search?q='.$gsearch_probider.'">Google</a></td></tr>'."\n";
  }
  echo "</tbody></table>\n";
}
?>
