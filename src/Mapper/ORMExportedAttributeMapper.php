<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

/**
 * Magento exported attribute mapper.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMExportedAttributeMapper extends Mapper
{
    /** @var MagentoSoapClientParameters */
    protected $clientParameters;

    /** @var HasValidCredentialsValidator */
    protected $hasValidCredentialsValidator;

    /** @var AttributeMappingManager */
    protected $attributeMappingManager;

    /** @var string */
    protected $rootIdentifier;

    /** @var string */
    protected $defaultStoreView;

    /** @var MagentoMappingMerger */
    protected $attributeCodeMappingMerger;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param string                       $rootIdentifier
     * @param AttributeMappingManager      $attributeMappingManager
     * @param MagentoMappingMerger         $attributeCodeMappingMerger
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        AttributeMappingManager $attributeMappingManager,
        MagentoMappingMerger $attributeCodeMappingMerger,
        $rootIdentifier
    ) {
        $this->hasValidCredentialsValidator = $hasValidCredentialsValidator;
        $this->rootIdentifier               = $rootIdentifier;
        $this->attributeMappingManager      = $attributeMappingManager;
        $this->attributeCodeMappingMerger   = $attributeCodeMappingMerger;
    }

    /**
     * {@inheritdoc}
     */
    public function getMapping()
    {
        $magentoAttributeMappings = $this->attributeMappingManager
            ->getAllMagentoAttribute($this->clientParameters->getSoapUrl());

        $attributeCodeMapping = $this->attributeCodeMappingMerger->getMapping();

        $mappingCollection = new MappingCollection();

        foreach ($magentoAttributeMappings as $magentoAttributeMapping) {
            $pimAttributeCode = $magentoAttributeMapping->getAttribute()->getCode();
            $mappingCollection->add(
                [
                    'source'    => $attributeCodeMapping->getTarget($pimAttributeCode),
                    'target'    => $magentoAttributeMapping->getMagentoAttributeId(),
                    'deletable' => true,
                ]
            );
        }

        return $mappingCollection;
    }

    /**
     * Set mapper parameters.
     *
     * @param MagentoSoapClientParameters $clientParameters
     * @param string                      $defaultStoreView
     */
    public function setParameters(MagentoSoapClientParameters $clientParameters, $defaultStoreView)
    {
        $this->clientParameters = $clientParameters;
        $this->defaultStoreView = $defaultStoreView;

        $this->attributeCodeMappingMerger->setParameters($clientParameters, $defaultStoreView);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($rootIdentifier = 'attribute_id')
    {
        if ($this->isValid()) {
            return sha1(sprintf(Mapper::IDENTIFIER_FORMAT, $rootIdentifier, $this->clientParameters->getSoapUrl()));
        } else {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return $this->clientParameters !== null &&
        $this->hasValidCredentialsValidator->areValidSoapCredentials($this->clientParameters);
    }
}
