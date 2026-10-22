<?php

namespace App\Models;

use App\Exceptions\PedidoInvalidoException;
use App\Interfaces\PagamentoInterface;
use DateTimeImmutable;
use InvalidArgumentException;

class Pedido
{
    public const STATUS_ABERTO = 'Aberto';
    public const STATUS_AGUARDANDO_PAGAMENTO = 'Aguardando Pagamento';
    public const STATUS_PAGO = 'Pago';
    public const STATUS_ENVIADO = 'Enviado';
    public const STATUS_CONCLUIDO = 'Concluído';
    public const STATUS_CANCELADO = 'Cancelado';

    private const TRANSICOES = [
        self::STATUS_ABERTO => [self::STATUS_AGUARDANDO_PAGAMENTO, self::STATUS_CANCELADO],
        self::STATUS_AGUARDANDO_PAGAMENTO => [self::STATUS_ABERTO, self::STATUS_PAGO, self::STATUS_CANCELADO],
        self::STATUS_PAGO => [self::STATUS_ENVIADO, self::STATUS_CONCLUIDO],
        self::STATUS_ENVIADO => [self::STATUS_CONCLUIDO],
        self::STATUS_CONCLUIDO => [],
        self::STATUS_CANCELADO => [],
    ];

    private int $numero;
    private Cliente $cliente;
    private DateTimeImmutable $data;
    private array $itens = [];
    private string $status = self::STATUS_ABERTO;

    public function __construct(int $numero, Cliente $cliente, ?DateTimeImmutable $data = null)
    {
        if ($numero <= 0) {
            throw new InvalidArgumentException('O número do pedido deve ser positivo.');
        }

        $this->numero = $numero;
        $this->cliente = $cliente;
        $this->data = $data ?? new DateTimeImmutable();
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function getCliente(): Cliente
    {
        return $this->cliente;
    }

    public function getData(): DateTimeImmutable
    {
        return $this->data;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getItens(): array
    {
        return $this->itens;
    }

    public function adicionarItem(Produto $produto, int $quantidade): void
    {
        $this->garantirPedidoEditavel();
        $this->itens[] = new ItemPedido($produto, $quantidade);
    }

    public function removerItem(int $indice): void
    {
        $this->garantirPedidoEditavel();
        if (!\array_key_exists($indice, $this->itens)) {
            throw new PedidoInvalidoException('O item informado não existe no pedido.');
        }

        unset($this->itens[$indice]);
        $this->itens = array_values($this->itens);
    }

    public function calcularValorTotal(): float
    {
        return round($this->calcularSubtotalItens() + $this->calcularFrete(), 2);
    }

    public function calcularSubtotalItens(): float
    {
        $subtotal = 0.0;
        foreach ($this->itens as $item) {
            $subtotal += $item->getSubtotal();
        }

        return round($subtotal, 2);
    }

    public function calcularFrete(): float
    {
        $frete = 0.0;
        foreach ($this->itens as $item) {
            $produto = $item->getProduto();
            if ($produto instanceof ProdutoFisico) {
                $frete += $produto->calcularFrete() * $item->getQuantidade();
            }
        }

        return round($frete, 2);
    }

    public function exibirDadosCompletos(?PagamentoInterface $pagamento = null): string
    {
        $linhas = [
            '==========================================',
            'PEDIDO Nº ' . $this->numero,
            '==========================================',
            'Cliente: ' . $this->cliente->getNome(),
            '------------------------------------------',
            'ITENS',
            '------------------------------------------',
        ];

        foreach ($this->itens as $item) {
            $linhas[] = 'Produto: ' . $item->getProduto()->getNome();
            $linhas[] = 'Quantidade: ' . $item->getQuantidade();
            $linhas[] = 'Valor unitário: ' . $this->formatarMoeda($item->getPrecoUnitario());
            $linhas[] = 'Subtotal: ' . $this->formatarMoeda($item->getSubtotal());
        }

        $linhas[] = '------------------------------------------';
        $linhas[] = 'Frete: ' . $this->formatarMoeda($this->calcularFrete());
        $linhas[] = 'Valor Total: ' . $this->formatarMoeda($this->calcularValorTotal());
        if ($pagamento !== null) {
            $linhas[] = 'Forma de pagamento: ' . $pagamento->getNome();
        }
        $linhas[] = 'Status: ' . $this->status;
        if ($pagamento !== null && $pagamento->getConfirmacao() !== '') {
            $linhas[] = $pagamento->getConfirmacao();
        }
        $linhas[] = '==========================================';

        return implode(PHP_EOL, $linhas) . PHP_EOL;
    }

    public function alterarStatus(string $novoStatus): void
    {
        if (!\array_key_exists($novoStatus, self::TRANSICOES)) {
            throw new PedidoInvalidoException('Status de pedido inválido.');
        }
        if (!\in_array($novoStatus, self::TRANSICOES[$this->status], true)) {
            throw new PedidoInvalidoException(
                \sprintf('Não é permitido alterar o pedido de "%s" para "%s".', $this->status, $novoStatus)
            );
        }

        $this->status = $novoStatus;
    }

    private function garantirPedidoEditavel(): void
    {
        if ($this->status === self::STATUS_CONCLUIDO || $this->status === self::STATUS_CANCELADO) {
            throw new PedidoInvalidoException('Não é possível alterar um pedido concluído ou cancelado.');
        }
    }

    private function formatarMoeda(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}
