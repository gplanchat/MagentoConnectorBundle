<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Magento category cleaner.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryCleaner extends Cleaner
{
    /** @staticvar string */
    const SOAP_FAULT_NO_CATEGORY = '102';

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
    public function execute()
    {
        parent::beforeExecute();

        $magentoCategories = $this->webservice->getCategoriesStatus();

        foreach ($magentoCategories as $category) {
            if (!$this->categoryMappingManager->magentoCategoryExists($category['category_id'], $this->getSoapUrl()) &&
                !(
                    $category['level'] === '0' ||
                    $category['level'] === '1'
                )
            ) {
                try {
                    $this->handleCategoryNotInPimAnymore($category);
                } catch (SoapCallException $e) {
                    throw new InvalidItemException($e->getMessage(), [json_encode($category)]);
                }
            }
        }
    }

    /**
     * Handle deletion or disabling of categories that are not in PIM anymore.
     *
     * @param array $category
     *
     * @throws InvalidItemException
     * @throws SoapCallException
     */
    protected function handleCategoryNotInPimAnymore(array $category)
    {
        if ($this->notInPimAnymoreAction === self::DISABLE) {
            $this->webservice->disableCategory($category['category_id']);
            $this->stepExecution->incrementSummaryInfo('category_disabled');
        } elseif ($this->notInPimAnymoreAction === self::DELETE) {
            try {
                $this->webservice->deleteCategory($category['category_id']);
                $this->stepExecution->incrementSummaryInfo('category_deleted');
            } catch (SoapCallException $e) {
                if (static::SOAP_FAULT_NO_CATEGORY === $e->getPrevious()->faultcode) {
                    throw new InvalidItemException(
                        $e->getMessage(),
                        [json_encode($category)],
                        [$e]
                    );
                } else {
                    throw $e;
                }
            }
        }
    }
}
