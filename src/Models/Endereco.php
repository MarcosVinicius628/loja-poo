<?php

namespace App\Models;

use InvalidArgumentException;

class Endereco
{
    private string $logradouro;
    private string $numero;
    private string $complemento;
    private string $bairro;
    private string $cidade;
    private string $estado;
    private string $cep;

    public function __construct(
        string $logradouro,
        string $numero,
        string $complemento,
        string $bairro,
        string $cidade,
        string $estado,
        string $cep
    ) {
        $camposObrigatorios = [$logradouro, $numero, $bairro, $cidade, $estado, $cep];
        foreach ($camposObrigatorios as $campo) {
            if (trim($campo) === '') {
                throw new InvalidArgumentException('Os campos principais do endereço são obrigatórios.');
            }
        }

        $this->logradouro = trim($logradouro);
        $this->numero = trim($numero);
        $this->complemento = trim($complemento);
        $this->bairro = trim($bairro);
        $this->cidade = trim($cidade);
        $this->estado = trim($estado);
        $this->cep = trim($cep);
    }

    public function getLogradouro(): string
    {
        return $this->logradouro;
    }

    public function getNumero(): string
    {
        return $this->numero;
    }

    public function getComplemento(): string
    {
        return $this->complemento;
    }

    public function getBairro(): string
    {
        return $this->bairro;
    }

    public function getCidade(): string
    {
        return $this->cidade;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getCep(): string
    {
        return $this->cep;
    }
}
