<?php require(dirname(__FILE__).'/../../code/ui/player/open_album.php'); ?>
<html>
  <head>
    <link rel="stylesheet" href="/css/player/open.css" type="text/css" />
    <script src="/lib/tablesorter/jquery-latest.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/lib/tablesorter/themes/blue/style.css" type="text/css" media="print, projection, screen" /><script src="/lib/tablesorter/jquery.tablesorter.min.js" type="text/javascript"></script>
    <script src="/js/player/open_album.js" type="text/javascript"></script>
    <title>Select Album - webMusic</title>
  </head>
  <body>
    <a href="" onclick="history.back(); return false;">Back</a><BR><hr />
    <?php link_album(); //link_album_nothumb(); ?>
  </body>
</html>
