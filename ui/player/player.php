<?php require(dirname(__FILE__).'/../../code/ui/player/player.php'); ?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $song_name; ?> - webMusic</title>
    <link rel="stylesheet" href="/css/player/player.css" type="text/css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script tyle="text/javascript" src="/lib/reflection/reflection.js"></script>
  </head>
  <body ondragover="return false">
    <audio id=player src="<?php echo "/".$song_path; ?>" preload=auto autoplay>Opps! This browser may be did not support for html5 audio tags</audio>
    <img id="aart" class="shimmer" src="<?php echo $aart_src; ?>">
    <div id="easy_loc" style="position:fixed;top:0;left:0;height:calc(100% - 180px);width:100%;margin:0;padding:0;"></div>
    <div>
    <div id="media_info">
      <h2 style="margin-bottom:3px;"><?php echo $song_name; ?></h2>
      <h4 style="margin:8px;"><a href="/ui/player/open_song.php?artist=<?php echo $artist_id; ?>"><?php echo $artist_name;?></a> - <a href="/ui/player/open_song.php?album=<?php echo $album_id; ?>"><?php echo $album_name; ?></a></h4>
    </div>
    <div id="control">
      <form>
        <button type="button" onclick="history.back(); return false;"><img width="40px" height="40px" src="/src/img/play_control/Eject.svg" /></button>
        <button type="button" onclick="player.currentTime -= 5"><img width="50px" height="50px" src="/src/img/play_control/RW.svg" /></button>
        <button type="button" onclick="changePlay()"><img id="play_ui" width="60px" height="60px" src="/src/img/play_control/play.svg" /></button>
        <button type="button" onclick="player.currentTime += 5"><img width="50px" height="50px" src="/src/img/play_control/FF.svg" /></button>
        <button type="button" onclick="changeLoop()"><img id="is_loop_ui" width="40px" height="40px" src="/src/img/play_control/repeat_false.svg" /></button>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <button type="button" onclick="if(player.volume>=0.1){player.volume -= 0.1;change_volume();}"><img width="40px" height="40px" src="/src/img/play_control/vol_min.svg" /></button>
        <button type="button" onclick="if(player.volume<1){player.volume += 0.1;change_volume();}"><img width="40px" height="40px" src="/src/img/play_control/vol_plus.svg" /></button>
        &nbsp;&nbsp;
        <button type="button" onclick="window.open('/ui/player/lyric.php?id=<?php echo $_GET['id']; ?>', '_blank', 'width=800,height=600');"><img width="40px" height="40px" src="/src/img/play_control/lyric.svg" /></button>
      </form>
    </div>
    <div id="seekbarbf"><span></span></div>
    <div id="seekbar"><span></span></div>
    </div>
    <script src="/js/player/player.js" type="text/javascript"></script>
  </body>
</html>
