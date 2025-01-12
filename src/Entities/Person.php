<?php

namespace Dnetix\Redirection\Entities;

use Dnetix\Redirection\Contracts\Entity;
use Dnetix\Redirection\Helpers\DocumentHelper;

class Person extends Entity
{
    /**
     * @var string
     */
    protected $document = '';

    /**
     * @var string
     */
    protected $documentType = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $surname = '';

    /**
     * @var string
     */
    protected $company = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $mobile = '';

    /**
     * @var Address|null
     */
    protected $address = null;

    public function __construct($data = [])
    {
        $this->load($data, ['document', 'documentType', 'name', 'surname', 'company', 'email', 'mobile']);
        $this->loadEntity($data['address'] ?? null, 'address', Address::class);
    }

    public function document(): string
    {
        return $this->document;
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function surname(): string
    {
        return $this->surname;
    }

    public function company(): string
    {
        return $this->company;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function address(): ?Address
    {
        return $this->address;
    }

    public function mobile(): string
    {
        return $this->mobile;
    }

    public function isBusiness(): bool
    {
        return $this->documentType() && DocumentHelper::businessDocument($this->documentType());
    }

    public function toArray(): array
    {
        return $this->arrayFilter([
            'document' => $this->document(),
            'documentType' => $this->documentType(),
            'name' => $this->name(),
            'surname' => $this->surname(),
            'email' => $this->email(),
            'mobile' => $this->mobile(),
            'company' => $this->company(),
            'address' => $this->address() ? $this->address()->toArray() : null,
        ]);
    }
}
