<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

/**
 * Magento category writer.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryWriter extends AbstractWriter
{
    /** @var CategoryMappingManager */
    protected $categoryMappingManager;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param CategoryMappingManager              $categoryMappingManager
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        CategoryMappingManager $categoryMappingManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $batches)
    {
        $this->beforeExecute();

        //creation for each product in the admin storeView (with default locale)
        foreach ($batches as $batch) {
            try {
                $this->handleNewCategory($batch);
                $this->handleUpdateCategory($batch);
                $this->handleMoveCategory($batch);
                $this->handleVariationCategory($batch);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), []);
            }
        }
    }

    /**
     * Handle category creation.
     *
     * @param array $batch
     */
    protected function handleNewCategory(array $batch)
    {
        if (isset($batch['create'])) {
            foreach ($batch['create'] as $newCategory) {
                $pimCategory       = $newCategory['pimCategory'];
                $magentoCategoryId = $this->webservice->sendNewCategory($newCategory['magentoCategory']);
                $magentoUrl        = $this->getSoapUrl();

                $this->categoryMappingManager->registerCategoryMapping(
                    $pimCategory,
                    $magentoCategoryId,
                    $magentoUrl
                );

                $this->stepExecution->incrementSummaryInfo('category_created');
            }
        }
    }

    /**
     * Handle category update.
     *
     * @param array $batch
     */
    protected function handleUpdateCategory(array $batch)
    {
        if (isset($batch['update'])) {
            foreach ($batch['update'] as $categoryToUpdate) {
                $this->webservice->sendUpdateCategory($categoryToUpdate);

                $storeViewList = $this->webservice->getStoreViewsList();
                if (count($storeViewList) > 1) {
                    $this->updateAdminStoreView($categoryToUpdate);
                }

                $this->stepExecution->incrementSummaryInfo('category_updated');
            }
        }
    }

    /**
     * Update category in admin store view.
     *
     * @param array $categoryToUpdate
     */
    protected function updateAdminStoreView(array $categoryToUpdate)
    {
        $categoryToUpdate[2] = Webservice::ADMIN_STOREVIEW;
        $this->webservice->sendUpdateCategory($categoryToUpdate);
    }

    /**
     * Handle category move.
     *
     * @param array $batch
     */
    protected function handleMoveCategory(array $batch)
    {
        if (isset($batch['move'])) {
            foreach ($batch['move'] as $moveCategory) {
                $this->webservice->sendMoveCategory($moveCategory);

                $this->stepExecution->incrementSummaryInfo('category_moved');
            }
        }
    }

    /**
     * Handle category variation update.
     *
     * @param array $batch
     */
    protected function handleVariationCategory(array $batch)
    {
        if (isset($batch['variation'])) {
            foreach ($batch['variation'] as $variationCategory) {
                $pimCategory        = $variationCategory['pimCategory'];
                $magentoCategoryId  = $this->categoryMappingManager
                    ->getIdFromCategory($pimCategory, $this->getSoapUrl());
                $magentoCategory    = $variationCategory['magentoCategory'];
                $magentoCategory[0] = $magentoCategoryId;

                $this->webservice->sendUpdateCategory($magentoCategory);

                $this->stepExecution->incrementSummaryInfo('category_translation_sent');
            }
        }
    }
}
