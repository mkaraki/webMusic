<?php require(dirname(__FILE__).'/../../code/ui/player/open_song.php');
if(isset($_GET['album'])){$arg="?album=".$_GET['album'];}elseif(isset($_GET['artist'])){$arg="?artist=".$_GET['artist'];}else{$arg="";}
?>
<!DOCTYPE html>
<html>
  <head>
    <script src="/lib/tablesorter/jquery-latest.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/css/player/open.css" type="text/css" />
    <link rel="stylesheet" href="/css/player/tablesorter/style.css" type="text/css" media="print, projection, screen" /><script src="/lib/tablesorter/jquery.tablesorter.min.js" type="text/javascript"></script>
    <script src="/js/player/open_songs.js" type="text/javascript"></script>
    <title>Songs - webMusic</title>
  </head>
  <body>
  <a href="" onclick="history.back(); return false;">Back</a>&nbsp;&nbsp;<a href="/ui/player/shuffle.php<?php echo "$arg"; ?>">Shuffle this list</a><br><hr />
<?php if (isset($_GET['album'])){table_song_list("album",$_GET['album']);}elseif(isset($_GET['artist'])){table_song_list("artist",$_GET['artist']);}else{table_song_list("","");} ?>
  </body>
</html>
