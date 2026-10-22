<?php

require_once __DIR__ . '/autoload.php';

use App\Exceptions\EstoqueInsuficienteException;
use App\Exceptions\PagamentoException;
use App\Exceptions\PedidoInvalidoException;
use App\Models\Cliente;
use App\Models\Endereco;
use App\Models\Pedido;
use App\Models\ProdutoDigital;
use App\Models\ProdutoFisico;
use App\Pagamentos\PagamentoPix;
use App\Services\PedidoService;

$saida = '';

try {
    $endereco = new Endereco(
        'Rua das Flores',
        '100',
        'Apto 12',
        'Centro',
        'São Paulo',
        'SP',
        '01000-000'
    );
    $cliente = new Cliente(1, 'Carlos Silva', '123.456.789-00', 'carlos@email.com', '(11) 99999-0000', $endereco);

    $teclado = new ProdutoFisico(1, 'Teclado Mecânico', 350.00, 10, 1.2, 45.0, 5.0, 15.0);
    $curso = new ProdutoDigital(
        2,
        'Curso PHP Orientado a Objetos',
        199.90,
        100,
        2048.0,
        'MP4',
        'https://exemplo.com/download/curso-php'
    );

    $pedido = new Pedido(1001, $cliente);
    $pedido->adicionarItem($teclado, 2);
    $pedido->adicionarItem($curso, 1);

    $pagamento = new PagamentoPix();
    $servico = new PedidoService();
    $servico->processarPedido($pedido, $pagamento);

    $saida = $pedido->exibirDadosCompletos($pagamento);
} catch (EstoqueInsuficienteException | PedidoInvalidoException | PagamentoException $exception) {
    $saida = 'Não foi possível processar o pedido: ' . $exception->getMessage() . "\n";
}

$testesAprovados = 0;
$resultadosTestes = [];
$criarClienteTeste = static function (): Cliente {
    $endereco = new Endereco('Rua A', '1', '', 'Centro', 'São Paulo', 'SP', '01000-000');

    return new Cliente(1, 'Cliente Teste', '123.456.789-00', 'teste@email.com', '11999990000', $endereco);
};
$verificarExcecao = static function (
    string $excecaoEsperada,
    callable $operacao,
    string $teste,
    string $mensagemEsperada
) use (&$testesAprovados, &$resultadosTestes): void {
    try {
        $operacao();
    } catch (Throwable $exception) {
        if ($exception instanceof $excecaoEsperada) {
            $testesAprovados++;
            $resultadosTestes[] = "{$teste}: {$mensagemEsperada}";
            return;
        }

        $resultadosTestes[] = "[FALHA] {$teste}: esperado {$excecaoEsperada}, recebido "
            . get_class($exception) . ': ' . $exception->getMessage();
        return;
    }

    $resultadosTestes[] = "[FALHA] {$teste}: nenhuma exceção foi lançada.";
};

$servicoTeste = new PedidoService();
$produtoTeste = new ProdutoFisico(1, 'Produto de teste', 50.00, 1, 1.0, 10.0, 10.0, 10.0);
$pedidoSemItens = new Pedido(2001, $criarClienteTeste());

$verificarExcecao(
    EstoqueInsuficienteException::class,
    static function () use ($servicoTeste, $produtoTeste, $criarClienteTeste): void {
        $pedido = new Pedido(2002, $criarClienteTeste());
        $pedido->adicionarItem($produtoTeste, 2);
        $servicoTeste->processarPedido($pedido, new PagamentoPix());
    },
    '1 - Comprar quantidade superior ao estoque',
    'A quantidade solicitada é superior ao estoque disponível.'
);

$verificarExcecao(
    InvalidArgumentException::class,
    static function () use ($produtoTeste, $criarClienteTeste): void {
        $pedido = new Pedido(2003, $criarClienteTeste());
        $pedido->adicionarItem($produtoTeste, 0);
    },
    '2 - Adicionar quantidade zero',
    'A quantidade deve ser maior que zero.'
);

$verificarExcecao(
    InvalidArgumentException::class,
    static function (): void {
        new ProdutoFisico(3, 'Preço inválido', -1.00, 1, 1.0, 1.0, 1.0, 1.0);
    },
    '3 - Criar produto com preço negativo',
    'O preço do produto não pode ser negativo.'
);

$verificarExcecao(
    PedidoInvalidoException::class,
    static function () use ($servicoTeste, $pedidoSemItens): void {
        $servicoTeste->processarPedido($pedidoSemItens, new PagamentoPix());
    },
    '4 - Processar pedido sem produtos',
    'O pedido precisa ter pelo menos um produto.'
);

$verificarExcecao(
    PedidoInvalidoException::class,
    static function () use ($produtoTeste, $servicoTeste, $criarClienteTeste): void {
        $pedido = new Pedido(2004, $criarClienteTeste());
        $pedido->adicionarItem($produtoTeste, 1);
        $pagamento = new PagamentoPix();
        $servicoTeste->processarPedido($pedido, $pagamento);
        if ($produtoTeste->getEstoque() !== 0 || $pedido->getStatus() !== Pedido::STATUS_PAGO) {
            throw new RuntimeException('O pagamento deveria confirmar e reduzir o estoque antes da conclusão.');
        }
        $servicoTeste->finalizarPedido($pedido);
        $pedido->adicionarItem($produtoTeste, 1);
    },
    '5 - Adicionar produto após pedido concluído',
    'Não é permitido alterar um pedido concluído.'
);

$verificarExcecao(
    PedidoInvalidoException::class,
    static function () use ($servicoTeste, $criarClienteTeste): void {
        $pedido = new Pedido(2005, $criarClienteTeste());
        $pedido->alterarStatus(Pedido::STATUS_CANCELADO);
        $servicoTeste->processarPedido($pedido, new PagamentoPix());
    },
    '6 - Pagar pedido cancelado',
    'Não é possível pagar um pedido cancelado.'
);

$totalTestes = count($resultadosTestes);
$resultadosTestes[] = "Resultado final: {$testesAprovados}/{$totalTestes} testes aprovados.";

echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedido e testes</title>
</head>
<body>
    <h1>Resumo do pedido</h1>
    <pre>' . htmlspecialchars($saida, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>
    <h2>Resultados dos testes</h2>
    <pre>' . htmlspecialchars(implode(PHP_EOL, $resultadosTestes), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>
</body>
</html>';