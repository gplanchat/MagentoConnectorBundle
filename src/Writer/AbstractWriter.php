<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;

/**
 * Magento product writer.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractWriter extends MagentoItemStep implements ItemWriterInterface
{
}
