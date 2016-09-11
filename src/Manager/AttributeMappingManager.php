<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Attribute mapping manager.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMappingManager
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
     * Get attribute from id and Magento url.
     *
     * @param integer $id
     * @param string  $magentoUrl
     *
     * @return AbstractAttribute|null
     */
    public function getAttributeFromId($id, $magentoUrl)
    {
        $magentoAttributeMapping = $this->getEntityRepository()->findOneBy(
            [
                'magentoAttributeId' => $id,
                'magentoUrl'         => $magentoUrl,
            ]
        );

        return $magentoAttributeMapping ? $magentoAttributeMapping->getAttribute() : null;
    }

    /**
     * Get id from attribute and Magento url.
     *
     * @param AbstractAttribute $attribute
     * @param string            $magentoUrl
     *
     * @return integer|null
     */
    public function getIdFromAttribute(AbstractAttribute $attribute, $magentoUrl)
    {
        $attributeMapping = $this->getEntityRepository()->findOneBy(
            [
                'attribute'   => $attribute,
                'magentoUrl'  => $magentoUrl,
            ]
        );

        return $attributeMapping ? $attributeMapping->getMagentoAttributeId() : null;
    }

    /**
     * Get all attribute mapping for a given magento.
     *
     * @param string $magentoUrl
     *
     * @return array
     */
    public function getAllMagentoAttribute($magentoUrl)
    {
        return $this->getEntityRepository()->findAll(
            [
                'magentoUrl' => $magentoUrl,
            ]
        );
    }

    /**
     * Register a new attribute mapping.
     *
     * @param AbstractAttribute $pimAttribute
     * @param integer           $magentoAttributeId
     * @param string            $magentoUrl
     */
    public function registerAttributeMapping(
        AbstractAttribute $pimAttribute,
        $magentoAttributeId,
        $magentoUrl
    ) {
        $attributeMapping = $this->getEntityRepository()->findOneBy([
            'attribute'  => $pimAttribute,
            'magentoUrl' => $magentoUrl,
        ]);
        $magentoAttributeMapping = new $this->className();

        if ($attributeMapping) {
            $magentoAttributeMapping = $attributeMapping;
        }

        $magentoAttributeMapping->setAttribute($pimAttribute);
        $magentoAttributeMapping->setMagentoAttributeId($magentoAttributeId);
        $magentoAttributeMapping->setMagentoUrl($magentoUrl);

        $this->objectManager->persist($magentoAttributeMapping);
        $this->objectManager->flush();
    }

    /**
     * Does the given magento attribute exist in pim ?
     *
     * @param string $attributeId
     * @param string $magentoUrl
     *
     * @return boolean
     */
    public function magentoAttributeExists($attributeId, $magentoUrl)
    {
        return $this->getAttributeFromId($attributeId, $magentoUrl) !== null;
    }

    /**
     * Get the entity repository.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->objectManager->getRepository($this->className);
    }
}
