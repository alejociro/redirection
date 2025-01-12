<?php

namespace Dnetix\Redirection\Entities;

use Dnetix\Redirection\Contracts\Entity;

class AmountDetail extends Entity
{
    /**
     * @var string
     */
    protected $kind;

    /**
     * @var float
     */
    protected $amount;

    public function __construct($data = [])
    {
        $this->load($data, ['kind', 'amount']);
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'kind' => $this->kind(),
            'amount' => $this->amount(),
        ]);
    }
}
