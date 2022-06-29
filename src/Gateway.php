<?php

namespace Omnipay\PayU;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\PayU\Messages\CompletePurchaseRequest;
use Omnipay\PayU\Messages\CompletePurchaseResponse;
use Omnipay\PayU\Messages\Notification;
use Omnipay\PayU\Messages\PurchaseRequest;
use Omnipay\PayU\Messages\PurchaseResponse;
use OpenPayU_Configuration;
use OpenPayU_Exception;
use OpenPayU_Exception_Configuration;

class Gateway extends AbstractGateway
{
    const ENV_TEST = 'sandbox';
    const ENV_PRODUCTION = 'secure';

    /**
     * Get gateway display name
     */
    public function getName(): string
    {
        return 'PayU';
    }

    /**
     * @param array $parameters
     * @return $this|Gateway
     * @throws OpenPayU_Exception_Configuration
     */
    public function initialize(array $parameters = []): self
    {
        parent::initialize($parameters);

        OpenPayU_Configuration::setEnvironment($this->getEnvironment());
        OpenPayU_Configuration::setOauthGrantType('client_credentials');
        OpenPayU_Configuration::setMerchantPosId($this->getParameter('posId'));
        OpenPayU_Configuration::setSignatureKey($this->getParameter('secondKey'));
        OpenPayU_Configuration::setOauthClientId($this->getParameter('posAuthKey'));
        OpenPayU_Configuration::setOauthClientSecret($this->getParameter('clientSecret'));

        return $this;
    }

    /**
     * @param array $options
     * @throws OpenPayU_Exception
     */
    public function purchase(array $options = []): AbstractRequest
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    /**
     * @param array $options
     * @throws OpenPayU_Exception
     */
    public function completePurchase(array $options = []): AbstractRequest
    {
        return $this->createRequest(CompletePurchaseRequest::class, $options);
    }

    public function acceptNotification(): Notification
    {
        return new Notification($this->httpRequest, $this->httpClient, $this->getParameter('secondKey'));
    }

    /** @return array<string, mixed> */
    public function getDefaultParameters(): array
    {
        return [
            'posId'        => '',
            'secondKey'    => '',
            'clientSecret' => '',
            'testMode'     => true,
            'posAuthKey'   => null,
        ];
    }

    /**
     * @param string $secondKey
     */
    public function setSecondKey(string $secondKey): void
    {
        $this->setParameter('secondKey', $secondKey);
    }

    /**
     * @param string $posId
     */
    public function setPosId(string $posId): void
    {
        $this->setParameter('posId', $posId);
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->setParameter('clientSecret', $clientSecret);
    }

    /**
     * @param string|null $posAuthKey
     */
    public function setPosAuthKey(string $posAuthKey = null): void
    {
        $this->setParameter('posAuthKey', $posAuthKey);
    }

    private function getEnvironment(): string
    {
        if ($this->getTestMode()) {
            return self::ENV_TEST;
        }

        return self::ENV_PRODUCTION;
    }
}
