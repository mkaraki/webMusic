<?php
require (dirname(__FILE__)."/../../connect_sql.php");
require_once(dirname(__FILE__).'/../../../lib/getid3/getid3.php');

function link_album(){
  global $sql_host;
  $albumlist=$sql_host->query("SELECT * FROM album");
  while ($meat = $albumlist->fetchArray()) {
    $songpath=$sql_host->querySingle("SELECT path FROM song where album_id = ".$meat['id']." limit 1");
    $getID3 = new getID3();
    $fI = $getID3->analyze(dirname(__FILE__)."/../../../$songpath");
    getid3_lib::CopyTagsToComments($fI);
    echo '<a href="/ui/player/open_song.php?album='.$meat['id'].'">';
    if(isset($fI['comments']['picture'][0]['image_mime'])){
      echo '<img title="'.$meat['name'].'" height="150" src="data:'.$fI['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($fI['comments']['picture'][0]['data']).'">';
    }else{
      echo '<img title="'.$meat['name'].'" height="150" src="/src/img/aart/no_art.svg">';
    }
    echo "</a>\n";
  }
}
?>