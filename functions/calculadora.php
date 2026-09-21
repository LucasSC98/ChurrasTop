<?php
declare(strict_types=1);

function pessoasEquivalentes(int $adultos, int $criancas): float
{
    return $adultos + ($criancas * 0.5);
}

function multiplicadorTipo(string $tipo): float
{
    return ['Econômico' => 0.8, 'Tradicional' => 1.0, 'ChurrasTop' => 1.2][$tipo] ?? 1.0;
}

function multiplicadorDuracao(string $duracao): float
{
    return ['2 horas' => 0.8, '4 horas' => 1.0, '6 horas ou mais' => 1.25][$duracao] ?? 1.0;
}

function calcularLista(array $produtos, int $adultos, int $criancas, string $tipo, string $duracao): array
{
    if ($adultos < 0 || $criancas < 0 || pessoasEquivalentes($adultos, $criancas) <= 0) {
        return [];
    }
    $fator = pessoasEquivalentes($adultos, $criancas) * multiplicadorTipo($tipo) * multiplicadorDuracao($duracao);
    $lista = [];
    foreach ($produtos as $produto) {
        $quantidade = ceil($fator * (float) $produto['quantidade_por_pessoa'] * 10) / 10;
        $lista[] = [...$produto, 'quantidade' => $quantidade, 'subtotal' => $quantidade * (float) $produto['preco']];
    }
    return $lista;
}

function totalLista(array $lista): float
{
    $total = 0.0;
    foreach ($lista as $item) {
        $total += (float) $item['subtotal'];
    }
    return $total;
}

function validarArrayProdutos(array $produtos): array
{
    $erros = [];
    if ($produtos === []) {
        $erros[] = 'Nenhum produto foi encontrado na lista. Verifique o banco de dados.';
    } else {
        foreach ($produtos as $produto) {
            if ((float) $produto['preco'] < 0 || (float) $produto['quantidade_por_pessoa'] < 0) {
                $erros[] = 'Existem produtos com preço ou quantidade por pessoa inválidos (valores negativos).';
                break;
            }
        }
    }
    return $erros;
}
