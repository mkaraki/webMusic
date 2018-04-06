<?php
echo "Remove Library Database";
unlink(dirname(__FILE__)."../db/library.db");

require (dirname(__FILE__)."/connect_sql.php");
?>
