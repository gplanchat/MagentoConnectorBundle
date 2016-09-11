<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap client to abstract interaction with the magento api (version 1.6 and above).
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webservice16 extends Webservice
{
    /**
     * Get magento storeview list from magento.
     *
     * @return array
     */
    public function getStoreViewsList()
    {
        return [];
    }
}
