<?php

namespace App\Models;

use InvalidArgumentException;

class ItemPedido
{
    private Produto $produto;
    private int $quantidade;
    private float $precoUnitario;
    private float $subtotal;

    public function __construct(Produto $produto, int $quantidade)
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException('A quantidade do item deve ser maior que zero.');
        }

        $this->produto = $produto;
        $this->quantidade = $quantidade;
        $this->precoUnitario = $produto->getPreco();
        $this->subtotal = round($this->precoUnitario * $this->quantidade, 2);
    }

    public function getProduto(): Produto
    {
        return $this->produto;
    }

    public function getQuantidade(): int
    {
        return $this->quantidade;
    }

    public function getPrecoUnitario(): float
    {
        return $this->precoUnitario;
    }

    public function getSubtotal(): float
    {
        return $this->subtotal;
    }
}
