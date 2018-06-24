<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
session_destroy();
session_start();
require_once("util.php");

function analitico($limiar)
{
	$ini = date("H")*3600+date("i")*60+date("s");
	$info = carregar_analitico("UPLOAD/inventario.csv", "UPLOAD/saidas.csv", "UPLOAD/entradas.csv", $limiar);
	$info->tempoExec = (date("H")*3600+date("i")*60+date("s")) - $ini;
	$info->ord = "cmp_tuplas";
	$_SESSION["analitico"] = $info;
	header("Location: ./analitico.php");
	exit();
}

$path = "./UPLOAD";
if(isset($_FILES['saidas']) && isset($_FILES['entradas']))
{
	$arquivos = glob("$path/*");
	foreach($arquivos as $arquivo)
	{
		if(is_file($arquivo))
			unlink($arquivo);
	}
	foreach($_FILES as $arquivo)
	{
		$keyArquivo = array_search($arquivo, $_FILES, false);
		$strNome = "$path/$keyArquivo.csv";
		move_uploaded_file($arquivo['tmp_name'], $strNome);
	}
	analitico($_POST['limiar'] / 1);
}
if(isset($_GET['limiar']))
{
	analitico($_GET['limiar'] / 1);
}
else if(isset($_FILES['analitico_txt']))
{
	move_uploaded_file($_FILES['analitico_txt']['tmp_name'], "$path/analitico.txt");
	$info = unserialize(file_get_contents("UPLOAD/analitico.txt"));
	$_SESSION['analitico'] = $info;
	header("Location: ./analitico.php");
	exit();
}

?>
<!DOCTYPE html>
<html>
<head>
<style>
a:link, a:visited {text-decoration: none;}
a:hover, a:active {color: red;}
</style>
</head>
<body>
<table border="0" height="100%" width="100%" cellspacing="10" cellpadding="10">
<tr><td>
	<form enctype="multipart/form-data" method="POST">
	<table border="0" width="100%">
	<tr>
		<td width="250">CSV Inventario (SEF)</td>
		<td><input name="inventario" type="file" /><font size="2"><br>* Se inexistente, estoque inicial = zero</font></td>
	</tr>
	<tr>
		<td>CSV Saidas (NF-e)</td>
		<td><input name="saidas" type="file" /><font size="2"><br>* obrigatorio</font></td>
	</tr>
	<tr>
		<td>CSV Entradas (NF-e)</td>
		<td><input name="entradas" type="file" /><font size="2"><br>* obrigatorio</font></td>
	</tr>
	<tr>
		<td>Limiar</td>
		<td><input name="limiar" type="text" value="70" size="2" /> %<font size="2"><br>* percentual de similiaridade minimo</font></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><br><input type="submit" value="Enviar" /></td>
	</tr>
	</table>
	</form>
</td></tr>
<tr><td><hr></td></tr>
<tr><td>
	<form enctype="multipart/form-data" method="POST">
	<table border="0" width="100%">
	<tr>
		<td width="250">Analitico (TXT)</td>
		<td><input name="analitico_txt" type="file" /><font size="2"><br>(carrega analitico pronto salvo anteriormente)</font></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><br><input type="submit" value="Carregar" /></td>
	</tr>
	</table>
	</form>
</td></tr>
</table>
</body>
</html>
