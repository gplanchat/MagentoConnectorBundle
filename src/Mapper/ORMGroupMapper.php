<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\AttributeGroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento group mapper.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMGroupMapper extends ORMMapper
{
    /** @var AttributeGroupMappingManager */
    protected $attributeGroupManager;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param SimpleMappingManager         $simpleMappingManager
     * @param AttributeGroupMappingManager $attributeGroupManager
     * @param string                       $rootIdentifier
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager         $simpleMappingManager,
        AttributeGroupMappingManager $attributeGroupManager,
        $rootIdentifier
    ) {
        parent::__construct($hasValidCredentialsValidator, $simpleMappingManager, $rootIdentifier);

        $this->groupManager = $attributeGroupManager;
    }

    /**
     * @param AttributeGroup $group
     *
     * @return array
     */
    public function getAllSources(AttributeGroup $group = null)
    {
        $sources = [];

        if ($this->isValid()) {
            $groups = $this->attributeGroupManager->getAllGroups();

            foreach ($groups as $group) {
                $sources[] = ['id' => $group->getCode(), 'name' => $group->getCode()];
            }
        }

        return $sources;
    }
}
