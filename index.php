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

echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedido</title>
</head>
<body>
    <pre>' . htmlspecialchars($saida, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>
</body>
</html>';