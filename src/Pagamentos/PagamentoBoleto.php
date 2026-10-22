<?php

namespace App\Pagamentos;

use App\Exceptions\PagamentoException;
use App\Interfaces\PagamentoInterface;

class PagamentoBoleto implements PagamentoInterface
{
    private string $confirmacao = '';

    public function pagar(float $valor): void
    {
        if ($valor <= 0) {
            throw new PagamentoException('O valor do pagamento via boleto deve ser positivo.');
        }

        $this->confirmacao = sprintf(
            'Pagamento de boleto confirmado: R$ %s.',
            number_format($valor, 2, ',', '.')
        );
    }

    public function getNome(): string
    {
        return 'Boleto';
    }

    public function getConfirmacao(): string
    {
        return $this->confirmacao;
    }
}
