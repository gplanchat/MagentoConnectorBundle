<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento storeview mapper.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoStoreViewMapper extends MagentoMapper
{
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
            $storeViews = $this->webserviceGuesser->getWebservice($this->clientParameters)->getStoreViewsList();

            foreach ($storeViews as $storeView) {
                if ($storeView['code'] !== $this->defaultStoreView) {
                    $targets[] = ['id' => $storeView['code'], 'text' => $storeView['code']];
                }
            }
        }

        return $targets;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($rootIdentifier = 'storeview')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
