<?php

function debug($a)
{
	echo "<pre>";
	echo var_dump($a);
	echo "</pre>";
}

function csvstring_to_array($string, $separatorChar = ',', $enclosureChar = '"', $newlineChar = "\n")
{
	$string = str_replace("=", "", $string);
    $array = array();
    $size = strlen($string);
    $columnIndex = 0;
    $rowIndex = 0;
    $fieldValue = "";
    $isEnclosured = false;
    for($i=0; $i<$size;$i++)
	{
        $char = $string{$i};
        $addChar = "";
        if($isEnclosured)
		{
            if($char==$enclosureChar)
			{
                if($i+1<$size && $string{$i+1} == $enclosureChar)
				{
                    $addChar = $char; // escaped char
                    $i++; // dont check next char
                }
				else
                    $isEnclosured = false;
            }
			else
                $addChar = $char;
        }
		else
		{
            if($char == $enclosureChar)
                $isEnclosured = true;
            else
			{
                if($char == $separatorChar)
				{
                    $array[$rowIndex][$columnIndex] = $fieldValue;
                    $fieldValue = "";
                    $columnIndex++;
                }
				elseif($char == $newlineChar)
				{
                    echo $char;
                    $array[$rowIndex][$columnIndex] = $fieldValue;
                    $fieldValue = "";
                    $columnIndex = 0;
                    $rowIndex++;
                }
				else
                    $addChar = $char;
            }
        }
        if($addChar != "")
            $fieldValue .= $addChar;
    }
    if($fieldValue) // save last field
        $array[$rowIndex][$columnIndex] = $fieldValue;
    return $array;
}

function get_sigla_tributacao($str)
{
	if(strpos($str, "00") !== false) return "NO";
	if(strpos($str, "10") !== false) return "TA";
	if(strpos($str, "40") !== false) return "IS";
	if(strpos($str, "60") !== false) return "ST";
	return "";
}

function formatar_string($str)
{
	return trim(preg_replace('/\s(?=\s)/', '', preg_replace('/^0*/', '', preg_replace("/[^A-Z0-9]+/", ' ', strtoupper(
		str_replace(array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','ß'),
		array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','ss'), strtolower($str)))))));
}

function formatar_numero($string)
{
	$string = trim(str_replace(".", "", $string));
	$string = str_replace(",", ".", $string);
	return (strpos($string, ".") >= 0 ? (float) $string : (int) $string);
}

function carregar_planilha($file, $limiar, $info)
{
	$csvData = file_get_contents($file);
	$lines = csvstring_to_array($csvData, ';', '"', "\n");
	$colunas = array();
	foreach($lines[1] as $coluna)
		$colunas[] = formatar_string($coluna);
	$iChave = array_search("CHAVE", $colunas);
	$iDataEmissao = array_search("DATA EMISSAO", $colunas);
	$iSituacao = array_search("SITUACAO", $colunas);
	$iTipoOperacao = array_search("TIPO OPERACAO", $colunas);
	$iNaturezaOperacao = array_search("NATUREZA OPERACAO", $colunas);
	$iCodigoProduto = array_search("CODIGO PRODUTO", $colunas);
	$iDescricaoProduto = array_search("DESCRICAO PRODUTO", $colunas);
	$iUnidadeComercial = array_search("UNIDADE COMERCIAL", $colunas);
	$iUnidadeTributavel = array_search("UNIDADE TRIBUTAVEL", $colunas);
	$iQuantidadeComercial = array_search("QUANTIDADE COMERCIAL", $colunas);
	$iQuantidadeTributavel = array_search("QUANTIDADE TRIBUTAVEL", $colunas);
	$iValorProduto = array_search("VALOR PRODUTO", $colunas);
	$iTributacaoICMS = array_search("TRIBUTACAO ICMS", $colunas);
	$agregados = array();
	if(strpos($file, "entradas") !== false)
	{
		$info->ie = trim($lines[2][array_search("IE DESTINATARIO", $colunas)]);
		$info->cnpj = trim($lines[2][array_search("DOCUMENTO DESTINATARIO", $colunas)]);
		$info->dataIni = trim($lines[2][$iDataEmissao]);
		$info->dataFim = trim($lines[sizeof($lines)-1][$iDataEmissao]);
	}
	for($i = 2; $i < sizeof($lines); $i++)
	{
		$line = (array) $lines[$i];
		$item = new StdClass();
		if(substr(formatar_string($line[$iSituacao]), 0, 3) == "100") // AUTORIZADO
		{
			$item->chave = str_replace(' ', '', $line[$iChave]);
			$item->data = trim($line[$iDataEmissao]);
			//$item->situacao = formatar_string($line[$iSituacao]);
			$item->to = formatar_numero($line[$iTipoOperacao]);
			$item->natureza = formatar_string($line[$iNaturezaOperacao]);
			//$item->codigo = str_replace(' ', '', formatar_string($line[$iCodigoProduto]));
			$item->descricao = $line[$iDescricaoProduto];
			$item->descricao_lev = get_texto_lev($line[$iDescricaoProduto]);
			$item->unidade = formatar_string($line[$iUnidadeComercial]);
			$item->unidadeTributavel = formatar_string($line[$iUnidadeTributavel]);
			$item->qtd = formatar_numero($line[$iQuantidadeComercial]);
			$item->qtdTributavel = formatar_numero($line[$iQuantidadeTributavel]);
			$item->multQtd = 1;
			$item->valor = formatar_numero($line[$iValorProduto]);
			$item->tributacaoICMS = trim(strtoupper($line[$iTributacaoICMS]));
			$item->trib = get_sigla_tributacao($item->tributacaoICMS);
			if($item->to == 0) // to = 0 -> devolucao de compra ou venda
			{
				$item->qtd = -$item->qtd;
				$item->valor = -$item->valor;
			}
			$achou = false;
			foreach($agregados as $agregado)
			{
				if($agregado->itens[0]->descricao_lev == $item->descricao_lev)
				{
					$agregado->itens[] = $item;
					$achou = true;
					break;
				}
			}
			if(!$achou)
			{
				$agregado = new StdClass();
				$agregado->itens = array();
				$agregado->itens[] = $item;
				$agregados[] = $agregado;
			}
		}
	}
	usort($agregados, "cmp_agregados");
	$todos = $agregados;
	$agregados = array();
	foreach($todos as $cada) // procura matches
	{
		$percMelhor = $limiar;
		$agregadoMelhor = null;
		foreach($agregados as $agregado)
		{
			$perc = cmp_texto($agregado->itens[0]->descricao_lev, $cada->itens[0]->descricao_lev);
			if($perc > $percMelhor)
			{
				$percMelhor = $perc;
				$agregadoMelhor = $agregado;
			}
		}
		if($agregadoMelhor != null)
		{
			foreach($cada->itens as $item)
				$agregadoMelhor->itens[] = $item;
		}
		else
			$agregados[] = $cada;
	}
	return $agregados;
}

function carregar_planilha_inventario($file)
{
	$csvData = file_get_contents($file);
	$lines = csvstring_to_array($csvData, ';', '"', "\n");
	$colunas = array();
	foreach($lines[0] as $coluna)
		$colunas[] = formatar_string($coluna);
	$iCodigo = array_search("CODIGO DA MERCADORIA", $colunas);
	$iDescricao = array_search("DESCRICAO", $colunas);
	$iUnidade = array_search("UNIDADE", $colunas);
	$iQuantidade = array_search("QUANTIDADE", $colunas);
	$iValorUnitario = array_search("VALOR UNITARIO", $colunas);
	//$iValor = array_search("VALOR BRUTO", $colunas);
	$itens = array();
	for($i = 1; $i < sizeof($lines); $i++)
	{
		$line = (array) $lines[$i];
		$item = new StdClass();
		//$item->codigo = formatar_string($line[$iCodigo]);
		$item->descricao = $line[$iDescricao];
		$item->descricao_lev = get_texto_lev($line[$iDescricao]);
		$item->unidade = formatar_string($line[$iUnidade]);
		$item->qtd = formatar_numero($line[$iQuantidade]);
		$item->vu = formatar_numero($line[$iValorUnitario]);
		$item->valor = $item->qtd * $item->vu;
		$itens[] = $item;
	}
	return $itens;
}

function carregar_analitico($inventarioArquivo, $saidasArquivo, $entradasArquivo, $limiar)
{
	$info = new stdclass();
	$nI = 0;
	$nS = 0;
	$nE = 0;
	$info->limiar = $limiar;
	$limiar = $limiar / 100;
	$inventario = null;
	if(file_exists($inventarioArquivo))
		$inventario = carregar_planilha_inventario($inventarioArquivo, $limiar);
	$saidas = carregar_planilha($saidasArquivo, $limiar, $info);
	$entradas = carregar_planilha($entradasArquivo, $limiar, $info);
	
	
	/* $qt = 0;
	$sm = 0;
	foreach($entradas as $e)
	{
		foreach($e->itens as $it)
		{
			$qt++;
			$sm += $it->valor;
		}
	}
	echo "qt: $qt, sm: $sm<br>";
	 */
	
	
	$limiar = pow($limiar, 2);
	$saidas[] = null;
	$entradas[] = null;
	$todas = array();
	foreach($saidas as $s)
	{
		foreach($entradas as $e)
		{
			$adiciona = ($s != null && $e == null) || ($s == null && $e != null);
			$percSE = 0;
			if($s != null && $e != null)
			{
				$percSE = cmp_texto($s->itens[0]->descricao_lev, $e->itens[0]->descricao_lev);
				$adiciona = $percSE > $limiar;
			}
			if($adiciona)
			{
				$tupla = new stdclass();
				$tupla->percSE = $percSE;
				$tupla->percI = 0;
				$tupla->inventario = null;
				$tupla->saida = $s;
				$tupla->entrada = $e;
				$todas[] = $tupla;
			}
		}
	}
	usort($todas, "cmp_tuplas_SE");
	
	/* $qt = 0;
	$sm = 0;
	foreach($todas as $tupla)
	{
		if($tupla->entrada != null)
		{
			foreach($tupla->entrada->itens as $it)
			{
				$qt++;
				$sm += $it->valor;
			}
		}
	}
	echo "qt: $qt, sm: $sm<br>";
	exit();
	 */
	
	
	//echo "s: ".sizeof($saidas). ", e:".sizeof($entradas).", e.s:".sizeof($todas)."<br>"; exit();
	
	
	
	$tuplas = array();
	foreach($todas as $cada)
	{
		$achou = false;
		foreach($tuplas as $tupla)
		{
			$achou = ($cada->saida != null && $cada->saida == $tupla->saida) || ($cada->entrada != null && $cada->entrada == $tupla->entrada);
			if($achou) break;
		}
		if(!$achou)
		{
			$tuplas[] = $cada;
			if($inventario != null)
			{
				$melhorInventario = null;
				$melhorPerc = $limiar * 2.00; // percIS + percIE
				foreach($inventario as $i)
				{
					if($cada->saida != null && $cada->entrada != null)
					{
						$perc = cmp_texto($i->descricao_lev, $cada->saida->itens[0]->descricao_lev) + cmp_texto($i->descricao_lev, $cada->entrada->itens[0]->descricao_lev);
						if($perc > $melhorPerc)
						{
							$melhorInventario = $i;
							$melhorPerc = $perc;
						}
					}
					elseif($cada->saida != null)
					{
						$perc = 2.00 * cmp_texto($i->descricao_lev, $cada->saida->itens[0]->descricao_lev);
						if($perc > $melhorPerc)
						{
							$melhorInventario = $i;
							$melhorPerc = $perc;
						}
					}
					elseif($cada->entrada != null)
					{
						$perc = 2.00 * cmp_texto($i->descricao_lev, $cada->entrada->itens[0]->descricao_lev);
						if($perc > $melhorPerc)
						{
							$melhorInventario = $i;
							$melhorPerc = $perc;
						}
					}
				}
				if($melhorInventario != null)
				{
					$cada->percI = $melhorPerc;
					$cada->inventario = $melhorInventario;
					unset($inventario[array_search($melhorInventario, $inventario, false)]);
				}
			}
			$cada->ef = get_estoque_final($cada);
			$cada->select = 0;
			if($cada->inventario != null)
				$cada->inventario->codigo = ++$nI;
			if($cada->saida != null)
			{
				$nS++;
				foreach($cada->saida->itens as $item)
					$item->codigo = $nS;
			}
			if($cada->entrada != null)
			{
				$nE++;
				foreach($cada->entrada->itens as $item)
					$item->codigo = $nE;
			}
		}
	}
	if($inventario != null)
	{
		foreach($inventario as $i)
		{
			//tupla so com inventario
			$tuplaNova = new stdclass();
			$tuplaNova->percSE = 0;
			$tuplaNova->percI = 0;
			$tuplaNova->inventario = $i;
			$tuplaNova->saida = null;
			$tuplaNova->entrada = null;
			$tuplaNova->inventario->codigo = ++$nI;
			$tuplaNova->ef = get_estoque_final($tuplaNova);
			$tuplaNova->select = 0;
			$tuplas[] = $tuplaNova;
		}
	}
	$info->tuplas = $tuplas;
	return $info;
}

function cmp_agregados($a, $b)
{
	return strcmp($a->itens[0]->descricao, $b->itens[0]->descricao);
}

function cmp_tuplas($a, $b)
{
	return ($b->percI + $b->percSE) > ($a->percI + $a->percSE);
}

function cmp_tuplas_SE($a, $b)
{
	return $b->percSE > $a->percSE;
}

function cmp_descricao($a, $b)
{
	$objA = ($a->inventario != null ? $a->inventario : ($a->saida != null ? $a->saida->itens[0] : ($a->entrada != null ? $a->entrada->itens[0] : null)));
	$objB = ($b->inventario != null ? $b->inventario : ($b->saida != null ? $b->saida->itens[0] : ($b->entrada != null ? $b->entrada->itens[0] : null)));
	return strcmp($objA->descricao, $objB->descricao);
}

function cmp_et_pos($a, $b)
{
	return $b->ef->valor - $a->ef->valor;
}

function cmp_et_neg($a, $b)
{
	return $a->ef->valor - $b->ef->valor;
}

function cmp_select($a, $b)
{
	return $b->select - $a->select;
}

/* float:function cmp_texto_OLD($a, $b)
{
	similar_text($a, $b, $p1);
	similar_text($b, $a, $p2);
	return ($p1+$p2) / 2.00;
} */

function get_texto_lev($a)
{
	$a = preg_replace('/\b[A-Z0-9]{1,2}\b/', '', preg_replace("/[^A-Z0-9]+/", ' ', strtoupper(
		str_replace(array('à','á','â','ã','ä','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ÿ','ß'),
		array('a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','u','u','u','u','y','y','ss'), strtolower($a)))));
	$arrA = explode(' ', $a);
	sort($arrA);
	$a = implode('', $arrA);
	return $a;
}

float:function cmp_texto($a, $b)
{
	return max(1.00 - (levenshtein($a, $b, 1, 2, 1) / max(strlen($a), strlen($b))), 0.00); 
}

function get_estoque_final($tupla)
{
	$qtd = 0;
	$vu = 0;
	if($tupla->saida != null)
	{
		$qtdSaida = 0;
		$valorSaida = 0;
		foreach($tupla->saida->itens as $item)
		{
			$qtdSaida += $item->qtd * $item->multQtd;
			$valorSaida += $item->valor;
		}
		$qtd -= $qtdSaida;
		if($qtdSaida != 0) $vu = ($valorSaida / $qtdSaida) * 0.7;
	}
	if($tupla->inventario != null)
	{
		$qtd += $tupla->inventario->qtd;
		$vu = $tupla->inventario->vu;
	}
	if($tupla->entrada != null)
	{
		$qtdEntrada = 0;
		$valorEntrada = 0;
		foreach($tupla->entrada->itens as $item)
		{
			$qtdEntrada += $item->qtd * $item->multQtd;
			$valorEntrada += $item->valor;
		}
		$qtd += $qtdEntrada;
		if($qtdEntrada != 0) $vu = $valorEntrada / $qtdEntrada;
	}
	$ef = new stdclass();
	$ef->qtd = round($qtd, 2);
	$ef->vu = round($vu, 2);
	$ef->valor = round($qtd * $vu, 2);
	return $ef;
}

function reagruparItens($fonte, &$tuplas, $tupla, $agregado, $itens, $novoCodigo)
{
	// retirar do antigo
	foreach($agregado->itens as $item)
	{
		foreach($itens as $itemRetirar)
		{
			if($item == $itemRetirar)
			{
				// remove item
				unset($agregado->itens[array_search($itemRetirar, $agregado->itens, false)]);
				$agregado->itens = array_values($agregado->itens);
				if(sizeof($agregado->itens) == 0)
				{
					// remove agregado da tupla
					if($fonte == "saidas") $tupla->saida = null;
					elseif($fonte == "entradas") $tupla->entrada = null;
					// remove tupla?
					if($tupla->inventario == null && $tupla->saida == null && $tupla->entrada == null)
					{
						unset($tuplas[array_search($tupla, $tuplas, false)]);
						unset($tupla);
						$tuplas = array_values($tuplas);
					}
				}
				if(isset($tupla) && $tupla != null)
					$tupla->ef = get_estoque_final($tupla);
			}
		}
	}
	// colocar no novo
	$achou = false;
	foreach($tuplas as $cada)
	{
		if($fonte == "saidas")
			$agregado = $cada->saida;
		elseif($fonte == "entradas")
			$agregado = $cada->entrada;
		if($agregado != null && $agregado->itens[0]->codigo == $novoCodigo)
		{
			$achou = true;
			foreach($itens as $item)
			{
				$agregado->itens[] = $item; // adiciona
				$item->codigo = $novoCodigo;
			}
			$cada->ef = get_estoque_final($cada);
			break;
		}
	}
	if(!$achou)
	{
		// cria tupla nova
		$novaTupla = new stdclass();
		$novaTupla->percI = 0;
		$novaTupla->percSE = 0;
		$novaTupla->inventario = null;
		$novaTupla->saida = null;
		$novaTupla->entrada = null;
		$novaTupla->select = 0;
		$newAgreg = new stdclass();
		foreach($itens as $item)
			$item->codigo = $novoCodigo;
		$newAgreg->itens = $itens;
		if($fonte == "saidas")
			$novaTupla->saida = $newAgreg;
		elseif($fonte == "entradas")
			$novaTupla->entrada = $newAgreg;
		$novaTupla->ef = get_estoque_final($novaTupla);
		$tuplas[] = $novaTupla;
	}
}

function reassociarInventario($fonte, &$tuplas, $tupla, $novoCodigoInventario)
{
	foreach($tuplas as $cada)
	{
		if($cada->inventario != null && $cada->inventario->codigo == $novoCodigoInventario)
		{
			// juntar aqui os itens da tupla
			if($fonte == "saidas")
			{
				if($cada->saida == null)
					$cada->saida = $tupla->saida;
				else
				{
					foreach($tupla->saida->itens as $item)
						$cada->saida->itens[] = $item;
				}
				$tupla->saida = null;
			}
			elseif($fonte == "entradas")
			{
				if($cada->entrada == null)
					$cada->entrada = $tupla->entrada;
				else
				{
					foreach($tupla->entrada->itens as $item)
						$cada->entrada->itens[] = $item;
				}
				$tupla->entrada = null;
			}
			// retifica tupla antiga
			if($tupla->inventario == null && $tupla->saida == null && $tupla->entrada == null)
			{
				unset($tuplas[array_search($tupla, $tuplas, false)]);
				unset($tupla);
				$tuplas = array_values($tuplas);
			}
			else
				$tupla->ef = get_estoque_final($tupla);
			// retifica tupla nova
			$cada->ef = get_estoque_final($cada);
			break;
		}
	}
}

function reassociarEntrada(&$tuplas, $tupla, $novoCodigoEntrada)
{
	foreach($tuplas as $cada)
	{
		if($cada->entrada != null && $cada->entrada->itens[0]->codigo == $novoCodigoEntrada)
		{
			// juntar aqui os itens da tupla
			if($cada->saida == null)
				$cada->saida = $tupla->saida;
			else
			{
				foreach($tupla->saida->itens as $item)
					$cada->saida->itens[] = $item;
			}
			$tupla->saida = null;
			// retifica tupla antiga
			if($tupla->inventario == null && $tupla->saida == null && $tupla->entrada == null)
			{
				unset($tuplas[array_search($tupla, $tuplas, false)]);
				unset($tupla);
				$tuplas = array_values($tuplas);
			}
			else
				$tupla->ef = get_estoque_final($tupla);
			// retifica tupla nova
			$cada->ef = get_estoque_final($cada);
			break;
		}
	}
}

function reassociarSaida(&$tuplas, $tupla, $novoCodigoSaida)
{
	foreach($tuplas as $cada)
	{
		if($cada->saida != null && $cada->saida->itens[0]->codigo == $novoCodigoSaida)
		{
			// juntar aqui os itens da tupla
			if($cada->entrada == null)
				$cada->entrada = $tupla->entrada;
			else
			{
				foreach($tupla->entrada->itens as $item)
					$cada->entrada->itens[] = $item;
			}
			$tupla->entrada = null;
			// retifica tupla antiga
			if($tupla->inventario == null && $tupla->saida == null && $tupla->entrada == null)
			{
				unset($tuplas[array_search($tupla, $tuplas, false)]);
				unset($tupla);
				$tuplas = array_values($tuplas);
			}
			else
				$tupla->ef = get_estoque_final($tupla);
			// retifica tupla nova
			$cada->ef = get_estoque_final($cada);
			break;
		}
	}
}

?>
