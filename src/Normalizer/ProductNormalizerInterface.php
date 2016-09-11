<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;

/**
 * Defines the interface of a product normalizers.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductNormalizerInterface
{
    /**
     * Get values array for a given product.
     *
     * @param ProductInterface  $product                  The given product
     * @param array             $magentoAttributes        Attribute list from Magento
     * @param array             $magentoAttributesOptions Attribute options list from Magento
     * @param string            $localeCode               The locale to apply
     * @param string            $scopeCode                The akeno scope
     * @param MappingCollection $categoryMapping          Root category mapping
     * @param MappingCollection $attributeMapping         Attribute mapping
     * @param boolean           $onlyLocalized            If true, only get translatable attributes
     * @param string            $pimGrouped               Pim grouped association code
     *
     * @return array Computed data
     */
    public function getValues(
        ProductInterface $product,
        $magentoAttributes,
        $magentoAttributesOptions,
        $localeCode,
        $scopeCode,
        MappingCollection $categoryMapping,
        MappingCollection $attributeMapping,
        $onlyLocalized,
        $pimGrouped
    );

    /**
     * Get all images of a product normalized.
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getNormalizedImages(ProductInterface $product);
}
