<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Magento family mapping.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoFamilyMapping
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $magentoUrl;

    /** @var integer */
    protected $magentoFamilyId;

    /** @var string */
    protected $pimFamilyCode;

    /** @var Family */
    protected $family;

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
     * @return MagentoFamilyMapping
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
     * @param integer $magentoFamilyId
     *
     * @return MagentoFamilyMapping
     */
    public function setMagentoFamilyId($magentoFamilyId)
    {
        $this->magentoFamilyId = $magentoFamilyId;

        return $this;
    }

    /**
     * @return integer
     */
    public function getMagentoFamilyId()
    {
        return $this->magentoFamilyId;
    }

    /**
     * @param string $pimFamilyCode
     *
     * @return MagentoFamilyMapping
     */
    public function setPimFamilyCode($pimFamilyCode)
    {
        $this->pimFamilyCode = $pimFamilyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPimFamilyCode()
    {
        return $this->pimFamilyCode;
    }

    /**
     * @param Family $family
     *
     * @return MagentoFamilyMapping
     */
    public function setFamily(Family $family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * @return Family
     */
    public function getFamily()
    {
        return $this->family;
    }
}
