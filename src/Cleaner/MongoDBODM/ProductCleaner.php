<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\MagentoConnectorBundle\Cleaner\AbstractProductCleaner;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

/**
 * Magento product cleaner for MongoDB.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCleaner extends AbstractProductCleaner
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     * @param ChannelManager                      $channelManager
     * @param ProductManager                      $productManager
     * @param AttributeRepository                 $attributeRepository
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        ChannelManager $channelManager,
        ProductManager $productManager,
        AttributeRepository $attributeRepository
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry, $channelManager, $productManager);

        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExportedProductsSkus()
    {
        $identifierCode = $this->getIdentifierAttributeCode();

        $qb = $this->productManager->getProductRepository()
            ->buildByChannelAndCompleteness($this->getChannelByCode())
            ->select([sprintf("normalizedData.%s", $identifierCode)]);

        return $this->getProductsSkus($qb, $identifierCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPimProductsSkus()
    {
        $identifierCode = $this->getIdentifierAttributeCode();
        $qb = $this->productManager->getProductRepository()->createQueryBuilder('p');

        $qb
            ->addAnd($qb->expr()->field('enabled')->equals(true))
            ->select([sprintf("normalizedData.%s", $identifierCode)]);

        return $this->getProductsSkus($qb, $identifierCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductsSkus(Builder $qb, $identifierCode)
    {
        $results = $qb->hydrate(false)->getQuery()->execute()->toArray();
        $skus = [];
        foreach ($results as $result) {
            $skus[] = $result['normalizedData'][$identifierCode];
        }

        return $skus;
    }

    /**
     * Get the identifier attribute code.
     *
     * @return string
     */
    protected function getIdentifierAttributeCode()
    {
        return $this->attributeRepository->getIdentifierCode();
    }
}
