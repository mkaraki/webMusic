<?php
require(dirname(__FILE__).'/../../code/ui/lyric.php');
if (!isset($_GET['id'])){echo "<script>window.open('about:blank','_self').close();</script>";exit;}
set_id($_GET['id']);
read_url($id);
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Lyric DB</title>
    <style>
    html,body{margin:0;padding:0;width:100%;height:100%;}
    </style>
    <title></title>
  </head>
  <body>
    <?php export_link(); ?>
  </body>
</html>
