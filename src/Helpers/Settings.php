<?php

namespace Dnetix\Redirection\Helpers;

use Dnetix\Redirection\Carrier\Authentication;
use Dnetix\Redirection\Carrier\RestCarrier;
use Dnetix\Redirection\Carrier\SoapCarrier;
use Dnetix\Redirection\Contracts\Carrier;
use Dnetix\Redirection\Contracts\Entity;
use Dnetix\Redirection\Exceptions\PlacetoPayException;
use GuzzleHttp\Client;

class Settings extends Entity
{
    public const TP_REST = 'rest';
    public const TP_SOAP = 'soap';

    /**
     * @var string|mixed
     */
    protected $type = self::TP_REST;

    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var int
     */
    protected $timeout = 15;

    /**
     * @var bool
     */
    protected $verifySsl = true;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $tranKey;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $authAdditional = [];

    /**
     * @var Logger|null
     */
    protected $logger = null;

    /**
     * @var Client|null
     */
    protected $client = null;

    /**
     * @var Carrier|null
     */
    protected $carrier = null;

    public function __construct(array $data)
    {
        if (!isset($data['login']) || !isset($data['tranKey'])) {
            throw PlacetoPayException::forDataNotProvided('No login or tranKey provided on gateway');
        }

        if (!isset($data['baseUrl']) || !filter_var($data['baseUrl'], FILTER_VALIDATE_URL)) {
            throw PlacetoPayException::forDataNotProvided('No service URL provided to use');
        }

        if (substr($data['baseUrl'], -1) != '/') {
            $data['baseUrl'] .= '/';
        }

        if (isset($data['type']) && in_array($data['type'], [self::TP_SOAP, self::TP_REST])) {
            $this->type = $data['type'];
        }

        $allowedKeys = [
            'baseUrl',
            'timeout',
            'verifySsl',
            'login',
            'tranKey',
            'headers',
            'client',
            'authAdditional',
        ];

        $this->load($data, $allowedKeys);
        $this->logger = new Logger($data['logger'] ?? null);
    }

    public function baseUrl(string $endpoint = ''): string
    {
        return $this->baseUrl . $endpoint;
    }

    public function wsdl(): string
    {
        return $this->baseUrl('soap/redirect?wsdl');
    }

    public function location(): ?string
    {
        return $this->baseUrl('soap/redirect');
    }

    public function timeout(): int
    {
        return $this->timeout;
    }

    public function verifySsl(): bool
    {
        return $this->verifySsl;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function login(): string
    {
        return $this->login;
    }

    public function tranKey(): string
    {
        return $this->tranKey;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function client(): Client
    {
        if (!$this->client) {
            $this->client = new Client([
                'timeout' => $this->timeout(),
                'connect_timeout' => $this->timeout(),
                'verify' => $this->verifySsl(),
            ]);
        }
        return $this->client;
    }

    public function logger(): Logger
    {
        return $this->logger;
    }

    public function toArray(): array
    {
        return [];
    }

    public function carrier(): Carrier
    {
        if ($this->carrier instanceof Carrier) {
            return $this->carrier;
        }

        if ($this->type() == self::TP_SOAP) {
            $this->carrier = new SoapCarrier($this);
        } else {
            $this->carrier = new RestCarrier($this);
        }

        return $this->carrier;
    }

    public function authentication(): Authentication
    {
        return new Authentication([
            'login' => $this->login(),
            'tranKey' => $this->tranKey(),
            'authAdditional' => $this->authAdditional,
        ]);
    }
}
