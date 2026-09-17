<?php
declare(strict_types=1);

function filtrarProdutos(array $produtos, string $busca, string $categoria = '', ?float $min = null, ?float $max = null): array
{
    $busca = mb_strtolower(trim($busca));
    return array_values(array_filter($produtos, static function (array $produto) use ($busca, $categoria, $min, $max): bool {
        $nomeValido = $busca === '' || str_contains(mb_strtolower($produto['nome']), $busca);
        $categoriaValida = $categoria === '' || $produto['categoria'] === $categoria;
        $precoValido = ($min === null || (float) $produto['preco'] >= $min)
            && ($max === null || (float) $produto['preco'] <= $max);
        return $nomeValido && $categoriaValida && $precoValido;
    }));
}

function moeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
