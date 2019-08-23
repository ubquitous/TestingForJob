<?php

require_once('SqlConnection.php');
$sort=!empty($_POST["sort"])?$_POST["sort"]:"name";
$page=!empty($_POST["page"])?$_POST["page"]:0;
$sql=new SQLCon();
if(empty($_POST["sort"]) && empty($_POST["page"]))
    echo '{"page":"'.$sql->get_page().'"}';
else{
    echo json_encode($sql->get_data($sort, $page));
}