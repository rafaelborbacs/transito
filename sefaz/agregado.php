<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
require_once("util.php");

$info = $_SESSION['analitico'];

/**********************************************************/

$codigo;$agregado;$fonte;$tupla;
$codigoInventario = "";
$codigoOutraFonte = "";
if($info->tuplas != null && isset($_GET['fonte']) && isset($_GET['codigo']))
{
	$fonte = $_GET['fonte'];
	$codigo = $_GET['codigo'];
	foreach($info->tuplas as $cada)
	{
		if(($fonte == "saidas" && $cada->saida != null && $cada->saida->itens[0]->codigo == $codigo))
		{
			$agregado = $cada->saida;
			$tupla = $cada;
			$codigoOutraFonte = ($tupla->entrada != null ? $tupla->entrada->itens[0]->codigo : "");
			break;
		}
		else if($fonte == "entradas" && $cada->entrada != null && $cada->entrada->itens[0]->codigo == $codigo)
		{
			$agregado = $cada->entrada;
			$tupla = $cada;
			$codigoOutraFonte = ($tupla->saida != null ? $tupla->saida->itens[0]->codigo : "");
			break;
		}
	}
	$codigoInventario = ($tupla->inventario != null ? $tupla->inventario->codigo : "");
	// reagrupar
	if(isset($_GET['novocodigo']))
	{
		$novoCodigo = $_GET['novocodigo'];
		reagruparItens($fonte, $info->tuplas, $tupla, $agregado, $agregado->itens, $novoCodigo);
		$_SESSION['analitico'] = $info;
		//echo "<script language=\"javascript\"> window.opener.location = './analitico.php'; window.close();</script>";
		echo "<script language=\"javascript\"> window.close();</script>";
		exit();
	}
	// associar novo inventario
	if(isset($_GET['codigoInventario']))
	{
		$novoCodigoInventario = $_GET['codigoInventario'];
		reassociarInventario($fonte, $info->tuplas, $tupla, $novoCodigoInventario);
		$_SESSION['analitico'] = $info;
		//echo "<script language=\"javascript\"> window.opener.location = './analitico.php'; window.close();</script>";
		echo "<script language=\"javascript\"> window.close();</script>";
		exit();
	}
	// associar nova outra fonte (saida ou entrada)
	if(isset($_GET['codigoOutraFonte']))
	{
		$novoCodigoOutraFonte = $_GET['codigoOutraFonte'];
		if($fonte == "saidas")
		{
			reassociarEntrada($info->tuplas, $tupla, $novoCodigoOutraFonte);
		}
		else if($fonte == "entradas")
		{
			reassociarSaida($info->tuplas, $tupla, $novoCodigoOutraFonte);
		}
		$_SESSION['analitico'] = $info;
		//echo "<script language=\"javascript\"> window.opener.location = './analitico.php'; window.close();</script>";
		echo "<script language=\"javascript\"> window.close();</script>";
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
<?php
	echo "<table border=\"0\" width=\"100%\" cellspacing=\"20\"><tr valign=\"top\"><td width=\"100\" align=\"right\">Fonte<br>Agregado<br>#NF</td><td><b>";
	echo $fonte;
	echo "<br>";
	echo $agregado->itens[0]->descricao;
	echo "<br>";
	echo sizeof($agregado->itens);
	echo "</td></tr><tr><td colspan=\"2\"><hr/></td></tr><tr><td align=\"center\">";
	$rand = rand(999999, 9999999999);
	echo "<a href=\"agregado.php?fonte=$fonte&codigo=$codigo&novocodigo=N$rand\">EXCLUIR</a></td><td>";
	echo "<form action=\"agregado.php\"><input type=\"hidden\" name=\"fonte\" value=\"$fonte\"><input type=\"hidden\" name=\"codigo\" value=\"$codigo\">";
	echo "Codigo ($fonte): <input type=\"text\" name=\"novocodigo\" size=\"10\" value=\"$codigo\">&nbsp;<input type=\"submit\" value=\"ok\"></form></td></tr>";
	echo "<tr><td colspan=\"2\"><hr/></td></tr><tr><td align=\"right\">INVENTARIO";
	echo "</td><td><form action=\"agregado.php\"><input type=\"hidden\" name=\"fonte\" value=\"$fonte\"><input type=\"hidden\" name=\"codigo\" value=\"$codigo\">";
	echo "Codigo: <input type=\"text\" name=\"codigoInventario\" size=\"10\" value=\"$codigoInventario\">&nbsp;<input type=\"submit\" value=\"ok\"></form></td></tr>";
	echo "<tr><td align=\"right\">";
	echo ($fonte == "saidas" ? "ENTRADA" : "SAIDA");
	echo "</td><td><form action=\"agregado.php\"><input type=\"hidden\" name=\"fonte\" value=\"$fonte\"><input type=\"hidden\" name=\"codigo\" value=\"$codigo\">";
	echo "Codigo: <input type=\"text\" name=\"codigoOutraFonte\" size=\"10\" value=\"$codigoOutraFonte\">&nbsp;<input type=\"submit\" value=\"ok\"></form></td></tr>";
	echo "</td></tr><tr><td colspan=\"2\"><hr/></td></tr><tr valign=\"top\"><td colspan=\"2\">";
	foreach($agregado->itens as $item)
	{
		echo "<table border=\"0\" width=\"100%\" cellspacing=\"10\"><tr valign=\"top\"><td width=\"100\" align=\"right\">";
		echo "Chave NF<br>Data<br>Tipo Operacao<br>Item<br>Unidade<br>Quantidade<br>Valor Unitario<br>Valor Total<br>Unid Tribut<br>Qtd Tribut<br>Natureza<br>Tributacao<br></td><td>";
		echo $item->chave;
		echo "<br>";
		echo $item->data;
		echo "<br>";
		echo $item->to;
		echo "<br><b>";
		echo $item->descricao;
		echo "<br>";
		echo $item->unidade;
		echo "<br>";
		echo $item->qtd;
		echo "<br>";
		echo round($item->valor/$item->qtd,2);
		echo "<br>";
		echo $item->valor;
		echo "</b><br>";
		echo $item->unidadeTributavel;
		echo "<br>";
		echo $item->qtdTributavel;
		echo "<br>";
		echo $item->natureza;
		echo "<br>";
		echo $item->tributacaoICMS;
		echo "</td></tr></table>";
	}
	echo "</td></tr></table>";
}
else
{
	echo "Erro";
}

?>
</body>
</html>
