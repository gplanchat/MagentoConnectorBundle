<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * A magento guesser abstract class.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractGuesser
{
    /** @staticvar string */
    const MAGENTO_VERSION_1_14 = '1.14';

    /** @staticvar string */
    const MAGENTO_VERSION_1_13 = '1.13';

    /** @staticvar string */
    const MAGENTO_VERSION_1_12 = '1.12';

    /** @staticvar string */
    const MAGENTO_VERSION_1_11 = '1.11';

    /** @staticvar string */
    const MAGENTO_VERSION_1_9  = '1.9';

    /** @staticvar string */
    const MAGENTO_VERSION_1_8  = '1.8';

    /** @staticvar string */
    const MAGENTO_VERSION_1_7  = '1.7';

    /** @staticvar string */
    const MAGENTO_VERSION_1_6  = '1.6';

    /** @staticvar string */
    const MAGENTO_CORE_ACCESS_DENIED = 'Access denied.';

    /** @staticvar string */
    const UNKNOWN_VERSION = 'unknown_version';

    /** @staticvar string */
    const MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE = 'Your Magento version is not supported yet.';

    /** @var string */
    protected $version = null;

    /**
     * Get the Magento version for the given client.
     *
     * @param MagentoSoapClient $client
     *
     * @return float
     */
    protected function getMagentoVersion(MagentoSoapClient $client = null)
    {
        if (null === $client) {
            return null;
        }

        if (!$this->version) {
            try {
                $magentoVersion = $client->call('core_magento.info')['magento_version'];
            } catch (\SoapFault $e) {
                return self::MAGENTO_VERSION_1_6;
            } catch (SoapCallException $e) {
                throw $e;
            }

            $pattern = '/^(?P<version>[0-9]+\.[0-9]+)(\.[0-9])*/';

            if (preg_match($pattern, $magentoVersion, $matches)) {
                $this->version = $matches['version'];
            } else {
                $this->version = $magentoVersion;
            }
        }

        return $this->version;
    }
}
