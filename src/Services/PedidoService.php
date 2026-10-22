<?php

namespace App\Services;

use App\Exceptions\EstoqueInsuficienteException;
use App\Exceptions\PagamentoException;
use App\Exceptions\PedidoInvalidoException;
use App\Interfaces\PagamentoInterface;
use App\Models\ItemPedido;
use App\Models\Pedido;

class PedidoService
{
    public function validarPedido(Pedido $pedido): void
    {
        if ($pedido->getStatus() === Pedido::STATUS_CANCELADO) {
            throw new PedidoInvalidoException('Um pedido cancelado não pode ser pago.');
        }
        if ($pedido->getStatus() !== Pedido::STATUS_ABERTO) {
            throw new PedidoInvalidoException('Somente pedidos abertos podem ser processados.');
        }
        if (\count($pedido->getItens()) === 0) {
            throw new PedidoInvalidoException('O pedido deve possuir pelo menos um item.');
        }
    }

    public function verificarDisponibilidadeEstoque(Pedido $pedido): void
    {
        $quantidades = [];
        foreach ($pedido->getItens() as $item) {
            $produto = $item->getProduto();
            $identidade = spl_object_id($produto);
            if (!isset($quantidades[$identidade])) {
                $quantidades[$identidade] = ['produto' => $produto, 'quantidade' => 0];
            }
            $quantidades[$identidade]['quantidade'] += $item->getQuantidade();
        }

        foreach ($quantidades as $linha) {
            if ($linha['quantidade'] > $linha['produto']->getEstoque()) {
                throw new EstoqueInsuficienteException(
                    \sprintf(
                        'Estoque insuficiente para "%s". Solicitado: %d; disponível: %d.',
                        $linha['produto']->getNome(),
                        $linha['quantidade'],
                        $linha['produto']->getEstoque()
                    )
                );
            }
        }
    }

    public function processarPedido(Pedido $pedido, PagamentoInterface $pagamento): void
    {
        $this->validarPedido($pedido);
        $this->verificarDisponibilidadeEstoque($pedido);
        $pedido->alterarStatus(Pedido::STATUS_AGUARDANDO_PAGAMENTO);

        try {
            $pagamento->pagar($pedido->calcularValorTotal());
        } catch (PagamentoException $exception) {
            $pedido->alterarStatus(Pedido::STATUS_ABERTO);
            throw $exception;
        }

        $this->atualizarEstoque($pedido);
        $pedido->alterarStatus(Pedido::STATUS_PAGO);
    }

    public function atualizarEstoque(Pedido $pedido): void
    {
        foreach ($pedido->getItens() as $item) {
            $this->removerUnidades($item);
        }
    }

    public function finalizarPedido(Pedido $pedido): void
    {
        if ($pedido->getStatus() !== Pedido::STATUS_PAGO && $pedido->getStatus() !== Pedido::STATUS_ENVIADO) {
            throw new PedidoInvalidoException('O pedido deve estar pago ou enviado antes da conclusão.');
        }

        $pedido->alterarStatus(Pedido::STATUS_CONCLUIDO);
    }

    private function removerUnidades(ItemPedido $item): void
    {
        $item->getProduto()->removerEstoque($item->getQuantidade());
    }
}
