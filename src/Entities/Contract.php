<?php

namespace Musonza\LaravelDynamodbChat\Entities;

use Bego\Component\Resultset;

interface Contract
{
    public function getPrimaryKey(): array;
    public function toItem(): array;
    public function getPK(): string;
    public function getSK(): string;
}