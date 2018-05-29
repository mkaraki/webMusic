<!DOCTYPE html>
<html>
  <head>
    <title>webMusic</title>
    <link rel="stylesheet" href="/css/player/open.css" type="text/css" />
  </head>
  <body>
    <form action="/ui/player/search.php" method="get">
      <a>Search : </a>
      <input type="text" name="s_str" />
      <a>Target : </a>
      <select name="s_target">
        <option value="all">All</option>
        <option value="album">Album</option>
        <option value="artist">Artist</option>
        <option value="song">Song</option>
      </select>
      <button type="text">Search</button>
    </form>
    <a href="/ui/player/open_album.php">Album Select</a><BR>
    <a href="/ui/player/open_artist.php">Artist Select</a><BR>
    <a href="/ui/player/open_song.php">Song Select (take a while)</a><BR>
    <a href="/ui/player/shuffle.php">Play Shuffle</a><BR>
  </body>
</html>
