<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Manager\DeltaConfigurableExportManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

/**
 * Write configurable in Magento.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeltaConfigurableWriter extends ProductWriter
{
    /** @var DeltaConfigurableExportManager */
    protected $configExportManager;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param ChannelManager                      $channelManager
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     * @param DeltaConfigurableExportManager      $configExportManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        ChannelManager $channelManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        DeltaConfigurableExportManager $configExportManager
    ) {
        parent::__construct($webserviceGuesser, $channelManager, $clientParametersRegistry);

        $this->configExportManager = $configExportManager;
    }

    /**
     * Compute an individual product and all its parts (translations).
     *
     * @param array $product
     */
    protected function computeProduct($product)
    {
        $sku = $this->getProductSku($product);

        parent::computeProduct($product);

        $sku = substr($sku, 5); // due to "conf-" prefix for configurables
        $channel = $this->channelManager->getChannelByCode($this->getChannel());
        $this->configExportManager->setLastExportDate($channel, $this->getJobInstance(), $sku);
    }

    /**
     * @return \Akeneo\Bundle\BatchBundle\Entity\JobInstance
     */
    protected function getJobInstance()
    {
        return $this->stepExecution->getJobExecution()->getJobInstance();
    }
}
