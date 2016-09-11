<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientParametersRegistry
{
    /**
     * Array of all MagentoSoapClientParameters instances.
     *
     * @var array
     */
    protected $instances;

    /**
     * Gives MagentoSoapClientParameters which corresponding to given parameters.
     *
     * @param array $soapParameters Associative array which contains soap parameters
     *
     * @return MagentoSoapClientParameters
     */
    public function getInstance(
        $soapUsername,
        $soapApiKey,
        $magentoUrl,
        $wsdlUrl,
        $defaultStoreView = Webservice::SOAP_DEFAULT_STORE_VIEW,
        $httpLogin = null,
        $httpPassword = null
    ) {
        $hash = md5(
            $soapUsername.
            $soapApiKey.
            $magentoUrl.
            $wsdlUrl.
            $defaultStoreView.
            $httpLogin.
            $httpPassword
        );

        if (!isset($this->instances[$hash])) {
            $this->instances[$hash] = new MagentoSoapClientParameters(
                $soapUsername,
                $soapApiKey,
                $magentoUrl,
                $wsdlUrl,
                $defaultStoreView,
                $httpLogin,
                $httpPassword
            );
        }

        return $this->instances[$hash];
    }
}
