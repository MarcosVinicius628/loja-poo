<?php

namespace App\Models;

use InvalidArgumentException;

class Cliente
{
    private int $codigo;
    private string $nome;
    private string $cpf;
    private string $email;
    private string $telefone;
    private array $enderecos = [];

    public function __construct(
        int $codigo,
        string $nome,
        string $cpf,
        string $email,
        string $telefone,
        Endereco $endereco
    ) {
        if ($codigo <= 0 || trim($nome) === '' || trim($cpf) === '') {
            throw new InvalidArgumentException('Código, nome e CPF do cliente são obrigatórios.');
        }
        $this->validarEmail($email);

        $this->codigo = $codigo;
        $this->nome = trim($nome);
        $this->cpf = trim($cpf);
        $this->email = trim($email);
        $this->telefone = trim($telefone);
        $this->enderecos[] = $endereco;
    }

    public function getCodigo(): int
    {
        return $this->codigo;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTelefone(): string
    {
        return $this->telefone;
    }

    public function getEnderecos(): array
    {
        return $this->enderecos;
    }

    public function alterarNome(string $nome): void
    {
        if (trim($nome) === '') {
            throw new InvalidArgumentException('O nome do cliente não pode ser vazio.');
        }

        $this->nome = trim($nome);
    }

    public function alterarEmail(string $email): void
    {
        $this->validarEmail($email);
        $this->email = trim($email);
    }

    public function alterarTelefone(string $telefone): void
    {
        $this->telefone = trim($telefone);
    }

    public function adicionarEndereco(Endereco $endereco): void
    {
        $this->enderecos[] = $endereco;
    }

    private function validarEmail(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('E-mail inválido.');
        }
    }
}
