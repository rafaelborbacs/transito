<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
require_once("util.php");


$info = $_SESSION['analitico'];

//qt: 915, sm: 4806168.22

 $qt = 0;
$sm = 0;
foreach($info->tuplas as $tupla)
{
	if($tupla->saida != null)
	{
		foreach($tupla->saida->itens as $it)
		{
			$qt++;
			$sm += $it->valor;
		}
	}
}
echo "qt: $qt, sm: $sm<br>";

?>
