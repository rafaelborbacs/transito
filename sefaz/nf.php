<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
require_once("util.php");

$info = $_SESSION['analitico'];

/**********************************************************/

$codigo;$agregado;$fonte;$tupla;$multQtd;$descricao;$descricao_lev;
$soma_qtd = 0;
if($info->tuplas != null && isset($_GET['fonte']) && isset($_GET['descricao']))
{
	$fonte = $_GET['fonte'];
	$descricao = $_GET['descricao'];
	$itens = array();
	foreach($info->tuplas as $cada)
	{
		if($fonte == "saidas")
		{
			$agregado = $cada->saida;
		}
		else if($fonte == "entradas")
		{
			$agregado = $cada->entrada;
		}
		if($agregado != null)
		{
			$achou = false;
			foreach($agregado->itens as $item)
			{
				if($item->descricao == $descricao)
				{
					$codigo = $agregado->itens[0]->codigo;
					$tupla = $cada;
					$itens[] = $item;
					$achou = true;
					$soma_qtd += $item->qtd;
				}
			}
			if($achou)
			{
				break;
			}
		}
	}
	// reagrupar NFs
	if(isset($_GET['codigo']))
	{
		$novoCodigo = $_GET['codigo'];
		reagruparItens($fonte, $info->tuplas, $tupla, $agregado, $itens, $novoCodigo);
		$_SESSION['analitico'] = $info;
		//echo "<script language=\"javascript\"> window.opener.location = './analitico.php'; window.close();</script>";
		echo "<script language=\"javascript\"> window.close();</script>";
		exit();
	}
	// novoMultQtd
	if(isset($_GET['novoMultQtd']))
	{
		$novoMultQtd = $_GET['novoMultQtd'];
		foreach($itens as $item)
		{
			$item->multQtd = formatar_numero($novoMultQtd);
		}
		$tupla->ef = get_estoque_final($tupla);
		$_SESSION['analitico'] = $info;
		//echo "<script language=\"javascript\"> window.opener.location = './analitico.php'; window.close();</script>";
		echo "<script language=\"javascript\"> window.close();</script>";
		exit();
	}
	$descricao_lev = $agregado->itens[0]->descricao_lev;
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
	$multQtd = $itens[0]->multQtd;
	$rand = rand(999999, 9999999999);
	echo "<table border=\"0\" width=\"100%\" cellspacing=\"10\"><tr valign=\"top\"><td width=\"100\" align=\"right\">Fonte<br>Item<br>Lev<br>#NF</td><td><b>";
	echo $fonte;
	echo "<br>";
	echo $descricao;
	echo "</b><br>";
	echo $descricao_lev;
	echo "<b><br>";
	echo sizeof($itens);
	echo "</td></tr><tr><td colspan=\"2\"><hr/></td></tr><tr><td align=\"center\">";
	echo "<a href=\"nf.php?fonte=$fonte&codigo=N$rand&descricao=$descricao\">EXCLUIR</a></td><td>";
	echo "<form action=\"nf.php\"><input type=\"hidden\" name=\"fonte\" value=\"$fonte\"><input type=\"hidden\" name=\"descricao\" value=\"$descricao\">";
	echo "Codigo ($fonte): <input type=\"text\" name=\"codigo\" size=\"10\" value=\"$codigo\">&nbsp;<input type=\"submit\" value=\"ok\"></form></td></tr>";
	echo "<tr><td colspan=\"2\"><hr/></td></tr><tr><td align=\"right\">Quantidade</td><td>";
	echo "<form action=\"nf.php\"><input type=\"hidden\" name=\"fonte\" value=\"$fonte\"><input type=\"hidden\" name=\"descricao\" value=\"$descricao\">";
	echo "Multiplicador: <input type=\"text\" name=\"novoMultQtd\" size=\"4\" value=\"$multQtd\">&nbsp;x&nbsp;<b>$soma_qtd</b>&nbsp;<input type=\"submit\" value=\"ok\"></form>";
	echo "</td></tr><tr><td colspan=\"2\">";
	echo "<hr/>";
	echo "</td></tr><tr valign=\"top\"><td colspan=\"2\">";
	foreach($itens as $item)
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
	unset($itens);
	echo "</td></tr></table>";
}
else
{
	echo "Erro";
}

?>
</body>
</html>
