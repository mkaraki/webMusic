<?php
//require(dirname(__FILE__).'/update_llm.php');

$update_core_path = dirname(__FILE__).'/update_llm.php';

$log = "";

if (file_exists(dirname(__FILE__)."/../../tmp/$session_id.prs")){
    $log = file_get_contents(dirname(__FILE__)."/../../tmp/$session_id.prs");
}else{
    $session_file = dirname(__FILE__)."/../../tmp/$session_id.prs";
    exec("C:\\php\\php.exe $update_core_path");
    //do_update();
}
?>