<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * Magento attribute code mapper.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoAttributeCodeMapper extends MagentoMapper
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
        if (!$this->isValid()) {
            return new MappingCollection();
        } else {
            try {
                $attributes = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAllAttributes();
            } catch (SoapCallException $e) {
                return new MappingCollection();
            }

            $mapping = new MappingCollection();
            foreach (array_keys($attributes) as $attributeCode) {
                if (in_array($attributeCode, $this->mandatoryAttributes())) {
                    $mapping->add(
                        [
                            'source'    => $attributeCode,
                            'target'    => $attributeCode,
                            'deletable' => false,
                        ]
                    );
                }
            }

            return $mapping;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTargets()
    {
        $sources = [];

        if ($this->isValid()) {
            $attributeCodes = array_keys(
                $this->webserviceGuesser->getWebservice($this->clientParameters)->getAllAttributes()
            );

            foreach ($attributeCodes as $attributeCode) {
                $sources[] = ['id' => $attributeCode, 'text' => $attributeCode];
            }
        }

        return $sources;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($rootIdentifier = 'attribute')
    {
        return parent::getIdentifier($rootIdentifier);
    }

    /**
     * @return string[]
     */
    protected function mandatoryAttributes()
    {
        return [
            'name',
            'price',
            'description',
            'short_description',
            'tax_class_id',
            'weight',
        ];
    }
}
