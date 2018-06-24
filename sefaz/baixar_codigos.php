<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
require_once("util.php");

header('Content-type: text/csv');
header('Content-disposition: attachment; filename=codigos.csv');
header("Pragma: no-cache");
header("Expires: 0");

$info = $_SESSION['analitico'];
$linhas = sizeof($info->tuplas);

function get_itens_tabela($itens, $fonte)
{
	$obj = new stdclass();
	$obj->itens = array();
	$obj->soma_qtd = 0;
	$obj->str_qtd = "";
	$obj->str_nfs = "";
	$obj->soma_valor = 0;
	foreach($itens as $item)
	{
		$achou = false;
		foreach($obj->itens as $o)
		{
			if($o->descricao == $item->descricao && $o->unidade == $item->unidade)
			{
				$achou = true;
				$o->soma_qtd_item += $item->qtd;
				break;
			}
		}
		if(!$achou)
		{
			$item->soma_qtd_item = $item->qtd;
			$obj->itens[] = $item;
			$obj->str_nfs .= "<br><a href=\"javascript:nf('$fonte','".$item->descricao."')\">".$item->descricao."</a> (".$item->unidade.")";
		}
		$obj->soma_qtd += $item->qtd * $item->multQtd;
		$obj->soma_valor += $item->valor;
		$obj->vu = ($obj->soma_qtd == 0 ? 0 : round($obj->soma_valor/$obj->soma_qtd, 2));
	}
	$obj->str_qtd = "<b>" . round($obj->soma_qtd, 2) . "</b><br><font size=2>";
	foreach($obj->itens as $item)
	{
		$obj->str_qtd .= "<br>+" . round($item->soma_qtd_item, 2);
		unset($item->soma_qtd_item);
	}
	$obj->str_qtd .= "</font>";
	return $obj;
}

/**********************************************************/

echo "SELECIONADO;CODIGO;DESCRICAO_INVENTARIO;DESCRICAO_SAIDAS;DESCRICAO_ENTRADAS";

foreach($info->tuplas as $tupla)
{
	$codigo = round(rand(9999999, 99999999999));
	
	if($tupla->inventario != null)
	{
		echo "\r\n";
		echo $tupla->select;
		echo ";";
		echo $codigo;
		echo ";";
		//inventario
		echo $tupla->inventario->descricao;
		echo ";;";
	}
	
	//saidas
	if($tupla->saida != null)
	{
		$obj = get_itens_tabela($tupla->saida->itens, "saidas");
		foreach($obj->itens as $item)
		{
			echo "\r\n";
			echo $tupla->select;
			echo ";";
			echo $codigo;
			echo ";;";
			echo $item->descricao;
			echo ";";
		}
	}
	
	//entradas
	if($tupla->entrada != null)
	{
		$obj = get_itens_tabela($tupla->entrada->itens, "entradas");
		foreach($obj->itens as $item)
		{
			echo "\r\n";
			echo $tupla->select;
			echo ";";
			echo $codigo;
			echo ";;;";
			echo $item->descricao;
		}
	}
}

?>
