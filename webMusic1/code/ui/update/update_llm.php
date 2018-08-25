<?php
require_once(dirname(__FILE__).'/../lib/getid3/getid3.php');
require(dirname(__FILE__).'/../conf/client/client_setting.php');
require(dirname(__FILE__).'/../conf/server/server_setting.php');
require (dirname(__FILE__)."/connect_sql.php");
$musicfile_filt=$conf_musicfile_filter;
$llddir=$conf_local_library_directory;

$session_file = dirname(__FILE__)."/../../tmp/00000.prs";

function startlogging(){
  global $session_file;
  file_put_contents($session_file,"webMusic LLM Database updater v0.1\nCopyright mkapps 2018\n\nStart update : ".time()."\n\n");
}

function logging($meat){
  global $session_file;
  file_put_contents($session_file,$meat."\n",FILE_APPEND);
}

function getdirlist($targetdir){
  return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetdir,FilesystemIterator::SKIP_DOTS|FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO), RecursiveIteratorIterator::LEAVES_ONLY);
}

function get_id3_info($filename,$what_to_get,$forUI){
  $getID3 = new getID3();
	$getID3->setOption(array('encoding' => 'UTF-8'));
	$fileInfo = $getID3->analyze($filename);
	getid3_lib::CopyTagsToComments($fileInfo);
  $res = $fileInfo['comments']["$what_to_get"][0];
  if ($forUI){if ($res=""){return "Unknown";}}else{return "$res";}
}

function puts_llm_album($dirlist,$sql_link){
  global $musicfile_filt;
  foreach($dirlist as $pathname){
	  if(in_array(substr($pathname, strrpos($pathname, '.') + 1),$musicfile_filt)){
      $albmnm=get_id3_info($pathname,"album",false);
      if ($albmnm == ""){continue;}

      //check cant use char
      $org_albmnm=$albmnm;
      $albmnm=str_replace('"','\\"',$albmnm);

      $dupchk=$sql_link->querySingle('select count(name) from album where name = "'.$albmnm.'" limit 1');
      if ($dupchk){continue;}
      $res=$sql_link->query('INSERT INTO album (name) VALUES ("'.$albmnm.'")');
      if (!$res){
        logging("[FILED] $org_albmnm - ".$sql_link->lastErrorMsg());
      }else{
        logging("[ADD] $org_albmnm");
      }
    }
    // }else{
    //  echo "[WR] {$pathname} is not Music file.\n";
    // }
  }
}

function puts_llm_artist($dirlist,$sql_link){
  global $musicfile_filt;
  foreach($dirlist as $pathname){
	  if(in_array(substr($pathname, strrpos($pathname, '.') + 1),$musicfile_filt)){
      $artnm=get_id3_info($pathname,"artist",false);
      if ($artnm == ""){continue;}
      $songnm=str_replace('"','\\"',$songnm);

      //check cant use char
      $org_artnm=$artnm;
      $artnm=str_replace('"','\\"',$artnm);

      $dupchk=$sql_link->querySingle('select count(name) from artist where name = "'.$artnm.'" limit 1');
      if ($dupchk){continue;}
      $res=$sql_link->query('INSERT INTO artist (name) VALUES ("'.$artnm.'")');
      if (!$res){
        logging("[FILED] $org_artnm - ".$sql_link->lastErrorMsg());
      }else{
        logging("[ADD] $org_artnm");
      }
    }
    // }else{
    //  echo "[WR] {$pathname} is not Music file.\n";
    // }
  }
}

function puts_llm_songs($dirlist,$sql_link){
  global $musicfile_filt;
  foreach($dirlist as $pathname){
	  if(in_array(substr($pathname, strrpos($pathname, '.') + 1),$musicfile_filt)){
      $albmnm=get_id3_info($pathname,"album",false);
      if (!get_id3_info($pathname,"track_number",false)){$trknum = 0;}else{$trknum=get_id3_info($pathname,"track_number",false);}
      $artnm=get_id3_info($pathname,"artist",false);
      $songnm=get_id3_info($pathname,"title",false);
      if (!isset($songnm)){$songnm = $pathname;}

      //ALIGN to
      if (isset($albmnm)){
        $albmnm=str_replace('"','\\"',$albmnm);
        $albmnm_id=$sql_link->querySingle('select id from album where name = "'.$albmnm.'"');
      }else{$albmnm_id=0;}
      if (isset($artnm)){
        $artnm=str_replace('"','\\"',$artnm);
        $artnm_id=$sql_link->querySingle('select id from artist where name = "'.$artnm.'"');
      }else{$artnm_id=0;}
      if (!isset($trknum)){$trknum = 0;}

      //check cant use char
      $org_songnm=$songnm;
      $org_pathname=$pathname;
      $songnm=str_replace('"','\\"',$songnm);
      $pathname=str_replace('"','\\"',$pathname);

      //Duplicate Check
      $dupchk=$sql_link->querySingle('select count(path) from song where path = "'.$pathname.'" limit 1');
      if ($dupchk){continue;}

      //ADD
      $res=$sql_link->query('INSERT INTO song (name,path,album_id,tnum,artist_id) VALUES ("'.$songnm.'","'.$pathname.'",'.$albmnm_id.','.$trknum.','.$artnm_id.')');
      if (!$res){
        logging("[FILED] $org_songnm - ".$sql_link->lastErrorMsg());
      }else{
        if (file_exists($org_pathname.".html")){
          $res=$sql_link->query("INSERT INTO lyric (song_id,provider,address,type) VALUES (".$sql_link->lastInsertRowID().",0,'".$pathname.'.html'."','html')");
        }elseif(file_exists($org_pathname.".lrc")){
          $res=$sql_link->query("INSERT INTO lyric (song_id,provider,address,type) VALUES (".$sql_link->lastInsertRowID().",0,'".$pathname.'.lrc'."','lrc')");
        }elseif(file_exists($org_pathname.".txt")){
          $res=$sql_link->query("INSERT INTO lyric (song_id,provider,address,type) VALUES (".$sql_link->lastInsertRowID().",0,'".$pathname.'.txt'."','txt')");
        }else{
          $res=true;
        }
        if (!$res){
          logging("[LRC FILED] $org_songnm - ".$sql_link->lastErrorMsg());
        }else{
          logging("[ADD] $org_songnm");
        }
      }
    }
    // }else{
    //  echo "[WR] {$pathname} is not Music file.\n";
    // }
  }
}

//function do_update(){
//  global $llddir;
//  global $sql_host;
  startlogging();
  $dirlist=getdirlist($llddir);
  logging("=== Start update (album)");
  puts_llm_album($dirlist,$sql_host);
  logging("=== Start update (artist)");
  puts_llm_artist($dirlist,$sql_host);
  logging("=== Start update (song)");
  puts_llm_songs($dirlist,$sql_host);
//}
?>
