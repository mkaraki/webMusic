<?php
require (dirname(__FILE__)."/../connect_sql.php");

$id="";

$url="";
$p_name="";
$c_type="";

function set_id($a1){
  global $id;
  $id=$a1;
}

function read_url($get_id){
  global $sql_host;

  global $url;
  global $p_name;
  global $c_type;

  $lyric_db=$sql_host->querySingle("SELECT * FROM lyric WHERE song_id = $get_id LIMIT 1",true);
  $lyric_f=$sql_host->querySingle("SELECT count(song_id) FROM lyric WHERE song_id = $get_id LIMIT 1");
  if (!$lyric_f){$url="null://null";$p_name="null";return;}

  $provider=$lyric_db['provider'];
  $l_url=$lyric_db['address'];
  $l_type=$lyric_db['type'];
  $c_type=$l_type;

  if ($provider==0)
  {
    if ($l_type == "lrc"){
      $url=$l_url;
    }elseif ($l_type == "html"){
      $url=$l_url;
    }elseif ($l_type == "txt"){
      $url=$l_url;
    }else{
      $url="file://".$meat['address'];
    }
  }else{
    $lyric_pdb=$sql_host->querySingle("SELECT * FROM lyric_db WHERE id = $provider LIMIT 1",true);
    $p_name=$lyric_pdb['name'];
    $p_addr=$lyric_pdb['address_url'];

    $url=$p_addr.$l_url;
  }
}

function export_link(){
  global $url;
  global $p_name;
  global $c_type;

  if ($url=="null://null"&&$p_name=="null"){
    echo '<p>No Lyric</p>';
  }elseif($c_type=="https"||$c_type=="http"){
    echo '<a href="'.$url.'">Check lyric with '.$p_name.' (External site | We couldnot care)</a>';
  }elseif($c_type=="html"){
    echo '<iframe src="'."/".$url.'" style="border:0;width:100%;height:100%;margin:0;padding:0;"></iframe>';
  }elseif($c_type=="lrc"){
    echo "<pre>";readfile(dirname(__FILE__)."/../../".$url);echo "</pre>";
  }elseif($c_type=="txt"){
    echo "<pre>";readfile(dirname(__FILE__)."/../../".$url);echo "</pre>";
  }
}
?>
