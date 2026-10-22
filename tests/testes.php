<?php

require_once __DIR__ . '/../autoload.php';

use App\Exceptions\EstoqueInsuficienteException;
use App\Exceptions\PedidoInvalidoException;
use App\Models\Cliente;
use App\Models\Endereco;
use App\Models\Pedido;
use App\Models\ProdutoFisico;
use App\Pagamentos\PagamentoPix;
use App\Services\PedidoService;

$testesAprovados = 0;

function imprimirResultado(string $mensagem): void
{
    if (PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') {
        echo $mensagem . PHP_EOL;
        return;
    }

    echo htmlspecialchars($mensagem, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "<br>\n";
}

function criarClienteTeste(): Cliente
{
    $endereco = new Endereco('Rua A', '1', '', 'Centro', 'São Paulo', 'SP', '01000-000');

    return new Cliente(1, 'Cliente Teste', '123.456.789-00', 'teste@email.com', '11999990000', $endereco);
}

function verificarExcecao(
    string $excecaoEsperada,
    callable $operacao,
    string $teste,
    string $mensagemEsperada
): void
{
    global $testesAprovados;

    try {
        $operacao();
    } catch (Throwable $exception) {
        if ($exception instanceof $excecaoEsperada) {
            $testesAprovados++;
            imprimirResultado("[SUCESSO] {$teste}: {$mensagemEsperada}");
            return;
        }

        imprimirResultado(
            "[FALHA] {$teste} — esperado {$excecaoEsperada}, recebido "
            . get_class($exception) . ': ' . $exception->getMessage()
        );
        throw new RuntimeException(
            "{$teste}: esperava {$excecaoEsperada}, recebeu " . get_class($exception) . '.',
            0,
            $exception
        );
    }

    imprimirResultado("[FALHA] {$teste} — nenhuma exceção foi lançada.");
    throw new RuntimeException("{$teste}: nenhuma exceção foi lançada.");
}

$servico = new PedidoService();
$produto = new ProdutoFisico(1, 'Produto de teste', 50.00, 1, 1.0, 10.0, 10.0, 10.0);
$pedidoSemItens = new Pedido(2001, criarClienteTeste());

verificarExcecao(
    EstoqueInsuficienteException::class,
    function () use ($servico, $produto): void {
        $pedido = new Pedido(2002, criarClienteTeste());
        $pedido->adicionarItem($produto, 2);
        $servico->processarPedido($pedido, new PagamentoPix());
    },
    '1 - Comprar quantidade superior ao estoque',
    'A quantidade solicitada é superior ao estoque disponível.'
);

verificarExcecao(
    InvalidArgumentException::class,
    function () use ($produto): void {
        $pedido = new Pedido(2003, criarClienteTeste());
        $pedido->adicionarItem($produto, 0);
    },
    '2 - Adicionar quantidade zero',
    'A quantidade deve ser maior que zero.'
);

verificarExcecao(
    InvalidArgumentException::class,
    function (): void {
        new ProdutoFisico(3, 'Preço inválido', -1.00, 1, 1.0, 1.0, 1.0, 1.0);
    },
    '3 - Criar produto com preço negativo',
    'O preço do produto não pode ser negativo.'
);

verificarExcecao(
    PedidoInvalidoException::class,
    function () use ($servico, $pedidoSemItens): void {
        $servico->processarPedido($pedidoSemItens, new PagamentoPix());
    },
    '4 - Processar pedido sem produtos',
    'O pedido precisa ter pelo menos um produto.'
);

verificarExcecao(
    PedidoInvalidoException::class,
    function () use ($produto, $servico): void {
        $pedido = new Pedido(2004, criarClienteTeste());
        $pedido->adicionarItem($produto, 1);
        $pagamento = new PagamentoPix();
        $servico->processarPedido($pedido, $pagamento);
        if ($produto->getEstoque() !== 0 || $pedido->getStatus() !== Pedido::STATUS_PAGO) {
            throw new RuntimeException('O pagamento deveria confirmar e reduzir o estoque antes da conclusão.');
        }
        $servico->finalizarPedido($pedido);
        $pedido->adicionarItem($produto, 1);
    },
    '5 - Adicionar produto após pedido concluído',
    'Não é permitido alterar um pedido concluído.'
);

verificarExcecao(
    PedidoInvalidoException::class,
    function () use ($servico): void {
        $pedido = new Pedido(2005, criarClienteTeste());
        $pedido->alterarStatus(Pedido::STATUS_CANCELADO);
        $servico->processarPedido($pedido, new PagamentoPix());
    },
    '6 - Pagar pedido cancelado',
    'Não é possível pagar um pedido cancelado.'
);

imprimirResultado("Resultado final: {$testesAprovados}/6 testes aprovados.");
