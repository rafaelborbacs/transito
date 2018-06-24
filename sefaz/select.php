<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
//require_once("util.php");

$info = $_SESSION['analitico'];

if($info->tuplas != null)
{
	// atualizar select?
	if(isset($_GET["tuplaId"]) && isset($_GET["select"]))
	{
		$tuplaId = $_GET["tuplaId"] / 1;
		if($tuplaId == -1)
		{
			// todos
			foreach($info->tuplas as $tupla)
			{
				$tupla->select = $_GET["select"] / 1;
			}
		}
		else
		{
			$tupla = $info->tuplas[$_GET["tuplaId"] / 1];
			if($tupla != null)
			{
				$tupla->select = $_GET["select"] / 1;
			}
		}
		$_SESSION['analitico'] = $info;
		echo $tuplaId;
	}
}
else
	die("Erro: sessao vazia")

?>