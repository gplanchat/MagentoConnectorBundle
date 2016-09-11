<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner\ORM;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\MagentoConnectorBundle\Cleaner\AbstractProductCleaner;

/**
 * Magento product cleaner for ORM.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCleaner extends AbstractProductCleaner
{
    /**
     * {@inheritdoc}
     */
    protected function getExportedProductsSkus()
    {
        $qb = $this->productManager->getProductRepository()
            ->buildByChannelAndCompleteness($this->getChannelByCode())
            ->select('Value.varchar as sku')
            ->andWhere('Attribute.attributeType = :identifier_type')
            ->setParameter(':identifier_type', 'pim_catalog_identifier');

        return $this->getProductsSkus($qb);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPimProductsSkus()
    {
        $qb = $this->productManager->getProductRepository()->createQueryBuilder('p');
        $qb
            ->select('v.varchar as sku')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'a', Expr\Join::WITH, 'a.attributeType = :identifier_type')
            ->andWhere(
                $qb->expr()->eq('p.enabled', ':enabled')
            )
            ->setParameter(':identifier_type', 'pim_catalog_identifier')
            ->setParameter('enabled', true);

        return $this->getProductsSkus($qb);
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductsSkus(QueryBuilder $qb)
    {
        $results = $qb->getQuery()
            ->setHydrationMode(Query::HYDRATE_ARRAY)
            ->getResult();

        $skus = [];
        foreach ($results as $result) {
            $skus[] = (string) reset($result);
        };

        return $skus;
    }
}
