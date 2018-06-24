<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
require_once("util.php");

header('Content-type: text/csv');
header('Content-disposition: attachment; filename=analitico.csv');
header("Pragma: no-cache");
header("Expires: 0");

$info = $_SESSION['analitico'];
$linhas = sizeof($info->tuplas);

/**********************************************************/

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
	unset($obj->itens);
	return $obj;
}

echo "SELECIONADO;CODIGO_INVENTARIO;DESCRICAO_INVENTARIO;UNIDADE_INVENTARIO;QUANTIDADE_INVENTARIO;VALOR_UNITARIO_INVENTARIO;VALOR_INVENTARIO;";
echo "CODIGO_SAIDAS;DESCRICAO_SAIDAS;QUANTIDADE_SAIDAS;TRIBUTACAO_SAIDAS;NUMERO_NOTAS_SAIDAS;VALOR_UNITARIO_MEDIO_SAIDAS;VALOR_TOTAL_SAIDAS;";
echo "CODIGO_ENTRADAS;DESCRICAO_ENTRADAS;QUANTIDADE_ENTRADAS;TRIBUTACAO_ENTRADAS;NUMERO_NOTAS_ENTRADAS;VALOR_UNITARIO_MEDIO_ENTRADAS;VALOR_TOTAL_ENTRADAS;";
echo "ESTOQUE_FINAL_QUANTIDADE;ESTOQUE_FINAL_VALOR_UNITARIO;ESTOQUE_FINAL_VALOR_TOTAL";

foreach($info->tuplas as $tupla)
{
	echo "\r\n";
	echo $tupla->select;
	echo ";";
	//inventario
	if($tupla->inventario == null)
	{
		echo ";;;;;;";
	}
	else
	{
		echo $tupla->inventario->codigo;
		echo ";";
		echo $tupla->inventario->descricao;
		echo ";";
		echo $tupla->inventario->unidade;
		echo ";";
		echo number_format($tupla->inventario->qtd, 2, ',', '');
		echo ";";
		echo number_format($tupla->inventario->vu, 2, ',', '');
		echo ";";
		echo number_format($tupla->inventario->valor, 2, ',', '');
		echo ";";
	}
	//saida
	if($tupla->saida == null)
	{
		echo ";;;;;;;";
	}
	else
	{
		$aux = get_itens_tabela($tupla->saida->itens, "saidas");
		echo $tupla->saida->itens[0]->codigo;
		echo ";";
		echo $tupla->saida->itens[0]->descricao;
		echo ";";
		echo number_format($aux->soma_qtd, 2, ',', '');
		echo ";";
		echo $tupla->saida->itens[0]->tributacaoICMS;
		echo ";";
		echo sizeof($tupla->saida->itens);
		echo ";";
		echo number_format($aux->vu, 2, ',', '');
		echo ";";
		echo number_format($aux->soma_valor, 2, ',', '');
		echo ";";
	}
	//entrada
	if($tupla->entrada == null)
	{
		echo ";;;;;;;";
	}
	else
	{
		$aux = get_itens_tabela($tupla->entrada->itens, "entradas");
		echo $tupla->entrada->itens[0]->codigo;
		echo ";";
		echo $tupla->entrada->itens[0]->descricao;
		echo ";";
		echo number_format($aux->soma_qtd, 2, ',', '');
		echo ";";
		echo $tupla->entrada->itens[0]->tributacaoICMS;
		echo ";";
		echo sizeof($tupla->entrada->itens);
		echo ";";
		echo number_format($aux->vu, 2, ',', '');
		echo ";";
		echo number_format($aux->soma_valor, 2, ',', '');
		echo ";";
	}
	// estoque final teórico
	echo number_format($tupla->ef->qtd, 2, ',', '');
	echo ";";
	echo number_format($tupla->ef->vu, 2, ',', '');
	echo ";";
	echo number_format($tupla->ef->valor, 2, ',', '');
}

?>
