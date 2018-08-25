<?php require(dirname(__FILE__).'/../../code/ui/player/shuffle.php');
if (isset($_GET['album'])){set_getvalue("album",$_GET['album']);}elseif(isset($_GET['artist'])){set_getvalue("artist",$_GET['artist']);}else{set_getvalue("","");}
main_p();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>(Shuffle)<?php echo $song_name; ?> - webMusic</title>
    <link rel="stylesheet" href="/css/player/player.css" type="text/css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
    <script src="/js/player/shuffle.js" type="text/javascript"></script>
    <script tyle="text/javascript" src="/lib/reflection/reflection.js"></script>
  </head>
  <body ondragover="return false">
    <audio id=player src="/ui/player/request_music.php?id=<?php echo $song_id; ?>" preload=auto autoplay>Opps! This browser may be did not support for html5 audio tags</audio>
    <script>player.volume=Number(docCookies.getItem("volume"));plock=false;init_loop();</script>
    <img id="aart" class="simmer" src="<?php echo $aart_src; ?>">
    <div id="media_info">
      <h2 style="margin-bottom:3px;"><?php echo $song_name; ?></h2>
      <h4 style="margin-bottom:3px;">Shuffle Play
      <?php 
        if (isset($_GET['album'])){
          echo ' in <a href="/ui/player/open_song.php?album='.$album_id.'">'.$album_name.'</a>';
        }elseif(isset($_GET['artist'])){
          echo ' in <a href="/ui/player/open_song.php?artist='.$artist_id.'">'.$artist_name.'</a>';
        }
      ?></h4>
    </div>
    <div id="control">
      <form>
        <button type="button" onclick="history.back(); return false;"><img width="40px" height="40px" src="/src/img/play_control/Eject.svg" /></button>
        <button type="button" onclick="player.currentTime -= 5"><img width="50px" height="50px" src="/src/img/play_control/RW.svg" /></button>
        <button type="button" onclick="changePlay()"><img id="play_ui" width="60px" height="60px" src="/src/img/play_control/play.svg" /></button>
        <button type="button" onclick="player.currentTime += 5"><img width="50px" height="50px" src="/src/img/play_control/FF.svg" /></button>
        <button type="button" onclick="location.reload();"><img width="40px" height="40px" src="/src/img/play_control/s_next.svg" /></button>
        <button type="button" onclick="if(player.volume>=0.1){player.volume -= 0.1;change_volume();}"><img width="40px" height="40px" src="/src/img/play_control/vol_min.svg" /></button>
        <button type="button" onclick="if(player.volume<1){player.volume += 0.1;change_volume();}"><img width="40px" height="40px" src="/src/img/play_control/vol_plus.svg" /></button>
      </form>
    </div>
    <div id="seekbarbf"><span></span></div>
    <div id="seekbar"><span></span></div>
  </body>
</html>
