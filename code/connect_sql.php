<?php
if (file_exists(dirname(__FILE__)."/../db/library.db")){
  $sql_host = new SQLite3(dirname(__FILE__)."/../db/library.db");
}else{
  $sql_host = new SQLite3(dirname(__FILE__)."/../db/library.db");
  $sql_host->query("create table song ('id' INTEGER PRIMARY KEY AUTOINCREMENT,'name' TEXT NOT NULL,'path' TEXT NOT NULL,'album_id' INTEGER,'tnum' INTEGER,'artist_id' INTEGER)");
  $sql_host->query("create table album ('id' INTEGER PRIMARY KEY AUTOINCREMENT,'name' TEXT NOT NULL)");
  $sql_host->query("create table artist ('id' INTEGER PRIMARY KEY AUTOINCREMENT,'name' TEXT NOT NULL)");
  $sql_host->query("create table lyric ('id' INTEGER PRIMARY KEY AUTOINCREMENT,'song_id' INTEGER NOT NULL,'provider' INTEGER NOT NULL,'address' TEXT NOT NULL,'type' TEXT NOT NULL)");
}
?>
