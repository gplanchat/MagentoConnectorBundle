<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

/**
 * Magento family mapper.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoFamilyMapper extends MagentoMapper
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
            try {
                $families = $this->webserviceGuesser->getWebservice($this->clientParameters)->getAttributeSetList();
            } catch (SoapCallException $e) {
                return array();
            }

            foreach ($families as $familyId => $family) {
                $targets[] = ['id' => $familyId, 'name' => $family['name']];
            }
        }

        return $targets;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($rootIdentifier = 'family')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
