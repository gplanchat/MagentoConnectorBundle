<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\Completeness;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ProductAssociationProcessorSpec extends ObjectBehavior
{
    function let(
        LocaleManager $localeManager,
        MagentoMappingMerger $storeViewMappingMerger,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        AssociationTypeManager $associationTypeManager,
        Webservice $webservice,
        StepExecution $stepExecution,
        EventDispatcher $eventDispatcher,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $associationTypeManager,
            $clientParametersRegistry
        );
        $this->setStepExecution($stepExecution);
        $this->setEventDispatcher($eventDispatcher);

        $clientParametersRegistry->getInstance(null, null, null, '/api/soap/?wsdl', 'default', null, null)->willReturn(
            $clientParameters
        );
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);

        $this->setPimUpSell('UPSELL');
    }

    function it_generates_association_calls_for_given_products_if_associated_product_is_complete_and_enabled(
        $webservice,
        ProductInterface $product,
        ProductInterface $associatedProduct,
        Association $association,
        AssociationType $associationType,
        Completeness $completeness
    ) {
        $webservice->getAssociationsStatus($product)->willReturn(
            ['up_sell' => [], 'cross_sell' => [['sku' => 'sku-011']], 'related' => []]
        );

        $product->getIdentifier()->willReturn('sku-012');
        $product->getAssociations()->willReturn([$association]);

        $association->getAssociationType()->willReturn($associationType);
        $association->getProducts()->willReturn([$associatedProduct]);

        $associatedProduct->getIdentifier()->willReturn('sku-011');
        $associatedProduct->isEnabled()->willReturn(true);
        $associatedProduct->getCompletenesses()->willReturn([$completeness]);
        $completeness->getRatio()->willReturn(100);

        $associationType->getCode()->willReturn('UPSELL');

        $this->process([$product])->shouldReturn(
            [
                'remove' => [
                    [
                        'type'           => 'cross_sell',
                        'product'        => 'sku-012',
                        'linkedProduct'  => 'sku-011',
                        'identifierType' => 'sku',
                    ],
                ],
                'create' => [
                    [
                        'type'           => 'up_sell',
                        'product'        => 'sku-012',
                        'linkedProduct'  => 'sku-011',
                        'data'           => [],
                        'identifierType' => 'sku',
                    ],
                ],
            ]
        );
    }

    function it_does_not_generate_association_calls_for_given_products_if_associated_product_is_not_enabled(
        $webservice,
        ProductInterface $product,
        ProductInterface $associatedProduct,
        Association $association,
        AssociationType $associationType,
        Completeness $completeness
    ) {
        $webservice->getAssociationsStatus($product)->willReturn(
            ['up_sell' => [], 'cross_sell' => [['sku' => 'sku-011']], 'related' => []]
        );

        $product->getIdentifier()->willReturn('sku-012');
        $product->getAssociations()->willReturn([$association]);

        $association->getAssociationType()->willReturn($associationType);
        $association->getProducts()->willReturn([$associatedProduct]);

        $associatedProduct->getIdentifier()->willReturn('sku-011');
        $associatedProduct->isEnabled()->willReturn(false);
        $associatedProduct->getCompletenesses()->willReturn([$completeness]);
        $completeness->getRatio()->willReturn(100);

        $associationType->getCode()->willReturn('UPSELL');

        $this->process([$product])->shouldReturn(
            [
                'remove' => [
                    [
                        'type'           => 'cross_sell',
                        'product'        => 'sku-012',
                        'linkedProduct'  => 'sku-011',
                        'identifierType' => 'sku',
                    ],
                ],
                'create' => [],
            ]
        );
    }

    function it_does_not_generate_association_calls_for_given_products_if_associated_product_is_not_complete(
        $webservice,
        ProductInterface $product,
        ProductInterface $associatedProduct,
        Association $association,
        AssociationType $associationType,
        Completeness $completeness
    ) {
        $webservice->getAssociationsStatus($product)->willReturn(
            ['up_sell' => [], 'cross_sell' => [['sku' => 'sku-011']], 'related' => []]
        );

        $product->getIdentifier()->willReturn('sku-012');
        $product->getAssociations()->willReturn([$association]);

        $association->getAssociationType()->willReturn($associationType);
        $association->getProducts()->willReturn([$associatedProduct]);

        $associatedProduct->getIdentifier()->willReturn('sku-011');
        $associatedProduct->isEnabled()->willReturn(true);
        $associatedProduct->getCompletenesses()->willReturn([$completeness]);
        $completeness->getRatio()->willReturn(50);

        $associationType->getCode()->willReturn('UPSELL');

        $this->process([$product])->shouldReturn(
            [
                'remove' => [
                    [
                        'type'           => 'cross_sell',
                        'product'        => 'sku-012',
                        'linkedProduct'  => 'sku-011',
                        'identifierType' => 'sku',
                    ],
                ],
                'create' => [],
            ]
        );
    }

    function it_does_not_generate_association_calls_for_given_products_if_associated_product_is_not_complete_nor_enable(
        $webservice,
        ProductInterface $product,
        ProductInterface $associatedProduct,
        Association $association,
        AssociationType $associationType,
        Completeness $completeness
    ) {
        $webservice->getAssociationsStatus($product)->willReturn(
            ['up_sell' => [], 'cross_sell' => [['sku' => 'sku-011']], 'related' => []]
        );

        $product->getIdentifier()->willReturn('sku-012');
        $product->getAssociations()->willReturn([$association]);

        $association->getAssociationType()->willReturn($associationType);
        $association->getProducts()->willReturn([$associatedProduct]);

        $associatedProduct->getIdentifier()->willReturn('sku-011');
        $associatedProduct->isEnabled()->willReturn(false);
        $associatedProduct->getCompletenesses()->willReturn([$completeness]);
        $completeness->getRatio()->willReturn(50);

        $associationType->getCode()->willReturn('UPSELL');

        $this->process([$product])->shouldReturn(
            [
                'remove' => [
                    [
                        'type'           => 'cross_sell',
                        'product'        => 'sku-012',
                        'linkedProduct'  => 'sku-011',
                        'identifierType' => 'sku',
                    ],
                ],
                'create' => [],
            ]
        );
    }

    function it_throws_an_exception_if_something_went_wrong_with_soap_call(
        $webservice,
        $eventDispatcher,
        ProductInterface $product
    ) {
        $webservice
            ->getAssociationsStatus($product)
            ->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');

        $eventDispatcher
            ->dispatch(
                EventInterface::INVALID_ITEM,
                Argument::type('Akeneo\Bundle\BatchBundle\Event\InvalidItemEvent')
            )
            ->shouldBeCalled();

        $this->process($product);
    }

    function it_is_configurable()
    {
        $this->setPimUpSell('foo');
        $this->setPimCrossSell('bar');
        $this->setPimRelated('fooo');
        $this->setPimGrouped('baar');

        $this->getPimUpSell()->shouldReturn('foo');
        $this->getPimCrossSell()->shouldReturn('bar');
        $this->getPimRelated()->shouldReturn('fooo');
        $this->getPimGrouped()->shouldReturn('baar');
    }
}
