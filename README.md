# Loja POO

## Objetivo

Aplicação didática em PHP para demonstrar programação orientada a objetos em um fluxo de loja: catálogo de produtos, composição de pedidos, pagamentos polimórficos, controle de estoque e tratamento de erros de negócio.

O fluxo de pagamento é simulado. As classes de pagamento confirmam a operação localmente; não há integração com banco, adquirente ou serviço externo.

## Estrutura

```text
loja-poo/
├── autoload.php
├── index.php
├── diagrama-classes.puml
├── README.md
├── src/
│   ├── Exceptions/
│   ├── Interfaces/
│   ├── Models/
│   ├── Pagamentos/
│   └── Services/
└── tests/
    └── testes.php
```

## Classes implementadas

- `App\Models\Produto`: classe abstrata; protege código, nome, preço e estoque e declara `calcularPrecoFinal()`.
- `ProdutoFisico`: herda de `Produto`; inclui dimensões e calcula frete a R$ 10,00 por kg.
- `ProdutoDigital`: herda de `Produto`; inclui tamanho, formato e link, sem cobrança de frete.
- `Cliente` e `Endereco`: o cliente recebe pelo menos um endereço na criação e pode possuir outros. O CPF é privado, só tem acesso de leitura e não tem método de alteração.
- `ItemPedido`: captura o preço unitário no momento da inclusão e calcula o subtotal; não aceita subtotal informado.
- `Pedido`: mantém os itens, calcula subtotal, frete e total e controla transições válidas de status.
- `PagamentoInterface`, `PagamentoPix`, `PagamentoCartao` e `PagamentoBoleto`: pagamentos intercambiáveis com confirmações próprias. O cartão aceita de 1 a 12 parcelas.
- `PedidoService`: valida pedido, confere estoque, solicita pagamento, atualiza o estoque após a confirmação e conclui pedidos pagos/enviados.
- Exceções de domínio: `EstoqueInsuficienteException`, `PedidoInvalidoException` e `PagamentoException`.

## Conceitos de POO

- **Encapsulamento:** os dados são privados e acessados por getters e operações validadas. Preço e estoque só mudam por métodos que protegem as regras; CPF não pode ser alterado.
- **Herança:** `ProdutoFisico` e `ProdutoDigital` herdam de `Produto`.
- **Abstração:** `Produto` representa características comuns e exige que cada tipo implemente o cálculo do preço final.
- **Polimorfismo:** `PedidoService` recebe `PagamentoInterface` e chama `pagar()` sem depender de PIX, cartão ou boleto.
- **Composição:** `Cliente` é criado com pelo menos um `Endereco`; `Pedido` contém `ItemPedido`; cada item referencia um `Produto`.
- **Interface:** `PagamentoInterface` define o contrato comum para processar e identificar pagamentos.

## Regras de negócio

1. Preços, quantidades e estoques não podem ser negativos; quantidade de item deve ser maior que zero.
2. A quantidade total pedida de cada produto não pode exceder o estoque.
3. Um pedido sem itens não pode ser processado.
4. Pedidos cancelados não podem ser pagos; pedidos concluídos ou cancelados não aceitam inclusão/remoção de itens.
5. A conclusão só é permitida depois do pagamento (ou após o envio).
6. O serviço só remove estoque depois que `pagar()` retorna sem erro.
7. Frete físico é R$ 10,00 por kg por unidade. Produto digital não gera frete.
8. A transição para pagamento aguardando pode voltar a aberto se o pagamento lançar `PagamentoException`.

## Execução

Requer PHP 7.4 ou superior (tipagem de propriedades e tipos de retorno).

No terminal, na pasta do projeto:

```bash
php index.php
```

O cenário de demonstração usa o cliente Carlos Silva, dois produtos, pagamento via PIX simulado e pedido nº 1001. A saída inclui:

```text
Frete: R$ 24,00
Valor Total: R$ 923,90
Forma de pagamento: PIX
Status: Pago
Pagamento PIX confirmado: R$ 923,90.
```

O frete é R$ 12,00 por teclado (1,2 kg × R$ 10,00/kg), totalizando R$ 24,00 para duas unidades. Produtos digitais não entram no cálculo.

## Testes obrigatórios

Execute o verificador sem dependências externas:

```bash
php tests/testes.php
```

Ele cobre os seis cenários exigidos: estoque insuficiente, quantidade zero, preço negativo, pedido vazio, alteração de pedido cancelado (a mesma proteção vale para concluído) e tentativa de pagar pedido cancelado. Cada cenário exibe uma mensagem de sucesso com o erro esperado e sua mensagem; se um teste falhar, exibe a causa da falha. Ao final, mostra o total de testes aprovados. As mensagens aparecem em linhas separadas tanto no terminal quanto no navegador.

## Diagrama UML

O arquivo contém o diagrama PlantUML, com herança, composição, associações e implementação da interface.
