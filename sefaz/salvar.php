<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
require_once("util.php");

header('Content-type: text/txt');
header('Content-disposition: attachment; filename=analitico.txt');
header("Pragma: no-cache");
header("Expires: 0");

$info = $_SESSION['analitico'];

echo serialize($info);

?>