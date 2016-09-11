<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

/**
 * Magento group mapping.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoGroupMapping
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $magentoUrl;

    /** @var integer */
    protected $magentoGroupId;

    /** @var string */
    protected $pimGroupCode;

    /** @var integer */
    protected $pimFamilyCode;

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
     * @return MagentoGroupMapping
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
     * @param string $magentoGroupId
     *
     * @return MagentoGroupMapping
     */
    public function setMagentoGroupId($magentoGroupId)
    {
        $this->magentoGroupId = $magentoGroupId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMagentoGroupId()
    {
        return $this->magentoGroupId;
    }

    /**
     * @param string $pimGroupCode
     *
     * @return MagentoGroupMapping
     */
    public function setPimGroupCode($pimGroupCode)
    {
        $this->pimGroupCode = $pimGroupCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPimGroupCode()
    {
        return $this->pimGroupCode;
    }

    /**
     * @param integer $pimFamilyCode
     *
     * @return MagentoGroupMapping
     */
    public function setPimFamilyCode($pimFamilyCode)
    {
        $this->pimFamilyCode = $pimFamilyCode;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPimFamilyCode()
    {
        return $this->pimFamilyCode;
    }
}
