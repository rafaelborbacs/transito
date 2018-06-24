<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
require_once("util.php");

$info = $_SESSION['analitico'];

/**********************************************************/

$codigo;$tupla;
if($info->tuplas != null && isset($_GET['codigo']))
{
	foreach($info->tuplas as $cada)
	{
		$codigo = $_GET['codigo'];
		if($cada->inventario != null && $cada->inventario->codigo == $codigo)
		{
			$tupla = $cada;
			break;
		}
	}
	// atualizar dados inventario
	if(isset($_GET['qtd']) && isset($_GET['vu']))
	{
		$tupla->inventario->qtd = formatar_numero($_GET['qtd']);
		$tupla->inventario->vu = formatar_numero($_GET['vu']);
		$tupla->inventario->valor = $tupla->inventario->vu * $tupla->inventario->qtd;
		$tupla->ef = get_estoque_final($tupla);
		$_SESSION['analitico'] = $info;
		echo "<script language=\"javascript\"> window.opener.location = './analitico.php'; window.close();</script>";
		exit();
	}
	$descricao = $tupla->inventario->descricao;
	$unidade = $tupla->inventario->unidade;
	$qtd = $tupla->inventario->qtd;
	$vu = $tupla->inventario->vu;
	$valor = $tupla->inventario->valor;
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
<?php
	echo "<form action=\"inventario.php\">";
	echo "<table border=\"0\" width=\"100%\" cellspacing=\"20\"><tr valign=\"top\"><td width=\"100\" align=\"right\">Inventario</td><td><b>$descricao</td></tr>";
	echo "<tr><td align=\"right\">Codigo</td><td><b>$codigo</b></td></tr>";
	echo "<tr><td colspan=\"2\"><hr/></td></tr>";
	echo "<tr><td align=\"right\">Unidade</td><td>$unidade</td></tr>";
	echo "<tr><td align=\"right\">Quantidade</td>";
	echo "<td><input type=\"hidden\" name=\"codigo\" value=\"$codigo\"><input type=\"text\" name=\"qtd\" size=\"10\" value=\"$qtd\"></td></tr>";
	echo "<tr><td align=\"right\">Valor Unitario</td>";
	echo "<td><input type=\"text\" name=\"vu\" size=\"10\" value=\"$vu\"></td></tr>";
	echo "<tr><td align=\"right\">Valor Total</td><td><b>$valor</b></td></tr>";
	echo "<tr><td align=\"right\">&nbsp;</td><td><input type=\"submit\" value=\"ok\"></form></td></tr>";
	echo "</table></form>";
}
else
{
	echo "Erro";
}

?>
</body>
</html>
