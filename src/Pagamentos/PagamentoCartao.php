<?php

namespace App\Pagamentos;

use App\Exceptions\PagamentoException;
use App\Interfaces\PagamentoInterface;
use InvalidArgumentException;

class PagamentoCartao implements PagamentoInterface
{
    private int $parcelas;
    private string $confirmacao = '';

    public function __construct(int $parcelas = 1)
    {
        if ($parcelas < 1 || $parcelas > 12) {
            throw new InvalidArgumentException('O cartão permite de 1 a 12 parcelas.');
        }

        $this->parcelas = $parcelas;
    }

    public function pagar(float $valor): void
    {
        if ($valor <= 0) {
            throw new PagamentoException('O valor do pagamento com cartão deve ser positivo.');
        }

        $this->confirmacao = sprintf(
            'Pagamento com cartão confirmado em %d parcela(s): R$ %s.',
            $this->parcelas,
            number_format($valor, 2, ',', '.')
        );
    }

    public function getNome(): string
    {
        return 'Cartão';
    }

    public function getConfirmacao(): string
    {
        return $this->confirmacao;
    }
}
