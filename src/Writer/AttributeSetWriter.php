<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

/**
 * Magento attribute set writer.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeSetWriter extends AbstractWriter
{
    /** @var FamilyMappingManager */
    protected $familyMappingManager;

    /** @var AttributeMappingManager */
    protected $attributeMappingManager;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param FamilyMappingManager                $familyMappingManager
     * @param AttributeMappingManager             $attributeMappingManager
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        AttributeMappingManager $attributeMappingManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->attributeMappingManager = $attributeMappingManager;
        $this->familyMappingManager    = $familyMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->beforeExecute();
        foreach ($items as $item) {
            try {
                $this->handleNewFamily($item);
            } catch (SoapCallException $e) {
                $this->stepExecution->incrementSummaryInfo('family_exists');
            }
        }
    }

    /**
     * Handle family creation.
     *
     * @param array $item
     *
     * @throws InvalidItemException
     */
    protected function handleNewFamily(array $item)
    {
        if (isset($item['families_to_create'])) {
            $pimFamily       = $item['family_object'];
            $magentoFamilyId = $this->webservice->createAttributeSet($item['families_to_create']['attributeSetName']);
            $magentoUrl      = $this->getSoapUrl();
            $this->familyMappingManager->registerFamilyMapping(
                $pimFamily,
                $magentoFamilyId,
                $magentoUrl
            );
            $this->stepExecution->incrementSummaryInfo('family_created');
        }
    }
}
