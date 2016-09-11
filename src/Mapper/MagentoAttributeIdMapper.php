<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * Magento attribute id mapper.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributeIdMapper extends MagentoMapper
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
    public function getMapping()
    {
        $mapping = new MappingCollection();

        if ($this->isValid()) {
            try {
                $attributes = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAllAttributes();
            } catch (SoapCallException $e) {
                return $mapping;
            }

            foreach ($attributes as $attribute) {
                $mapping->add(
                    [
                        'source'    => $attribute['code'],
                        'target'    => $attribute['attribute_id'],
                        'deletable' => true,
                    ]
                );
            }
        }

        return $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTargets()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllSources()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($rootIdentifier = 'attribute_id')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
