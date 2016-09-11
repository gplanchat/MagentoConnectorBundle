<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice16;
use Pim\Bundle\MagentoConnectorBundle\Webservice\WebserviceEE;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientFactory;

/**
 * A magento soap client to abstract interaction with the magento api.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebserviceGuesser extends AbstractGuesser
{
    /** @var Webservice */
    protected $webservice;

    /** @var MagentoSoapClientFactory */
    protected $magentoSoapClientFactory;

    /**
     * @param MagentoSoapClientFactory $magentoSoapClientFactory
     */
    public function __construct(MagentoSoapClientFactory $magentoSoapClientFactory)
    {
        $this->magentoSoapClientFactory = $magentoSoapClientFactory;
    }

    /**
     * Get the Webservice corresponding to the given Magento parameters.
     *
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @throws NotSupportedVersionException If the magento version is not supported
     *
     * @return Webservice
     */
    public function getWebservice(MagentoSoapClientParameters $clientParameters)
    {
        if (!$this->webservice) {
            $client         = $this->magentoSoapClientFactory->getMagentoSoapClient($clientParameters);
            $magentoVersion = $this->getMagentoVersion($client);

            switch ($magentoVersion) {
                case AbstractGuesser::MAGENTO_VERSION_1_14:
                case AbstractGuesser::MAGENTO_VERSION_1_13:
                case AbstractGuesser::MAGENTO_VERSION_1_12:
                case AbstractGuesser::MAGENTO_VERSION_1_11:
                    $this->webservice = new WebserviceEE($client);
                    break;
                case AbstractGuesser::UNKNOWN_VERSION:
                case AbstractGuesser::MAGENTO_VERSION_1_9:
                case AbstractGuesser::MAGENTO_VERSION_1_8:
                case AbstractGuesser::MAGENTO_VERSION_1_7:
                    $this->webservice = new Webservice($client);
                    break;
                case AbstractGuesser::MAGENTO_VERSION_1_6:
                    $this->webservice = new Webservice16($client);
                    break;
                default:
                    throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
            }
        }

        return $this->webservice;
    }
}
