<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Magento family cleaner.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyCleaner extends Cleaner
{
    /** @var FamilyMappingManager */
    protected $familyMappingManager;

    /** @var boolean */
    protected $forceAttributeSetRemoval;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param FamilyMappingManager                $familyMappingManager
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->familyMappingManager = $familyMappingManager;
    }

    /**
     * @return boolean
     */
    public function isForceAttributeSetRemoval()
    {
        return $this->forceAttributeSetRemoval;
    }

    /**
     * @param boolean $forceAttributeSetRemoval
     *
     * @return FamilyCleaner
     */
    public function setForceAttributeSetRemoval($forceAttributeSetRemoval)
    {
        $this->forceAttributeSetRemoval = $forceAttributeSetRemoval;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoFamilies = $this->webservice->getAttributeSetList();

        foreach ($magentoFamilies as $name => $id) {
            try {
                $this->handleFamilyNotInPimAnymore($name, $id);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), [$name]);
            }
        }
    }

    /**
     * Handle deletion of families that are not in PIM anymore.
     *
     * @param string $name
     * @param int    $id
     *
     * @throws InvalidItemException
     */
    protected function handleFamilyNotInPimAnymore($name, $id)
    {
        if (
            $this->notInPimAnymoreAction === self::DELETE &&
            !$this->familyMappingManager->magentoFamilyExists($id, $this->getSoapUrl()) &&
            !in_array($name, $this->getIgnoredFamilies())
        ) {
            try {
                $this->webservice->removeAttributeSet(
                    $id,
                    $this->forceAttributeSetRemoval
                );
                $this->stepExecution->incrementSummaryInfo('family_deleted');
            } catch (SoapCallException $e) {
                throw new InvalidItemException(
                    $e->getMessage(),
                    [$id],
                    [$e]
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
                'notInPimAnymoreAction' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => [
                            Cleaner::DO_NOTHING => 'pim_magento_connector.export.do_nothing.label',
                            Cleaner::DELETE     => 'pim_magento_connector.export.delete.label',
                        ],
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.notInPimAnymoreAction.help',
                        'label'    => 'pim_magento_connector.export.notInPimAnymoreAction.label',
                        'attr'     => ['class' => 'select2'],
                    ],
                ],
                'forceAttributeSetRemoval' => [
                    'type' => 'checkbox',
                    'options' => [
                        'help' => 'pim_magento_connector.export.forceAttributeSetRemoval.help',
                        'label' => 'pim_magento_connector.export.forceAttributeSetRemoval.label',
                    ],
                ],
            ]
        );
    }

    /**
     * Get all ignored families.
     *
     * @return string[]
     */
    protected function getIgnoredFamilies()
    {
        return [
            'Default',
        ];
    }
}
