<?php

namespace App\Interfaces;

interface PagamentoInterface
{
    public function pagar(float $valor): void;

    public function getNome(): string;

    public function getConfirmacao(): string;
}
