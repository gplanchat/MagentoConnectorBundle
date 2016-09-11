<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap client factory.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClientFactory
{
    /* @var MagentoSoapClientProfiler */
    protected $profiler;

    /**
     * Get a new magento soap client.
     *
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return MagentoSoapClient
     */
    public function getMagentoSoapClient(MagentoSoapClientParameters $clientParameters)
    {
        return new MagentoSoapClient($clientParameters, null, $this->profiler);
    }

    /**
     * Set MagentoSoapClientProfiler.
     *
     * @param MagentoSoapClientProfiler $profiler
     */
    public function setProfiler(MagentoSoapClientProfiler $profiler)
    {
        $this->profiler = $profiler;
    }
}
