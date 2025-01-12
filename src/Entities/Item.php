<?php

namespace Dnetix\Redirection\Entities;

use Dnetix\Redirection\Contracts\Entity;

class Item extends Entity
{
    /**
     * @var string
     */
    protected $sku = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $category = '';

    /**
     * @var string
     */
    protected $qty = '';

    /**
     * @var string
     */
    protected $price = '';

    /**
     * @var string
     */
    protected $tax = '';

    public function __construct($data = [])
    {
        $this->load($data, ['sku', 'name', 'category', 'qty', 'price', 'tax']);
    }

    public function sku(): string
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function qty(): string
    {
        return $this->qty;
    }

    public function price(): string
    {
        return $this->price;
    }

    public function tax(): string
    {
        return $this->tax;
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'sku' => $this->sku(),
            'name' => $this->name(),
            'category' => $this->category(),
            'qty' => $this->qty(),
            'price' => $this->price(),
            'tax' => $this->tax(),
        ]);
    }
}
