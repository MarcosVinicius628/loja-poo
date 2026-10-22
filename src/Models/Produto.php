<?php

namespace App\Models;

use App\Exceptions\EstoqueInsuficienteException;
use InvalidArgumentException;

abstract class Produto
{
    private int $codigo;
    private string $nome;
    private float $preco;
    private int $estoque;

    public function __construct(int $codigo, string $nome, float $preco, int $estoque)
    {
        if ($codigo <= 0 || trim($nome) === '') {
            throw new InvalidArgumentException('Código e nome do produto são obrigatórios.');
        }
        if ($preco < 0) {
            throw new InvalidArgumentException('O preço do produto não pode ser negativo.');
        }
        if ($estoque < 0) {
            throw new InvalidArgumentException('O estoque não pode ser negativo.');
        }

        $this->codigo = $codigo;
        $this->nome = trim($nome);
        $this->preco = $preco;
        $this->estoque = $estoque;
    }

    public function getCodigo(): int
    {
        return $this->codigo;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function getEstoque(): int
    {
        return $this->estoque;
    }

    public function alterarPreco(float $preco): void
    {
        if ($preco < 0) {
            throw new InvalidArgumentException('O preço do produto não pode ser negativo.');
        }

        $this->preco = $preco;
    }

    public function adicionarEstoque(int $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException('A quantidade a adicionar deve ser maior que zero.');
        }

        $this->estoque += $quantidade;
    }

    public function removerEstoque(int $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new InvalidArgumentException('A quantidade a remover deve ser maior que zero.');
        }
        if ($quantidade > $this->estoque) {
            throw new EstoqueInsuficienteException(
                sprintf('Estoque insuficiente para "%s". Disponível: %d.', $this->nome, $this->estoque)
            );
        }

        $this->estoque -= $quantidade;
    }

    abstract public function calcularPrecoFinal(): float;
}
