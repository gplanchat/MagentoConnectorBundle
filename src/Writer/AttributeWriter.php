<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

/**
 * Magento attribute writer. Add attributes to groups and attribute sets on magento side.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeWriter extends AbstractWriter
{
    /** @staticvar int */
    const ATTRIBUTE_UPDATE_SIZE               = 2;

    /** @staticvar string */
    const SOAP_FAULT_ATTRIBUTE_ALREADY_IN_SET = '109';

    /** @staticvar string */
    const SOAP_FAULT_GROUP_ALREADY_IN_SET     = '112';

    /** @var AttributeMappingManager */
    protected $attributeMappingManager;

    /** @var AbstractAttribute */
    protected $attribute;

    /** @var FamilyMappingManager */
    protected $familyMappingManager;

    /** @var AttributeGroupMappingManager */
    protected $attributeGroupMappingManager;

    /** @var MagentoMappingMerger */
    protected $attributeIdMappingMerger;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param FamilyMappingManager                $familyMappingManager
     * @param AttributeMappingManager             $attributeMappingManager
     * @param AttributeGroupMappingManager        $attributeGroupMappingManager
     * @param MagentoMappingMerger                $attributeIdMappingMerger
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        AttributeMappingManager $attributeMappingManager,
        AttributeGroupMappingManager $attributeGroupMappingManager,
        MagentoMappingMerger $attributeIdMappingMerger,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->attributeMappingManager      = $attributeMappingManager;
        $this->familyMappingManager         = $familyMappingManager;
        $this->attributeGroupMappingManager = $attributeGroupMappingManager;
        $this->attributeIdMappingMerger     = $attributeIdMappingMerger;

        $this->attributeIdMappingMerger->setParameters($this->getClientParameters(), $this->getDefaultStoreView());
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $attributes)
    {
        $this->beforeExecute();

        foreach ($attributes as $attribute) {
            try {
                $pimAttribute = $attribute[0];
                $this->addGroupToAttributeSet($pimAttribute);
                $this->handleAttribute($attribute[1], $pimAttribute);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), [$pimAttribute]);
            }
        }
    }

    /**
     * Handle attribute creation and update.
     *
     * @param array             $attribute
     * @param AbstractAttribute $pimAttribute
     *
     * @throws InvalidItemException
     */
    protected function handleAttribute(array $attribute, $pimAttribute)
    {
        if (count($attribute) === self::ATTRIBUTE_UPDATE_SIZE) {
            $this->webservice->updateAttribute($attribute);
            $magentoAttributeId = $this->attributeIdMappingMerger->getMapping()->getTarget($pimAttribute->getCode());
            $this->manageAttributeSet($magentoAttributeId, $pimAttribute);

            $this->stepExecution->incrementSummaryInfo('attribute_updated');
        } else {
            $magentoAttributeId = $this->webservice->createAttribute($attribute);

            $this->manageAttributeSet($magentoAttributeId, $pimAttribute);

            $this->stepExecution->incrementSummaryInfo('attribute_created');

            $magentoUrl = $this->getSoapUrl();
            $this->attributeMappingManager->registerAttributeMapping(
                $pimAttribute,
                $magentoAttributeId,
                $magentoUrl
            );
        }
    }

    /**
     * Verify if the magento attribute id is null else add the attribute to the attribute set.
     *
     * @param integer           $magentoAttributeId
     * @param AbstractAttribute $pimAttribute
     */
    protected function manageAttributeSet($magentoAttributeId, $pimAttribute)
    {
        if ($this->attributeIdMappingMerger->getMapping()->getSource($magentoAttributeId) != $pimAttribute->getCode()) {
            $this->addAttributeToAttributeSet($magentoAttributeId, $pimAttribute);
        }
    }

    /**
     * Get the magento group id.
     *
     * @param AbstractAttribute $pimAttribute
     * @param Family            $pimFamily
     *
     * @return int|null
     */
    protected function getGroupId(AbstractAttribute $pimAttribute, Family $pimFamily)
    {
        $pimGroup = $pimAttribute->getGroup();

        if ($pimGroup !== null) {
            $magentoGroupId = $this->attributeGroupMappingManager
                ->getIdFromGroup($pimGroup, $pimFamily, $this->getSoapUrl());
        } else {
            $magentoGroupId = null;
        }

        return $magentoGroupId;
    }

    /**
     * Add attribute to corresponding attribute sets.
     *
     * @param integer           $magentoAttributeId
     * @param AbstractAttribute $pimAttribute
     *
     * @throws SoapCallException
     */
    protected function addAttributeToAttributeSet($magentoAttributeId, AbstractAttribute $pimAttribute)
    {
        $families = $pimAttribute->getFamilies();

        foreach ($families as $family) {
            $magentoGroupId  = $this->getGroupId($pimAttribute, $family);
            $magentoFamilyId = $this->familyMappingManager->getIdFromFamily($family, $this->getSoapUrl());
            try {
                if (null !== $magentoFamilyId) {
                    $this->webservice->addAttributeToAttributeSet(
                        $magentoAttributeId,
                        $magentoFamilyId,
                        $magentoGroupId
                    );
                }
            } catch (SoapCallException $e) {
                if (static::SOAP_FAULT_ATTRIBUTE_ALREADY_IN_SET === $e->getPrevious()->faultcode) {
                    echo "DEBUG: Attribute ".$magentoAttributeId.
                        " already exists in attribute set ".$magentoFamilyId."\n";
                } else {
                    throw $e;
                }
            }
        }
    }

    /**
     * Create a group in an attribute set.
     *
     * @param AbstractAttribute $pimAttribute
     *
     * @throws SoapCallException
     */
    protected function addGroupToAttributeSet(AbstractAttribute $pimAttribute)
    {
        $families = $pimAttribute->getFamilies();
        $group = $pimAttribute->getGroup();

        if (isset($group)) {
            $groupName = $group->getCode();

            foreach ($families as $family) {
                $familyMagentoId = $this->familyMappingManager->getIdFromFamily($family, $this->getSoapUrl());
                if (null === $familyMagentoId) {
                    $magentoAttributeSets = $this->webservice->getAttributeSetList();
                    if (array_key_exists($family->getCode(), $magentoAttributeSets)) {
                        $familyMagentoId = $magentoAttributeSets[$family->getCode()];
                    }
                }
                try {
                    $magentoGroupId = $this->webservice->addAttributeGroupToAttributeSet($familyMagentoId, $groupName);
                    $this->attributeGroupMappingManager->registerGroupMapping(
                        $group,
                        $family,
                        $magentoGroupId,
                        $this->getSoapUrl()
                    );
                } catch (SoapCallException $e) {
                    if (static::SOAP_FAULT_GROUP_ALREADY_IN_SET === $e->getPrevious()->faultcode) {
                        echo "DEBUG: Group ".$groupName." already exists in attribute set ".$familyMagentoId."\n";
                    } else {
                        throw $e;
                    }
                }
            }
        }
    }
}
