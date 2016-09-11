<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Attribute group mapping manager.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupMappingManager
{
    /** @var \Doctrine\Common\Persistence\ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $className;

    /**
     * @param ObjectManager $objectManager
     * @param string        $className
     */
    public function __construct(ObjectManager $objectManager, $className)
    {
        $this->objectManager = $objectManager;
        $this->className     = $className;
    }

    /**
     * Get id from group and Magento url.
     *
     * @param AttributeGroup $group
     * @param Family         $family
     * @param string         $magentoUrl
     *
     * @return integer
     */
    public function getIdFromGroup(AttributeGroup $group, Family $family, $magentoUrl)
    {
        $groupMapping = $this->getEntityRepository()->findOneBy(
            [
                'pimGroupCode'  => $group->getCode(),
                'pimFamilyCode' => $family->getCode(),
                'magentoUrl'    => $magentoUrl,
            ]
        );

        return $groupMapping ? $groupMapping->getMagentoGroupId() : null;
    }

    /**
     * Register a new group mapping.
     *
     * @param AttributeGroup $pimGroup
     * @param Family         $pimFamily
     * @param integer        $magentoGroupId
     * @param string         $magentoUrl
     */
    public function registerGroupMapping(
        AttributeGroup $pimGroup,
        Family $pimFamily,
        $magentoGroupId,
        $magentoUrl
    ) {
        $groupMapping = $this->getEntityRepository()->findOneBy(
            [
                'pimGroupCode'  => $pimGroup->getCode(),
                'pimFamilyCode' => $pimFamily->getCode(),
                'magentoUrl'    => $magentoUrl,
            ]
        );

        $magentoGroupMapping = new $this->className();

        if ($groupMapping) {
            $magentoGroupMapping = $groupMapping;
        }

        $magentoGroupMapping->setPimGroupCode($pimGroup->getCode());
        $magentoGroupMapping->setPimFamilyCode($pimFamily->getCode());
        $magentoGroupMapping->setMagentoGroupId($magentoGroupId);
        $magentoGroupMapping->setMagentoUrl($magentoUrl);

        $this->objectManager->persist($magentoGroupMapping);
        $this->objectManager->flush();
    }

    /**
     * @return array
     */
    public function getAllMappings()
    {
        return ($this->getEntityRepository()->findAll() ? $this->getEntityRepository()->findAll() : null);
    }

    /**
     * Get the entity manager.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->objectManager->getRepository($this->className);
    }
}
