<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento family mapper.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMFamilyMapper extends ORMPimMapper
{
    /** @var FamilyMappingManager */
    protected $familyManager;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param SimpleMappingManager         $simpleMappingManager
     * @param FamilyMappingManager         $familyManager
     * @param string                       $rootIdentifier
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager         $simpleMappingManager,
        FamilyMappingManager         $familyManager,
        $rootIdentifier
    ) {
        parent::__construct($hasValidCredentialsValidator, $simpleMappingManager, $rootIdentifier);

        $this->familyManager = $familyManager;
    }

    /**
     * @param Family $family
     *
     * @return array
     */
    public function getAllSources(Family $family = null)
    {
        $sources = [];

        if ($this->isValid()) {
            $families = $this->familyManager->getFamilies();

            foreach ($families as $family) {
                $sources[] = ['id' => $family->getCode(), 'name' => $family->getCode()];
            }
        }

        return $sources;
    }
}
