<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Magento attribute mapping.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributeMapping
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $magentoUrl;

    /** @var integer */
    protected $magentoAttributeId;

    /** @var AbstractAttribute */
    protected $attribute;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $magentoUrl
     *
     * @return MagentoAttributeMapping
     */
    public function setMagentoUrl($magentoUrl)
    {
        $this->magentoUrl = $magentoUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getMagentoUrl()
    {
        return $this->magentoUrl;
    }

    /**
     * @param string $magentoAttributeId
     *
     * @return MagentoAttributeMapping
     */
    public function setMagentoAttributeId($magentoAttributeId)
    {
        $this->magentoAttributeId = $magentoAttributeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMagentoAttributeId()
    {
        return $this->magentoAttributeId;
    }

    /**
     * @param AbstractAttribute $attribute
     *
     * @return MagentoAttributeMapping
     */
    public function setAttribute(AbstractAttribute $attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @return AbstractAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}
