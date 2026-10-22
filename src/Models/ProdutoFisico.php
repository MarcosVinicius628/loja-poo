<?php

namespace App\Models;

use InvalidArgumentException;

class ProdutoFisico extends Produto
{
    private float $peso;
    private float $largura;
    private float $altura;
    private float $profundidade;

    public function __construct(
        int $codigo,
        string $nome,
        float $preco,
        int $estoque,
        float $peso,
        float $largura,
        float $altura,
        float $profundidade
    ) {
        parent::__construct($codigo, $nome, $preco, $estoque);

        if ($peso <= 0 || $largura < 0 || $altura < 0 || $profundidade < 0) {
            throw new InvalidArgumentException('Peso deve ser positivo e dimensões não podem ser negativas.');
        }

        $this->peso = $peso;
        $this->largura = $largura;
        $this->altura = $altura;
        $this->profundidade = $profundidade;
    }

    public function getPeso(): float
    {
        return $this->peso;
    }

    public function getLargura(): float
    {
        return $this->largura;
    }

    public function getAltura(): float
    {
        return $this->altura;
    }

    public function getProfundidade(): float
    {
        return $this->profundidade;
    }

    public function calcularFrete(): float
    {
        // Regra didática: o frete custa R$ 10,00 por quilograma do produto.
        return round($this->peso * 10.00, 2);
    }

    public function calcularPrecoFinal(): float
    {
        return round($this->getPreco() + $this->calcularFrete(), 2);
    }
}
