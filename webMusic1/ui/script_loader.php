<?php
if (!isset($_GET['content'])) die ("NO DATA");
if (file_exists(dirname(__FILE__).'/../js/'.$_GET['content'])){
readfile (file_exists(dirname(__FILE__).'/../js/'.$_GET['content']));
}
?>