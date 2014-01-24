<?php

namespace Pim\Bundle\MagentoConnectorBundle\Step;

use Oro\Bundle\BatchBundle\Step\AbstractStep;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\MagentoConnectorBundle\Cleaner\Cleaner;

/**
 * A step to delete element that are no longer in PIM or in the channel
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PruneStep extends AbstractStep
{
    /**
     * @var Cleaner
     */
    protected $cleaner;

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        // inject the step execution in the step item to be able to log summary info during execution
        $this->cleaner->setStepExecution($stepExecution);
        $this->cleaner->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configuration = array();
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                foreach ($stepElement->getConfiguration() as $key => $value) {
                    if (!isset($configuration[$key]) || $value) {
                        $configuration[$key] = $value;
                    }
                }
            }
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                $stepElement->setConfiguration($config);
            }
        }
    }

    /**
     * Get cleaner
     * @return Cleaner
     */
    public function getCleaner()
    {
        return $this->cleaner;
    }

    /**
     * Get cleaner
     * @param Cleaner $cleaner
     */
    public function setCleaner(Cleaner $cleaner)
    {
        $this->cleaner= $cleaner;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableStepElements()
    {
        return array('cleaner' => $this->getCleaner());
    }
}