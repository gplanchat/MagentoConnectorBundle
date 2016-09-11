<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\CategoryNotMappedException;
use Gedmo\Sluggable\Util\Urlizer;

/**
 * A normalizer to transform a category entity into an array.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer extends AbstractNormalizer
{
    /** @var CategoryMappingManager */
    protected $categoryMappingManager;

    /** @var CategoryRepository */
    protected $categoryRepository;

    /**
     * @param ChannelManager         $channelManager
     * @param CategoryMappingManager $categoryMappingManager
     * @param CategoryRepository     $categoryRepository
     */
    public function __construct(
        ChannelManager         $channelManager,
        CategoryMappingManager $categoryMappingManager,
        CategoryRepository     $categoryRepository
    ) {
        parent::__construct($channelManager);

        $this->categoryMappingManager = $categoryMappingManager;
        $this->categoryRepository     = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($category, $format = null, array $context = [])
    {
        $normalizedCategory = $this->getDefaultCategory($category, $context);

        //For each storeview, we update the product only with localized attributes
        foreach ($category->getTranslations() as $translation) {
            $storeView = $this->getStoreViewForLocale(
                $translation->getLocale(),
                $context['magentoStoreViews'],
                $context['storeViewMapping']
            );

            //If a locale for this storeview exist in PIM, we create a translated product in this locale
            if ($storeView) {
                $normalizedCategory['variation'][] = $this->getNormalizedVariationCategory(
                    $category,
                    $translation->getLocale(),
                    $storeView['code'],
                    $context['urlKey']
                );
            }
        }

        return $normalizedCategory;
    }

    /**
     * Get the default category.
     *
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return array
     */
    protected function getDefaultCategory(CategoryInterface $category, array $context)
    {
        $normalizedCategory = [
            'create'    => [],
            'update'    => [],
            'move'      => [],
            'variation' => [],
        ];

        if ($this->magentoCategoryExists($category, $context['magentoCategories'], $context['magentoUrl'])) {
            $normalizedCategory['update'][] = $this->getNormalizedUpdateCategory(
                $category,
                $context
            );

            if ($this->categoryHasMoved($category, $context)) {
                $normalizedCategory['move'][] = $this->getNormalizedMoveCategory($category, $context);
            }
        } else {
            $normalizedCategory['create'][] = $this->getNormalizedNewCategory(
                $category,
                $context,
                $context['defaultStoreView']
            );
        }

        return $normalizedCategory;
    }

    /**
     * Test if the given category exist on Magento side.
     *
     * @param CategoryInterface $category
     * @param array             $magentoCategories
     * @param string            $magentoUrl
     *
     * @return boolean
     */
    protected function magentoCategoryExists(CategoryInterface $category, array $magentoCategories, $magentoUrl)
    {
        return ($magentoCategoryId = $this->getMagentoCategoryId($category, $magentoUrl)) !== null &&
            isset($magentoCategories[$magentoCategoryId]);
    }

    /**
     * Get category id on Magento side for the given category.
     *
     * @param CategoryInterface $category
     * @param string            $magentoUrl
     *
     * @return int
     */
    protected function getMagentoCategoryId(CategoryInterface $category, $magentoUrl)
    {
        return $this->categoryMappingManager->getIdFromCategory($category, $magentoUrl);
    }

    /**
     * Get new normalized categories.
     *
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return array
     *
     * @throws CategoryNotMappedException
     */
    protected function getNormalizedNewCategory(CategoryInterface $category, array $context)
    {
        $parentCategoryId = $this->categoryMappingManager->getIdFromCategory(
            $category->getParent(),
            $context['magentoUrl'],
            $context['categoryMapping']
        );

        $magentoCategoryBaseParameters = [
            'name'              => $this->getCategoryLabel($category, $context['defaultLocale']),
            'is_active'         => 1,
            'include_in_menu'   => 1,
            'available_sort_by' => 1,
            'default_sort_by'   => 1,
        ];

        if (false === $context['urlKey']) {
            $magentoCategoryBaseParameters['url_key'] = $this->generateUrlKey($category, $context['defaultLocale']);
        }

        if (null === $parentCategoryId) {
            throw new CategoryNotMappedException(
                sprintf(
                    'An error occured during the root category creation on Magento. The Magento '.
                    'connector was unable to find the mapped category "%s (%s)". Remember that you need to map your '.
                    'Magento root categories to Akeneo categories. All sub categories of %s will not be exported.',
                    $category->getLabel(),
                    $category->getCode(),
                    $category->getCode()
                )
            );
        } else {
            return [
                'magentoCategory' => [
                    (string) $parentCategoryId,
                    $magentoCategoryBaseParameters,
                    $context['defaultStoreView'],
                ],
                'pimCategory' => $category,
            ];
        }
    }

    /**
     * Get update normalized categories.
     *
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return array
     */
    protected function getNormalizedUpdateCategory(CategoryInterface $category, array $context)
    {
        $magentoCategoryBaseParameters = [
            'name'              => $this->getCategoryLabel($category, $context['defaultLocale']),
            'available_sort_by' => 1,
            'default_sort_by'   => 1,
            'is_anchor'         => $context['is_anchor'],
            'position'          => $category->getLeft(),
        ];

        if (false === $context['urlKey']) {
            $magentoCategoryBaseParameters['url_key'] = $this->generateUrlKey($category, $context['defaultLocale']);
        }

        return [
            $this->getMagentoCategoryId($category, $context['magentoUrl']),
            $magentoCategoryBaseParameters,
            $context['defaultStoreView'],
        ];
    }

    /**
     * Get normalized variation category.
     *
     * @param CategoryInterface $category
     * @param string            $localeCode
     * @param string            $storeViewCode
     * @param boolean           $urlKey
     *
     * @return array
     */
    protected function getNormalizedVariationCategory(
        CategoryInterface $category,
        $localeCode,
        $storeViewCode,
        $urlKey = false
    ) {
        $magentoCategoryData = [
            'name'              => $this->getCategoryLabel($category, $localeCode),
            'available_sort_by' => 1,
            'default_sort_by'   => 1,
        ];

        if (false === $urlKey) {
            $magentoCategoryData['url_key'] = $this->generateUrlKey($category, $localeCode);
        }

        return [
            'magentoCategory' => [
                null,
                $magentoCategoryData,
                $storeViewCode,
            ],
            'pimCategory' => $category,
        ];
    }

    /**
     * Get move normalized categories.
     *
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return array
     */
    protected function getNormalizedMoveCategory(CategoryInterface $category, array $context)
    {
        $magentoCategoryId = $this->getMagentoCategoryId($category, $context['magentoUrl']);

        $magentoCategoryNewParentId = $this->categoryMappingManager->getIdFromCategory(
            $category->getParent(),
            $context['magentoUrl'],
            $context['categoryMapping']
        );

        $previousCategories = $this->categoryRepository->getPrevSiblings($category);
        $previousCategory = end($previousCategories);

        $previousMagentoCategoryId = null;
        if ($previousCategory && null !== $previousCategory) {
            $previousMagentoCategoryId = $this->categoryMappingManager->getIdFromCategory(
                $previousCategory,
                $context['magentoUrl'],
                $context['categoryMapping']
            );
        }

        return [
            $magentoCategoryId,
            $magentoCategoryNewParentId,
            $previousMagentoCategoryId,
        ];
    }

    /**
     * Get category label.
     *
     * @param CategoryInterface $category
     * @param string            $localeCode
     *
     * @return string
     */
    protected function getCategoryLabel(CategoryInterface $category, $localeCode)
    {
        $category->setLocale($localeCode);

        return $category->getLabel();
    }

    /**
     * Test if the category has moved on magento side.
     *
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return boolean
     */
    protected function categoryHasMoved(CategoryInterface $category, $context)
    {
        $currentCategoryId = $this->getMagentoCategoryId($category, $context['magentoUrl']);
        $currentParentId   = $this->categoryMappingManager->getIdFromCategory(
            $category->getParent(),
            $context['magentoUrl'],
            $context['categoryMapping']
        );

        return isset($context['magentoCategories'][$currentCategoryId]) ?
            $context['magentoCategories'][$currentCategoryId]['parent_id'] !== $currentParentId :
            true;
    }

    /**
     * Generate url key for category name and code
     * The code is included to make sure the url_key is unique, as required in Magento.
     *
     * @param CategoryInterface $category
     * @param string            $localeCode
     *
     * @return string
     */
    protected function generateUrlKey(CategoryInterface $category, $localeCode)
    {
        $code = $category->getCode();
        $label = $this->getCategoryLabel($category, $localeCode);

        $url = Urlizer::urlize($label.'-'.$code);

        return $url;
    }
}
