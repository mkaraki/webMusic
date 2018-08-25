<?php require(dirname(__FILE__).'/../../code/ui/player/open_artist.php'); ?>
<html>
  <head>
    <link rel="stylesheet" href="/css/player/open.css" type="text/css" />
    <script src="/lib/tablesorter/jquery-latest.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/css/player/tablesorter/style.css" type="text/css" media="print, projection, screen" /><script src="/lib/tablesorter/jquery.tablesorter.min.js" type="text/javascript"></script>
    <script src="/js/player/open_artist.js" type="text/javascript"></script>
    <title>Select Artist - webMusic</title>
  </head>
  <body>
    <a href="" onclick="history.back(); return false;">Back</a><br><hr />
    <?php link_artist(); ?>
  </body>
</html>
