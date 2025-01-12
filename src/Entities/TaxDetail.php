<?php

namespace Dnetix\Redirection\Entities;

use Dnetix\Redirection\Contracts\Entity;

class TaxDetail extends Entity
{
    /**
     * @var string
     */
    protected $kind;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var float|null
     */
    protected $base = null;

    public function __construct($data = [])
    {
        $this->load($data, ['kind', 'amount', 'base']);
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function base(): ?float
    {
        return $this->base;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'kind' => $this->kind(),
            'amount' => $this->amount(),
            'base' => $this->base(),
        ]);
    }
}
