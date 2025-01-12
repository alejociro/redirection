<?php

namespace Dnetix\Redirection\Carrier;

use Dnetix\Redirection\Exceptions\PlacetoPayException;
use SoapHeader;
use SoapVar;
use stdClass;

/**
 * Class Authentication
 * Generates the needed authentication elements.
 */
class Authentication
{
    public const WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    public const WSSE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

    /**
     * @var string|mixed
     */
    private $login;

    /**
     * @var string|mixed
     */
    private $tranKey;

    /**
     * @var array|mixed
     */
    private $auth = [];

    /**
     * @var array|mixed
     */
    private $additional = [];

    /**
     * @var bool
     */
    private $overridden = false;

    /**
     * @var string|mixed
     */
    private $algorithm = 'sha1';

    /**
     * @throws PlacetoPayException
     */
    public function __construct(array $config)
    {
        if (!isset($config['login']) || !isset($config['tranKey'])) {
            throw PlacetoPayException::forDataNotProvided('No login or tranKey provided on authentication');
        }

        $this->login = $config['login'];
        $this->tranKey = $config['tranKey'];

        if (isset($config['auth'])) {
            $this->auth = $config['auth'];
            $this->overridden = true;
        }

        $this->algorithm = $config['algorithm'] ?? 'sha1';
        $this->additional = $config['authAdditional'] ?? [];

        $this->generate();
    }

    public function getNonce($encoded = true)
    {
        if ($this->auth) {
            $nonce = $this->auth['nonce'];
        } else {
            if (function_exists('openssl_random_pseudo_bytes')) {
                $nonce = bin2hex(openssl_random_pseudo_bytes(16));
            } elseif (function_exists('random_bytes')) {
                $nonce = bin2hex(random_bytes(16));
            } else {
                $nonce = mt_rand();
            }
        }

        if ($encoded) {
            return base64_encode($nonce);
        }

        return $nonce;
    }

    public function getSeed(): string
    {
        if ($this->auth) {
            return $this->auth['seed'];
        }

        return date('c');
    }

    public function digest($encoded = true): string
    {
        $digest = hash($this->algorithm, $this->getNonce(false) . $this->getSeed() . $this->tranKey(), true);

        if ($encoded) {
            return base64_encode($digest);
        }

        return $digest;
    }

    public function login(): string
    {
        return $this->login;
    }

    public function tranKey(): string
    {
        return $this->tranKey;
    }

    public function additional(): array
    {
        return $this->additional;
    }

    public function generate(): self
    {
        if (!$this->overridden) {
            $this->auth = [
                'seed' => $this->getSeed(),
                'nonce' => $this->getNonce(),
            ];
        }

        return $this;
    }

    /**
     * Parses the entity as a SOAP Header.
     * @return SoapHeader
     */
    public function asSoapHeader(): SoapHeader
    {
        $UsernameToken = new stdClass();
        $UsernameToken->Username = new SoapVar($this->login(), XSD_STRING, null, self::WSSE, null, self::WSSE);
        $UsernameToken->Password = new SoapVar($this->digest(), XSD_STRING, 'PasswordDigest', null, 'Password', self::WSSE);
        $UsernameToken->Nonce = new SoapVar($this->getNonce(), XSD_STRING, null, self::WSSE, null, self::WSSE);
        $UsernameToken->Created = new SoapVar($this->getSeed(), XSD_STRING, null, self::WSU, null, self::WSU);

        $security = new stdClass();
        $security->UsernameToken = new SoapVar($UsernameToken, SOAP_ENC_OBJECT, null, self::WSSE, 'UsernameToken', self::WSSE);

        return new SoapHeader(self::WSSE, 'Security', $security, true);
    }

    public function asArray(): array
    {
        return [
            'login' => $this->login(),
            'tranKey' => $this->digest(),
            'nonce' => $this->getNonce(),
            'seed' => $this->getSeed(),
            'additional' => $this->additional(),
        ];
    }
}
