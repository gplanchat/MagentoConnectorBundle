<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CategoryWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        CategoryMappingManager $categoryMappingManager,
        Webservice $webservice,
        StepExecution $stepExecution,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        MagentoSoapClientParameters $clientParameters
    ) {
        $clientParametersRegistry->getInstance(null, null, null, '/api/soap/?wsdl', 'default', null, null)->willReturn($clientParameters);
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);

        $this->beConstructedWith($webserviceGuesser, $categoryMappingManager, $clientParametersRegistry);
        $this->setStepExecution($stepExecution);
    }

    function it_sends_categories_to_create_on_magento_webservice(
        Category $category,
        $webservice,
        $categoryMappingManager
    ) {
        $batches = array(
            array(
                'create' => array(
                    array(
                        'pimCategory'     => $category,
                        'magentoCategory' => array('foo')
                    )
                )
            )
        );
        $webservice->sendNewCategory(array('foo'))->willReturn(12);
        $categoryMappingManager
                ->registerCategoryMapping($category, 12, MagentoSoapClientParameters::SOAP_WSDL_URL)
                ->shouldBeCalled();

        $this->setMagentoUrl(null);
        $this->write($batches);
    }

    function it_sends_categories_to_update_on_magento_webservice(
        Category $category,
        $webservice
    ) {
        $batches = array(
            array(
                'update' => array(
                    array('foo')
                )
            )
        );

        $webservice->sendUpdateCategory(array('foo'))->shouldBeCalled();

        $this->write($batches);
    }

    function it_sends_categories_to_move_on_magento_webservice(
        Category $category,
        $webservice
    ) {
        $batches = array(
            array(
                'move' => array(
                    array('foo')
                )
            )
        );

        $webservice->sendMoveCategory(array('foo'))->shouldBeCalled();

        $this->write($batches);
    }

    function it_sends_categories_to_update_variation_on_magento_webservice(
        Category $category,
        $webservice,
        $categoryMappingManager
    ) {
        $batches = array(
            array(
                'variation' => array(
                    array(
                        'pimCategory'     => $category,
                        'magentoCategory' => array('foo')
                    )
                )
            )
        );

        $categoryMappingManager
                ->getIdFromCategory($category, MagentoSoapClientParameters::SOAP_WSDL_URL)
                ->willReturn(12);

        $webservice->sendUpdateCategory(array(12))->shouldBeCalled();

        $this->setMagentoUrl(null);
        $this->write($batches);
    }

    function it_throws_an_exception_if_something_went_wrong_with_magento_calls(
        Category $category,
        $webservice,
        $categoryMappingManager
    ) {
        $batches = array(
            array(
                'create' => array(
                    array(
                        'pimCategory'     => $category,
                        'magentoCategory' => array('foo')
                    )
                )
            )
        );
        $webservice->sendNewCategory(array('foo'))->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');
        $categoryMappingManager
                ->registerCategoryMapping(Argument::cetera())
                ->shouldNotBeCalled();

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringWrite($batches);
    }
}
