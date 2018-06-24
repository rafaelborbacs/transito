<?php

/*** author: Rafael Borba Costa dos Santos (rafaelborba.cin@gmail.com) ***/

error_reporting( E_ALL );
session_start();
require_once("util.php");

if(!isset($_SESSION["analitico"]) || $_SESSION["analitico"] == null)
	header("Location: ./carregar.php");
$info = $_SESSION["analitico"];
// ordenar?
if(isset($_GET['ord']) && $_GET['ord'] != $info->ord)
{
	$info->ord = $_GET['ord'];
	usort($info->tuplas, $info->ord);
}
$valorEstoque = 0;
$omissaoEntrada = 0;
$todosSelected = true;
$nI = 0;
$nS = 0;
$nE = 0;
$notasS = 0;
$notasE = 0;
foreach($info->tuplas as $tupla)
{
	if($tupla->inventario != null) $nI++;
	if($tupla->saida != null)
	{
		$nS++;
		$notasS += sizeof($tupla->saida->itens);
	}
	if($tupla->entrada != null)
	{
		$nE++;
		$notasE += sizeof($tupla->entrada->itens);
	}
	if($tupla->ef->valor < 0)
		$omissaoEntrada += -$tupla->ef->valor;
	else
		$valorEstoque += $tupla->ef->valor;
	$todosSelected = $todosSelected && $tupla->select;
}
$linhas = sizeof($info->tuplas);

//debug($info);exit();

/* $a = "CREME LEITE ITALAC TP 200G";
$b = "LEITE PO ITALAC 200G C 50";
$c = "LEITE PO NINHO INST 400G C 24";
$d = "LEITE PO NINHO INT SH 800G C 12";
echo "<p>".get_texto_lev($a).", ".get_texto_lev($b).": ".cmp_texto(get_texto_lev($a),get_texto_lev($b))."</p>";
echo "<p>".get_texto_lev($c).", ".get_texto_lev($d).": ".cmp_texto(get_texto_lev($c),get_texto_lev($d))."</p>";
echo "<p>".get_texto_lev($a).", ".get_texto_lev($c).": ".cmp_texto(get_texto_lev($a),get_texto_lev($c))."</p>";
echo "<p>".get_texto_lev($b).", ".get_texto_lev($d).": ".cmp_texto(get_texto_lev($b),get_texto_lev($d))."</p>"; */

/**********************************************************/

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
<script type="text/javascript">
function nf(fonte,descricao){
	window.open("nf.php?fonte="+fonte+"&descricao="+descricao, "nf", "toolbar=no,status=no,scrollbars=no,resizable=no,top=50,left=100,width=670,height=600");
}
function agregado(fonte,codigo){
	window.open("agregado.php?fonte="+fonte+"&codigo="+codigo, "nf", "toolbar=no,status=no,scrollbars=no,resizable=no,top=50,left=100,width=670,height=600");
}
function inventario(codigo){
	window.open("inventario.php?codigo="+codigo, "nf", "toolbar=no,status=no,scrollbars=no,resizable=no,top=50,left=100,width=600,height=400");
}
function ord(combo){
	var ord = combo.options[combo.selectedIndex].value;
	window.location = "./analitico.php?ord="+ord;
}
function clickCBSelect(cb){
	var select = (cb.checked ? "1" : "0");
	chamaSelect(cb.id, select);
}
function clickCBSelectTodos(cb){
	var select = (cb.checked ? "1" : "0");
	chamaSelect(-1, select);
}
function chamaSelect(id, select){
	var xhr = new XMLHttpRequest();
	xhr.open('GET', "./select.php?tuplaId="+id+"&select="+select);
	xhr.onload = function() {
		if (xhr.status === 200) {
			if(xhr.responseText == "-1") {
				window.location = "./analitico.php";
			}
		} else {
			alert('Erro: ' + xhr.status);
		}
	};
	xhr.send();
}
</script>
<table border="0" width="100%">
	<tr valign="top"><td colspan="5">
		IE: <b><?php echo $info->ie ?></b> |
		CNPJ: <b><?php echo $info->cnpj ?></b> |
		Data INI: <b><?php echo $info->dataIni ?></b> |
		Data FIM: <b><?php echo $info->dataFim ?></b> |
		exec: <b><?php echo $info->tempoExec ?></b>s
	</td></tr>
	<tr valign="top"><td width="800">
		#linhas: <b><?php echo $linhas ?></b></b> | 
		#i: <b><?php echo $nI ?></b> | 
		#s: <b><?php echo $nS ?></b> | 
		#e: <b><?php echo $nE ?></b> | 
		#itens-s: <b><?php echo $notasS ?></b> | 
		#itens-e: <b><?php echo $notasE ?></b> | 
		estoque: <b><?php echo number_format($valorEstoque, 2, ',', '.') ?></b> | 
		O.E.: <b><?php echo number_format($omissaoEntrada, 2, ',', '.') ?></b>
	</td><td width="300">
		<form method="GET" action="carregar.php">
			Limiar (0 ~ 100)
			<input name="limiar" type="text" size="3" value="<?php echo $info->limiar; ?>" />
			<input type="submit" value="Ok" />
		</form>
	</td><td>
		Ordem
		<select name="cmp" id="cmp" onchange="ord(this)">
			<option value="cmp_tuplas" <?php echo ($info->ord=="cmp_tuplas" ? "selected" : ""); ?>>Match</option>
			<option value="cmp_descricao" <?php echo ($info->ord=="cmp_descricao" ? "selected" : ""); ?>>Descricao</option>
			<option value="cmp_et_pos" <?php echo ($info->ord=="cmp_et_pos" ? "selected" : ""); ?>>EF +</option>
			<option value="cmp_et_neg" <?php echo ($info->ord=="cmp_et_neg" ? "selected" : ""); ?>>EF -</option>
			<option value="cmp_select" <?php echo ($info->ord=="cmp_select" ? "selected" : ""); ?>>Selecionados</option>
		</select>
	</td><td align="right" width="80">
		<form method="GET" action="salvar.php">
			<input type="submit" value="SALVAR" />
		</form>
	</td><td align="right" width="80">
		<form method="GET" action="baixar.php">
			<input type="submit" value=".CSV" />
		</form>
	</td></td><td align="right" width="80">
		<form method="GET" action="baixar_codigos.php">
			<input type="submit" value="CODIGOS" />
		</form>
	</td></tr>
</table>
<br>
<table border=\"1\" style="text-align: center; font-size: 11pt;">
<tr style="text-align: center; font-weight: bold;">
	<td colspan="7">INVENTARIO</td>
	<td colspan="6">SAIDAS</td>
	<td colspan="6">ENTRADAS</td>
	<td colspan="3">ESTOQUE FINAL</td>
</tr>
<tr style="text-align: center; font-weight: bold;"><td><input type="checkbox" title="Selecionar Todos" onclick="clickCBSelectTodos(this)" <?php echo ($todosSelected ? "checked" : "") ?>/></td>
	<td>COD</td><td>PRODUTO</td><td>UN</td><td>QTD</td><td>VU</td><td>VT</td><td>COD</td><td>PRODUTO</td><td>QTD</td><td>VU</td><td>VT</td><td>TRIB</td>
	<td>COD</td><td>PRODUTO</td><td>QTD</td><td>VU</td><td>VT</td><td>TRIB</td><td>QTD</td><td>VU</td><td>VT</td></tr>
		
<?php

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
			if($o->descricao == $item->descricao)
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
			$obj->str_nfs .= "<a href=\"javascript:nf('$fonte','".$item->descricao."')\">".$item->descricao."</a> (".$item->unidade.")<br>";
		}
		$obj->soma_qtd += $item->qtd * $item->multQtd;
		$obj->soma_valor += $item->valor;
		$obj->vu = ($obj->soma_qtd == 0 ? 0 : round($obj->soma_valor/$obj->soma_qtd, 2));
	}
	$obj->str_qtd = "<b>" . round($obj->soma_qtd, 2) . "</b><p style=\"font-size:9pt\">";
	foreach($obj->itens as $item)
	{
		$obj->str_qtd .= ($item->soma_qtd_item >= 0 ? "+" : "-") . round($item->soma_qtd_item, 2) . ($item->multQtd==1 ? "" : "*" . $item->multQtd) . "<br>";
		unset($item->soma_qtd_item);
	}
	$obj->str_qtd .= "</p>";
	unset($obj->itens);
	return $obj;
}

$idInt = 0;
foreach($info->tuplas as $tupla)
{
	$auxSaida = ($tupla->saida == null ? null : get_itens_tabela($tupla->saida->itens, "saidas"));
	$auxEntrada = ($tupla->entrada == null ? null : get_itens_tabela($tupla->entrada->itens, "entradas"));
	$ratioSE = ($auxSaida != null && $auxEntrada != null && $auxEntrada->vu > 0 ? $auxSaida->vu / $auxEntrada->vu : 1);
	$ratioIE = ($tupla->inventario != null && $auxEntrada != null && $auxEntrada->vu > 0 ? $tupla->inventario->vu / $auxEntrada->vu : 1);
	$bgcolorAlertSE = ($ratioSE > 2.5 || $ratioSE < 0.8 ? "bgcolor=\"#FFC7C7\"" : "");
	$bgcolorAlertIE = ($ratioIE > 1.5 || $ratioIE < 0.5 ? "bgcolor=\"#FFC7C7\"" : "");
	$bgcolorAlertTrib = ($tupla->saida != null && $tupla->entrada != null && $tupla->saida->itens[0]->trib != $tupla->saida->itens[0]->trib ? "bgcolor=\"#FFC7C7\"" : "");
	echo "<tr valign=\"TOP\">";
	// select
	echo "<td><input type=\"checkbox\" id=\"";
	echo $idInt++;
	echo "\" onclick=\"clickCBSelect(this)\" ";
	echo ($tupla->select ? "checked" : "");
	echo "/></td>";
	//inventario
	if($tupla->inventario == null)
	{
		echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
	}
	else
	{
		echo "<td>";
		echo $tupla->inventario->codigo;
		echo "</td><td style=\"text-align: left;\"><a href=\"javascript:inventario('";
		echo $tupla->inventario->codigo;
		echo "')\">";
		echo $tupla->inventario->descricao;
		echo "</a></td><td>";
		echo $tupla->inventario->unidade;
		echo "</td><td><b>";
		echo $tupla->inventario->qtd;
		echo "</b></td><td $bgcolorAlertIE>";
		echo $tupla->inventario->vu;
		echo "</td><td>";
		echo $tupla->inventario->valor;
		echo "</td>";
	}
	//saida
	if($tupla->saida == null)
	{
		echo "</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
	}
	else
	{
		echo "<td>";
		echo $tupla->saida->itens[0]->codigo;
		echo "</td><td style=\"text-align: left;\"><a href=\"javascript:agregado('saidas','";
		echo $tupla->saida->itens[0]->codigo;
		echo "')\">";
		echo $tupla->saida->itens[0]->descricao;
		echo "</a> (";
		echo sizeof($tupla->saida->itens);
		echo " NF)<p style=\"font-size:9pt\">";
		echo $auxSaida->str_nfs;
		echo "</p></td><td>";
		echo $auxSaida->str_qtd;
		echo "</td><td $bgcolorAlertSE>";
		echo $auxSaida->vu;
		echo "</td><td>";
		echo $auxSaida->soma_valor;
		echo "</td><td $bgcolorAlertTrib>";
		echo $tupla->saida->itens[0]->trib;
		echo "</td>";
	}
	//entrada
	if($tupla->entrada == null)
	{
		echo "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
	}
	else
	{
		echo "<td>";
		echo $tupla->entrada->itens[0]->codigo;
		echo "</td><td style=\"text-align: left;\"><a href=\"javascript:agregado('entradas','";
		echo $tupla->entrada->itens[0]->codigo;
		echo "')\">";
		echo $tupla->entrada->itens[0]->descricao;
		echo "</a> (";
		echo sizeof($tupla->entrada->itens);
		echo " NF)<p style=\"font-size:9pt\">";
		echo $auxEntrada->str_nfs;
		echo "</p></td><td>";
		echo $auxEntrada->str_qtd;
		echo "</td><td $bgcolorAlertSE $bgcolorAlertIE>";
		echo $auxEntrada->vu;
		echo "</td><td>";
		echo $auxEntrada->soma_valor;
		echo "</td><td $bgcolorAlertTrib>";
		echo $tupla->entrada->itens[0]->trib;
		echo "</td>";
	}
	// estoque final teórico
	echo "<td><b>";
	echo $tupla->ef->qtd;
	echo "</b></td><td>";
	echo $tupla->ef->vu;
	echo "</td><td>";
	echo $tupla->ef->valor;
	echo "</td></tr>";
}
echo "</table></body></html>";

?>
