<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * Magento category mapper.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoCategoryMapper extends MagentoMapper
{
    /** @staticvar int */
    const ROOT_CATEGORY_ID = 1;

    /** @var WebserviceGuesser */
    protected $webserviceGuesser;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param WebserviceGuesser            $webserviceGuesser
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesser $webserviceGuesser
    ) {
        parent::__construct($hasValidCredentialsValidator);

        $this->webserviceGuesser = $webserviceGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTargets()
    {
        $targets = [];

        if ($this->isValid()) {
            try {
                $categories = $this->webserviceGuesser->getWebservice($this->clientParameters)->getCategoriesStatus();
            } catch (SoapCallException $e) {
                return array();
            }

            foreach ($categories as $categoryId => $category) {
                if ($categoryId != self::ROOT_CATEGORY_ID) {
                    $targets[] = ['id' => $categoryId, 'text' => $category['name']];
                }
            }
        }

        return $targets;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($rootIdentifier = 'category')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
