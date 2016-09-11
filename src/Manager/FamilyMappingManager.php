<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Family mapping manager.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyMappingManager
{
    /** @var ObjectManager */
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
     * Get family from id and Magento url.
     *
     * @param integer $id
     * @param string  $magentoUrl
     *
     * @return Family|null
     */
    public function getFamilyFromId($id, $magentoUrl)
    {
        $magentoFamilyMapping = $this->getEntityRepository()->findOneBy(
            [
                'magentoFamilyId' => $id,
                'magentoUrl'      => $magentoUrl,
            ]
        );

        return $magentoFamilyMapping ? $magentoFamilyMapping->getFamily() : null;
    }

    /**
     * Get id from family and Magento url.
     *
     * @param Family $family
     * @param string $magentoUrl
     *
     * @return integer|null
     */
    public function getIdFromFamily(Family $family, $magentoUrl)
    {
        $familyMapping = $this->getEntityRepository()->findOneBy(
            [
                'family'     => $family,
                'magentoUrl' => $magentoUrl,
            ]
        );

        return $familyMapping ? $familyMapping->getMagentoFamilyId() : null;
    }

    /**
     * Register a new family mapping.
     *
     * @param Family  $pimFamily
     * @param integer $magentoFamilyId
     * @param string  $magentoUrl
     */
    public function registerFamilyMapping(
        Family $pimFamily,
        $magentoFamilyId,
        $magentoUrl
    ) {
        $familyMapping = $this->getEntityRepository()->findOneBy([
            'family'     => $pimFamily,
            'magentoUrl' => $magentoUrl,
        ]);
        $magentoFamilyMapping = new $this->className();

        if ($familyMapping) {
            $magentoFamilyMapping = $familyMapping;
        }

        $magentoFamilyMapping->setFamily($pimFamily);
        $magentoFamilyMapping->setMagentoFamilyId($magentoFamilyId);
        $magentoFamilyMapping->setMagentoUrl($magentoUrl);

        $this->objectManager->persist($magentoFamilyMapping);
        $this->objectManager->flush();
    }

    /**
     * Does the given magento family exist in pim ?
     *
     * @param string $familyId
     * @param string $magentoUrl
     *
     * @return boolean
     */
    public function magentoFamilyExists($familyId, $magentoUrl)
    {
        return null !== $this->getFamilyFromId($familyId, $magentoUrl);
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
