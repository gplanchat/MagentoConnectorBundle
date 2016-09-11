<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * Magento soap client parameters.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientParameters
{
    /** @staticvar string */
    const SOAP_WSDL_URL = '/api/soap/?wsdl';

    /** @var string */
    protected $soapUsername;

    /** @var string */
    protected $soapApiKey;

    /** @var string */
    protected $wsdlUrl;

    /** @var string Magento Url (only the domain) */
    protected $magentoUrl;

    /** @var string */
    protected $defaultStoreView;

    /** @var string */
    protected $httpLogin;

    /** @var string */
    protected $httpPassword;

    /** @var boolean Are parameters valid or not ? */
    protected $isValid;

    /**
     * @param string $soapUsername     Magento soap username
     * @param string $soapApiKey       Magento soap api key
     * @param string $magentoUrl       Magento url (only the domain)
     * @param string $wsdlUrl          Only wsdl soap api extension
     * @param string $defaultStoreView Default store view
     * @param string $httpLogin        Login http authentication
     * @param string $httpPassword     Password http authentication
     */
    public function __construct(
        $soapUsername,
        $soapApiKey,
        $magentoUrl,
        $wsdlUrl,
        $defaultStoreView,
        $httpLogin = null,
        $httpPassword = null
    ) {
        $this->soapUsername     = $soapUsername;
        $this->soapApiKey       = $soapApiKey;
        $this->magentoUrl       = $magentoUrl;
        $this->wsdlUrl          = $wsdlUrl;
        $this->defaultStoreView = $defaultStoreView;
        $this->httpLogin        = $httpLogin;
        $this->httpPassword     = $httpPassword;
    }

    /**
     * Get hash to uniquely identify parameters even in different instances.
     *
     * @return string
     */
    public function getHash()
    {
        return md5(
            $this->soapUsername.
            $this->soapApiKey.
            $this->magentoUrl.
            $this->wsdlUrl.
            $this->defaultStoreView.
            $this->httpLogin.
            $this->httpPassword
        );
    }

    /**
     * Are parameters valid or not ?
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @param boolean $state
     */
    public function setValidation($state)
    {
        $this->isValid = $state;
    }

    /**
     * @return string
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * @return string
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * Soap url is concatenation between magento url and wsdl url
     *
     * @return string
     */
    public function getSoapUrl()
    {
        return $this->magentoUrl.$this->wsdlUrl;
    }

    /**
     * @return string
     */
    public function getWsdlUrl()
    {
        return $this->wsdlUrl;
    }

    /**
     * Magento url is the domain name
     *
     * @return string
     */
    public function getMagentoUrl()
    {
        return $this->magentoUrl;
    }

    /**
     * @return string
     */
    public function getDefaultstoreView()
    {
        return $this->defaultStoreView;
    }

    /**
     * @return string
     */
    public function getHttpLogin()
    {
        return $this->httpLogin;
    }

    /**
     * @return string
     */
    public function getHttpPassword()
    {
        return $this->httpPassword;
    }
}
