<?php

namespace App\Models;

use InvalidArgumentException;

class ProdutoDigital extends Produto
{
    private float $tamanhoArquivo;
    private string $formato;
    private string $linkDownload;

    public function __construct(
        int $codigo,
        string $nome,
        float $preco,
        int $estoque,
        float $tamanhoArquivo,
        string $formato,
        string $linkDownload
    ) {
        parent::__construct($codigo, $nome, $preco, $estoque);

        if ($tamanhoArquivo <= 0 || trim($formato) === '' || trim($linkDownload) === '') {
            throw new InvalidArgumentException('Tamanho, formato e link do arquivo digital são obrigatórios.');
        }

        $this->tamanhoArquivo = $tamanhoArquivo;
        $this->formato = trim($formato);
        $this->linkDownload = trim($linkDownload);
    }

    public function getTamanhoArquivo(): float
    {
        return $this->tamanhoArquivo;
    }

    public function getFormato(): string
    {
        return $this->formato;
    }

    public function getLinkDownload(): string
    {
        return $this->linkDownload;
    }

    public function calcularPrecoFinal(): float
    {
        return $this->getPreco();
    }
}
