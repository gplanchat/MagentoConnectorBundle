<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Doctrine\ORM\EntityManager;

/**
 * Magento option cleaner.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionCleaner extends Cleaner
{
    /** @var EntityManager */
    protected $em;

    /** @var string */
    protected $attributeClassName;

    /** @var string */
    protected $optionClassName;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param EntityManager                       $em
     * @param string                              $attributeClassName
     * @param string                              $optionClassName
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        EntityManager $em,
        $attributeClassName,
        $optionClassName,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->em                 = $em;
        $this->attributeClassName = $attributeClassName;
        $this->optionClassName    = $optionClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoOptions = $this->webservice->getAllAttributesOptions();

        foreach ($magentoOptions as $attributeCode => $options) {
            $attribute = $this->getAttribute($attributeCode);

            $this->cleanOptions($options, $attribute);
        }
    }

    /**
     * Clean options.
     *
     * @param array             $options
     * @param AbstractAttribute $attribute
     *
     * @throws InvalidItemException If clean doesn't goes well
     */
    protected function cleanOptions(array $options, AbstractAttribute $attribute = null)
    {
        foreach ($options as $optionLabel => $optionValue) {
            if ($attribute !== null &&
                !in_array($attribute->getCode(), $this->getIgnoredAttributes()) &&
                $this->getOption($optionLabel, $attribute) === null
            ) {
                try {
                    $this->handleOptionNotInPimAnymore($optionValue, $attribute->getCode());
                } catch (SoapCallException $e) {
                    throw new InvalidItemException($e->getMessage(), [$optionLabel]);
                }
            }
        }
    }

    /**
     * Handle deletion or disabling of options which are not in PIM anymore.
     *
     * @param string $optionId
     * @param string $attributeCode
     *
     * @throws InvalidItemException
     */
    protected function handleOptionNotInPimAnymore($optionId, $attributeCode)
    {
        if ($this->notInPimAnymoreAction === self::DELETE) {
            try {
                $this->webservice->deleteOption($optionId, $attributeCode);
                $this->stepExecution->incrementSummaryInfo('option_deleted');
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), [$optionId]);
            }
        }
    }

    /**
     * Get attribute for attribute code.
     *
     * @param string $attributeCode
     *
     * @return AbstractAttribute
     */
    protected function getAttribute($attributeCode)
    {
        return $this->em->getRepository($this->attributeClassName)->findOneBy(['code' => $attributeCode]);
    }

    /**
     * Get option for option label and attribute.
     *
     * @param string            $optionLabel
     * @param AbstractAttribute $attribute
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeOption
     */
    protected function getOption($optionLabel, AbstractAttribute $attribute)
    {
        return $this->em->getRepository($this->optionClassName)->findOneBy(
            ['code' => $optionLabel, 'attribute' => $attribute]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        $configurationFields = parent::getConfigurationFields();

        $configurationFields['notInPimAnymoreAction']['options']['choices'] = [
            Cleaner::DO_NOTHING => 'pim_magento_connector.export.do_nothing.label',
            Cleaner::DELETE     => 'pim_magento_connector.export.delete.label',
        ];

        $configurationFields['notInPimAnymoreAction']['options']['help'] =
            'pim_magento_connector.export.notInPimAnymoreAction.help';
        $configurationFields['notInPimAnymoreAction']['options']['label'] =
            'pim_magento_connector.export.notInPimAnymoreAction.label';

        return $configurationFields;
    }

    /**
     * @return string[]
     */
    protected function getIgnoredAttributes()
    {
        return [
            'visibility',
            'tax_class_id',
        ];
    }
}
