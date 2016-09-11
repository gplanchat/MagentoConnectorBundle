<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Magento category mapping.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoCategoryMapping
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $magentoUrl;

    /** @var integer */
    protected $magentoCategoryId;

    /** @var CategoryInterface */
    protected $category;

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
     * @return MagentoCategoryMapping
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
     * @param string $magentoCategoryId
     *
     * @return MagentoCategoryMapping
     */
    public function setMagentoCategoryId($magentoCategoryId)
    {
        $this->magentoCategoryId = $magentoCategoryId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMagentoCategoryId()
    {
        return $this->magentoCategoryId;
    }

    /**
     * @param CategoryInterface $category
     *
     * @return MagentoCategoryMapping
     */
    public function setCategory(CategoryInterface $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return CategoryInterface
     */
    public function getCategory()
    {
        return $this->category;
    }
}
