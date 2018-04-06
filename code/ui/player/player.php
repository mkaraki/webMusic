<?php
require (dirname(__FILE__)."/../../connect_sql.php");
require_once(dirname(__FILE__).'/../../../lib/getid3/getid3.php');

$songinfo=$sql_host->query("SELECT * FROM song where id = ".$_GET['id']);
while ($meat = $songinfo->fetchArray()) {
  $song_name=$meat['name'];
  $getID3 = new getID3();
  $fI = $getID3->analyze(dirname(__FILE__)."/../../../$song_path");
  getid3_lib::CopyTagsToComments($fI);
  if (isset($fI['comments']['album'][0])){$album_name=$fI['comments']['album'][0];$album_id=$meat['album_id'];}else{$album_name="Unknown Album";$album_id="0";}
  if (isset($fI['comments']['artist'][0])){$artist_name=$fI['comments']['artist'][0];$artist_id=$meat['artist_id'];}else{$artist_name="Unknown Artist";$artist_id="0";}
  if (isset($fI['comments']['picture'][0]['image_mime'])){
    $aart_src='data:'.$fI['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($fI['comments']['picture'][0]['data']);
  }else{
    $aart_src='/src/img/aart/blank.png';
  }
}
?>
