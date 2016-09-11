<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Manager\GroupManager as BaseGroupManager;
use Pim\Bundle\MagentoConnectorBundle\Entity\Repository\GroupRepository;

/**
 * Custom attribute manager.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupManager
{
    /** @var BaseGroupManager */
    protected $baseGroupManager;

    /** @var GroupRepository */
    protected $groupRepository;

    /**
     * @param BaseGroupManager $baseGroupManager
     * @param GroupRepository  $groupRepository
     */
    public function __construct(
        BaseGroupManager $baseGroupManager,
        GroupRepository $groupRepository
    ) {
        $this->baseGroupManager = $baseGroupManager;
        $this->groupRepository  = $groupRepository;
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\AbstractAttribute[]
     */
    public function getAvailableAxis()
    {
        return $this->baseGroupManager->getAvailableAxis();
    }

    /**
     * @return array
     */
    public function getAvailableAxisChoices()
    {
        return $this->baseGroupManager->getAvailableAxisChoices();
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->baseGroupManager->getChoices();
    }

    /**
     * Get axis as choice list.
     *
     * @param boolean $isVariant
     *
     * @return array
     */
    public function getTypeChoices($isVariant)
    {
        return $this->baseGroupManager->getTypeChoices($isVariant);
    }

    /**
     * Returns the entity repository.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->groupRepository;
    }

    /**
     * Returns the group type repository.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getGroupTypeRepository()
    {
        return $this->baseGroupManager->getGroupTypeRepository();
    }

    /**
     * Removes a group.
     *
     * @param Group $group
     */
    public function remove(Group $group)
    {
        $this->baseGroupManager->remove($group);
    }

    /**
     * Returns an array containing a limited number of product groups, and the total number of products.
     *
     * @param Group   $group
     * @param integer $maxResults
     *
     * @return array
     */
    public function getProductList(Group $group, $maxResults)
    {
        return $this->baseGroupManager->getProductList($group, $maxResults);
    }
}
