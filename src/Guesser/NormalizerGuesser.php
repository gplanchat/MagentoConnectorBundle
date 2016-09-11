<?php

namespace Pim\Bundle\MagentoConnectorBundle\Guesser;

use Pim\Bundle\MagentoConnectorBundle\Filter\ExportableLocaleFilter;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer16;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ConfigurableNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientFactory;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizerInterface;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductValueNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\FamilyNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\OptionNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNormalizer;

/**
 * A magento guesser to get the proper normalizer.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizerGuesser extends AbstractGuesser
{
    /** @var MagentoSoapClientFactory */
    protected $magentoSoapClientFactory;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var MediaManager */
    protected $mediaManager;

    /** @var ProductValueNormalizer */
    protected $productValueNormalizer;

    /** @var AssociationTypeManager */
    protected $associationTypeManager;

    /** @var \Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNormalizer */
    protected $attributeNormalizer;

    /** @var \Pim\Bundle\MagentoConnectorBundle\Normalizer\CategoryNormalizer */
    protected $categoryNormalizer;

    /** @var \Pim\Bundle\MagentoConnectorBundle\Normalizer\FamilyNormalizer */
    protected $familyNormalizer;

    /** @var \Pim\Bundle\MagentoConnectorBundle\Normalizer\OptionNormalizer */
    protected $optionNormalizer;

    /** @var ExportableLocaleFilter */
    protected $localeFilter;

    /**
     * @param MagentoSoapClientFactory $magentoSoapClientFactory
     * @param ChannelManager           $channelManager
     * @param MediaManager             $mediaManager
     * @param ProductValueNormalizer   $productValueNormalizer
     * @param CategoryMappingManager   $categoryMappingManager
     * @param AssociationTypeManager   $associationTypeManager
     * @param CategoryNormalizer       $categoryNormalizer
     * @param FamilyNormalizer         $familyNormalizer
     * @param OptionNormalizer         $optionNormalizer
     * @param ExportableLocaleFilter   $localeFilter
     */
    public function __construct(
        MagentoSoapClientFactory $magentoSoapClientFactory,
        ChannelManager $channelManager,
        MediaManager $mediaManager,
        ProductValueNormalizer $productValueNormalizer,
        CategoryMappingManager $categoryMappingManager,
        AssociationTypeManager $associationTypeManager,
        AttributeNormalizer $attributeNormalizer,
        CategoryNormalizer $categoryNormalizer,
        FamilyNormalizer $familyNormalizer,
        OptionNormalizer $optionNormalizer,
        ExportableLocaleFilter $localeFilter
    ) {
        $this->magentoSoapClientFactory = $magentoSoapClientFactory;
        $this->channelManager           = $channelManager;
        $this->mediaManager             = $mediaManager;
        $this->productValueNormalizer   = $productValueNormalizer;
        $this->categoryMappingManager   = $categoryMappingManager;
        $this->associationTypeManager   = $associationTypeManager;
        $this->attributeNormalizer      = $attributeNormalizer;
        $this->categoryNormalizer       = $categoryNormalizer;
        $this->familyNormalizer         = $familyNormalizer;
        $this->optionNormalizer         = $optionNormalizer;
        $this->localeFilter             = $localeFilter;
    }

    /**
     * Get the product normalizer corresponding to the given Magento parameters.
     *
     * @param MagentoSoapClientParameters $clientParameters
     * @param boolean                     $enabled
     * @param boolean                     $visibility
     * @param boolean                     $variantMemberVisibility
     * @param string                      $currencyCode
     *
     * @throws NotSupportedVersionException If the magento version is not supported
     *
     * @return AbstractNormalizer
     */
    public function getProductNormalizer(
        MagentoSoapClientParameters $clientParameters,
        $enabled,
        $visibility,
        $variantMemberVisibility,
        $currencyCode
    ) {
        $client         = $this->magentoSoapClientFactory->getMagentoSoapClient($clientParameters);
        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_14:
            case AbstractGuesser::MAGENTO_VERSION_1_13:
            case AbstractGuesser::MAGENTO_VERSION_1_12:
            case AbstractGuesser::MAGENTO_VERSION_1_11:
            case AbstractGuesser::MAGENTO_VERSION_1_9:
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
                return new ProductNormalizer(
                    $this->channelManager,
                    $this->mediaManager,
                    $this->productValueNormalizer,
                    $this->categoryMappingManager,
                    $this->associationTypeManager,
                    $this->localeFilter,
                    $enabled,
                    $visibility,
                    $variantMemberVisibility,
                    $currencyCode,
                    $clientParameters->getSoapUrl()
                );
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return new ProductNormalizer16(
                    $this->channelManager,
                    $this->mediaManager,
                    $this->productValueNormalizer,
                    $this->categoryMappingManager,
                    $this->associationTypeManager,
                    $this->localeFilter,
                    $enabled,
                    $visibility,
                    $variantMemberVisibility,
                    $currencyCode,
                    $clientParameters->getSoapUrl()
                );
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the configurable normalizer corresponding to the given Magento parameters.
     *
     * @param MagentoSoapClientParameters $clientParameters
     * @param ProductNormalizerInterface  $productNormalizer
     * @param PriceMappingManager         $priceMappingManager
     * @param boolean                     $visibility
     *
     * @return ConfigurableNormalizer
     *
     * @throws NotSupportedVersionException
     */
    public function getConfigurableNormalizer(
        MagentoSoapClientParameters $clientParameters,
        ProductNormalizerInterface $productNormalizer,
        PriceMappingManager $priceMappingManager,
        $visibility
    ) {
        $client         = $this->magentoSoapClientFactory->getMagentoSoapClient($clientParameters);
        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_14:
            case AbstractGuesser::MAGENTO_VERSION_1_13:
            case AbstractGuesser::MAGENTO_VERSION_1_12:
            case AbstractGuesser::MAGENTO_VERSION_1_11:
            case AbstractGuesser::MAGENTO_VERSION_1_9:
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return new ConfigurableNormalizer(
                    $this->channelManager,
                    $productNormalizer,
                    $priceMappingManager,
                    $visibility
                );
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the Category normalizer corresponding to the given Magento parameters.
     *
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return AbstractNormalizer
     *
     * @throws NotSupportedVersionException
     */
    public function getCategoryNormalizer(MagentoSoapClientParameters $clientParameters)
    {
        $client         = $this->magentoSoapClientFactory->getMagentoSoapClient($clientParameters);
        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_14:
            case AbstractGuesser::MAGENTO_VERSION_1_13:
            case AbstractGuesser::MAGENTO_VERSION_1_12:
            case AbstractGuesser::MAGENTO_VERSION_1_11:
            case AbstractGuesser::MAGENTO_VERSION_1_9:
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return $this->categoryNormalizer;
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the option normalizer corresponding to the given Magento parameters.
     *
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return AbstractNormalizer
     *
     * @throws NotSupportedVersionException
     */
    public function getOptionNormalizer(MagentoSoapClientParameters $clientParameters)
    {
        $client         = $this->magentoSoapClientFactory->getMagentoSoapClient($clientParameters);
        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_14:
            case AbstractGuesser::MAGENTO_VERSION_1_13:
            case AbstractGuesser::MAGENTO_VERSION_1_12:
            case AbstractGuesser::MAGENTO_VERSION_1_11:
            case AbstractGuesser::MAGENTO_VERSION_1_9:
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return $this->optionNormalizer;
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the attribute normalizer corresponding to the given Magento parameters.
     *
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return AbstractNormalizer
     *
     * @throws NotSupportedVersionException
     */
    public function getAttributeNormalizer(MagentoSoapClientParameters $clientParameters)
    {
        $client         = $this->magentoSoapClientFactory->getMagentoSoapClient($clientParameters);
        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_14:
            case AbstractGuesser::MAGENTO_VERSION_1_13:
            case AbstractGuesser::MAGENTO_VERSION_1_12:
            case AbstractGuesser::MAGENTO_VERSION_1_11:
            case AbstractGuesser::MAGENTO_VERSION_1_9:
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return $this->attributeNormalizer;
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }

    /**
     * Get the family normalizer corresponding to the given Magento parameters.
     *
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @throws NotSupportedVersionException
     *
     * @return FamilyNormalizer
     */
    public function getFamilyNormalizer(MagentoSoapClientParameters $clientParameters)
    {
        $client         = $this->magentoSoapClientFactory->getMagentoSoapClient($clientParameters);
        $magentoVersion = $this->getMagentoVersion($client);

        switch ($magentoVersion) {
            case AbstractGuesser::MAGENTO_VERSION_1_14:
            case AbstractGuesser::MAGENTO_VERSION_1_13:
            case AbstractGuesser::MAGENTO_VERSION_1_12:
            case AbstractGuesser::MAGENTO_VERSION_1_11:
            case AbstractGuesser::MAGENTO_VERSION_1_9:
            case AbstractGuesser::MAGENTO_VERSION_1_8:
            case AbstractGuesser::MAGENTO_VERSION_1_7:
            case AbstractGuesser::MAGENTO_VERSION_1_6:
                return $this->familyNormalizer;
            default:
                throw new NotSupportedVersionException(AbstractGuesser::MAGENTO_VERSION_NOT_SUPPORTED_MESSAGE);
        }
    }
}
