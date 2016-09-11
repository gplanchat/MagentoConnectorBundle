<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Manager\DeltaProductExportManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

/**
 * Write delta product in Magento.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeltaProductWriter extends ProductWriter
{
    /** @var DeltaProductExportManager */
    protected $productExportManager;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param ChannelManager                      $channelManager
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     * @param DeltaProductExportManager           $productExportManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        ChannelManager $channelManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        DeltaProductExportManager $productExportManager
    ) {
        parent::__construct($webserviceGuesser, $channelManager, $clientParametersRegistry);

        $this->productExportManager = $productExportManager;
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

        $this->productExportManager->updateProductExport($this->getJobInstance(), $sku);
    }

    /**
     * @return \Akeneo\Bundle\BatchBundle\Entity\JobInstance
     */
    protected function getJobInstance()
    {
        return $this->stepExecution->getJobExecution()->getJobInstance();
    }
}
