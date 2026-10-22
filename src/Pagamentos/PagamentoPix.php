<?php

namespace App\Pagamentos;

use App\Exceptions\PagamentoException;
use App\Interfaces\PagamentoInterface;

class PagamentoPix implements PagamentoInterface
{
    private string $confirmacao = '';

    public function pagar(float $valor): void
    {
        if ($valor <= 0) {
            throw new PagamentoException('O valor do pagamento via PIX deve ser positivo.');
        }

        $this->confirmacao = sprintf('Pagamento PIX confirmado: R$ %s.', number_format($valor, 2, ',', '.'));
    }

    public function getNome(): string
    {
        return 'PIX';
    }

    public function getConfirmacao(): string
    {
        return $this->confirmacao;
    }
}
