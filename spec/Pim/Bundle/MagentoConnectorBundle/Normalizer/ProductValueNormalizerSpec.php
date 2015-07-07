<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\Metric;
use PhpSpec\ObjectBehavior;

class ProductValueNormalizerSpec extends ObjectBehavior
{
    protected $globalContext;

    function let(
        ProductValue $value,
        MappingCollection $attributeMapping,
        AbstractAttribute $attribute
    ) {
        $this->globalContext = [
            'identifier'               => 'identifier',
            'scopeCode'                => 'scope_code',
            'localeCode'               => 'locale_code',
            'onlyLocalized'            => false,
            'magentoAttributes'        => ['attribute_code' => ['code' => 'attribute_ode', 'scope' => 'global']],
            'magentoAttributesOptions' => [],
            'currencyCode'             => 'currency_code',
            'attributeCodeMapping'     => $attributeMapping,
        ];

        $attributeMapping->getTarget('attribute_code')->willReturn('attribute_code');
        $value->getData()->willReturn('hello');
        $value->getAttribute()->willReturn($attribute);
        $value->getScope()->willReturn('scope_code');
        $attribute->getCode()->willReturn('attribute_code');
    }

    function it_normalizes_a_scopable_value($value, $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => 'hello']);
    }

    function it_normalizes_a_non_scopable_value($value, $attribute)
    {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => 'hello']);
    }

    function it_normalizes_a_localizable_value($value, $attribute)
    {
        $this->globalContext['magentoAttributes'] = ['attribute_code' => ['code' => 'attribute_ode', 'scope' => 'store']];

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('locale_code');

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => 'hello']);
    }

    function it_does_not_normalize_a_localizable_value_with_a_different_locale_than_current($value, $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('en_US');

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(null);
    }

    function it_raises_an_exception_if_scope_are_not_corresponding($value, $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $value->getLocale()->willReturn('locale_code');

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidScopeMatchException')->during('normalize', [$value, 'MagentoArray', $this->globalContext]);
    }

    function it_normalizes_a_localizable_value_when_only_localizable_values_are_requested($value, $attribute)
    {
        $this->globalContext['onlyLocalized'] = true;
        $this->globalContext['magentoAttributes'] = ['attribute_code' => ['code' => 'attribute_ode', 'scope' => 'store']];

        $attribute->isScopable()->willReturn(false);
        $value->getLocale()->willReturn('locale_code');

        $attribute->isLocalizable()->willReturn(true);

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => 'hello']);
    }

    function it_does_not_normalizes_a_non_localizable_value_when_only_localizable_values_are_requested($value, $attribute)
    {
        $this->globalContext['onlyLocalized'] = true;
        $this->globalContext['magentoAttributes'] = ['attribute_code' => ['code' => 'attribute_ode', 'scope' => 'store']];

        $attribute->isScopable()->willReturn(false);
        $value->getLocale()->willReturn('locale_code');

        $attribute->isLocalizable()->willReturn(false);

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(null);
    }

    function it_raises_an_exception_if_attribute_does_not_exists($value, $attribute)
    {
        $this->globalContext['magentoAttributes'] = [];
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\AttributeNotFoundException')->during('normalize', [$value, 'MagentoArray', $this->globalContext]);
    }

    function it_normalizes_a_true_boolean_value($value, $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn(true);

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => true]);
    }

    function it_normalizes_a_false_boolean_value($value, $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn(false);

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => false]);
    }

    function it_normalizes_a_date_value($value, $attribute)
    {
        $date = new \DateTime('2000-01-01');

        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn($date);

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => '2000-01-01 00:00:00']);
    }

    function it_normalizes_an_option_value(
        $value,
        $attribute,
        AttributeOption $option
    ) {
        $this->globalContext['magentoAttributesOptions'] = ['attribute_code' => ['option_code' => 'option_id']];
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn($option);
        $option->getCode()->willReturn('option_code');

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => 'option_id']);
    }

    function it_raises_an_exception_if_an_option_value_does_not_exists_in_magento(
        $value,
        $attribute,
        AttributeOption $option
    ) {
        $this->globalContext['magentoAttributesOptions'] = ['attribute_code' => ['option_code2' => 'option_id']];
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn($option);
        $option->getCode()->willReturn('option_code');

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidOptionException')->during('normalize', [$value, 'MagentoArray', $this->globalContext]);
    }

    function it_normalizes_a_collection_of_option_values(
        $value,
        $attribute,
        AttributeOption $option1,
        AttributeOption $option2
    ) {
        $this->globalContext['magentoAttributesOptions'] = ['attribute_code' => ['option_code_1' => 'option_id_1', 'option_code_2' => 'option_id_2']];
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn([$option1, $option2]);
        $option1->getCode()->willReturn('option_code_1');
        $option2->getCode()->willReturn('option_code_2');

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => ['option_id_1', 'option_id_2']]);
    }

    function it_normalizes_a_collection_of_product_prices(
        $value,
        $attribute,
        ProductPrice $price1,
        ProductPrice $price2
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn([$price1, $price2]);
        $price1->getData()->willReturn('10.0');
        $price1->getCurrency()->willReturn('EUR');
        $price2->getData()->willReturn('12.0');
        $price2->getCurrency()->willReturn('currency_code');

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => '12.0']);
    }

    function it_normalizes_a_collection_of_simple_values($value, $attribute)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn(['foo', 'bar']);

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => ['foo', 'bar']]);
    }

    function it_normalizes_a_metric($value, $attribute, Metric $metric)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);

        $value->getData()->willReturn($metric);
        $metric->getData()->willReturn('metric');

        $this->normalize($value, 'MagentoArray', $this->globalContext)->shouldReturn(['attribute_code' => 'metric']);
    }
}
