<?php require(dirname(__FILE__).'/../../code/ui/player/search.php');
if (!isset($_GET['s_str'])){echo "<script>location.href='/ui/player/index.php';</script>";exit;}else{
  set_sstr($_GET['s_str']);
  if (isset($_GET['s_target'])){set_star($_GET['s_target']);}else{set_star("all");}
}
?>
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="/css/player/open.css" type="text/css" />
    <link rel="stylesheet" href="/css/player/search.css" type="text/css" />
    <script src="/lib/tablesorter/jquery-latest.js" type="text/javascript"></script>
    <script src="/js/player/search.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/css/player/tablesorter/style.css" type="text/css" media="print, projection, screen" /><script src="/lib/tablesorter/jquery.tablesorter.min.js" type="text/javascript"></script>
    <title><?php echo $_GET['s_str']; ?> - Search - webMusic</title>
  </head>
  <body>
    <a href="" onclick="history.back(); return false;">Back</a><BR><hr />
    <?php res(); ?>
  </body>
</html>
