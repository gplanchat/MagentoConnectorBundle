# 1.2.1 (2015-03-27)
## Improvement
 - Add a tax_class_id attribute to fixtures
 - Add job_execution.summary.displayed missing translations

## Bug Fix
 - 464: Fix storage driver getter

# 1.2.0 (2015-03-12)
## New feature
 - Delta Export is directly integrated in MagentoConnector (DeltaExportBundle is now deprecated)
 - Connector Mapping is directly integrated in MagentoConnector (ConnectorMappingBundle is now deprecated)
 - Attribute, Category, Family and Option normalizers are now in the DI
 - Category export only create/update categories from the exported channel
 - Make it compatible with Magento EE-1.11 and EE-1.12
 - Add a log to profile calls to Magento
 - Add a command to purge Mapping in database
 - Add a command to purge Delta in database
 - MongoDB compliance
 - Akeneo PIM CE 1.3 compliance
 - Improve fixtures set

## Bug fixes
 - Connector send products only with complete locales

## BC Breaks
 - All DeltaExportBundle dependencies should be replaced by MagentoConnectorBundle ones
 - All ConnectorMappingBundle dependencies should be replaced by MagentoConnectorBundle ones
 - Inject AttributeNormalizer, CategoryNormalizer, FamilyNormalizer and OptionNormalizer in the NormalizerGuesser (MC-98)
 - magento_attribute_export, magento_option_export and magento_attributeset_export has been removed. These jobs are deprecated because they should be launch in a specific order
 - Categories export step has been removed from structure export
 - Inject ChannelManager inside CategoryReader
 - CategoryRepository::findOrderCategories takes a CategoryInterface
 - pim_base_connector.reader.doctrine.obsoleteproduct, pim_base_connector.reader.doctrine.obsoleteproduct.class, pim_magento_connector.reader.doctrine.product has been removed from readers.yml because they are unused
 - Remove defaultLocale argument from OptionNormalizer::getOptionLabel
 - Rename parameter `pim_magento_connector.reader.orm.delta_product.class` to `pim_magento_connector.reader.delta_product.class`
 - Rename parameter `pim_magento_connector.reader.orm.delta_product_association.class` to `pim_magento_connector.reader.delta_product_association.class`
 - Rename parameter `pim_magento_connector.reader.orm.delta_product.class` to `pim_magento_connector.reader.delta_product.class`
 - Set ProductCleaner parameters in a better order
 - Add ExportableLocaleFilter parameter in ProductNormalizer
 - Add ExportableLocaleFilter parameter in ProductNormalizer16
 - Add ExportableLocaleFilter parameter in NormalizerGuesser
 - Remove ProductValueManager class (used only to calculate default value which is not a feature anymore)
 - Remove ProductValueManager injection in NormalizerGuesser and AttributeNormalizer
 - Add translations for jobs: `pim_base_connector.jobs.magento_attribute_export.title`, `pim_base_connector.jobs.magento_category_export.title`, and `pim_base_connector.jobs.magento_product_export.title`

# 1.1.24 (2015-03-09)
## Bug fix
 - Remove url_key from product updates with Magento EE

# 1.1.23 (2015-03-06)
## Bug fixes
 - Prevent an error with url_key during a product update on Magento EE
 - Fix PHP notice about pimGrouped
 
# 1.1.22 (2015-03-04)
## Bug fix
 - Prevent empty url_key field in normalized category

# 1.1.21 (2015-02-27)
## Bug fixes
 - Fix mapping management to handle multiple magento environment on the same PIM application installation.

## BC Breaks
 - The structure of the mapping tables (`pim_magento_attribute_mapping`, `pim_magento_category_mapping`,
`pim_magento_family_mapping`, `pim_magento_group_mapping`) in the database have been changed:
    - For each of them the `magento_url` column has been changed from `tinytext` to a `varchar(255)`
    - For the `pim_magento_attribute_mapping` the index on `attribute_id` has been replaced by an index on
`attribute_id` and `magento_url`
    - For the `pim_magento_category_mapping` the index on `category_id` has been replaced by an index on
`category_id` and `magento_url`
    - For the `pim_magento_family_mapping` the index on `family_id` has been replaced by an index on
`family_id` and `magento_url`
    - For the `pim_magento_group_mapping` an index has been added on `pim_group_code`, `pim_family_code` and
`magento_url`

# 1.1.20 (2015-02-20)
## Bug fixes
 - Update product and category url keys in admin store view when multiple magento store views are used.

# 1.1.19 (2015-02-20)
## New features
 - Add an option to let Magento handle product url keys.
 - Add an option to add sku first in the product url key.

## Bug fixes
 - Category url keys are now correctly handled when multiple magento store views are used.
 - Url key are now send on product update.

# 1.1.18 (2015-02-19)
## Bug fixes
 - Remove useless attributes fields in attribute normalizer

# 1.1.17 (2015-02-16)
## Bug fixes
 - Prevent error during product creation if sku is a number.
 
# 1.1.16 (2015-02-13)
## Bug fixes
 - Url_key are now correctly handled when multiple magento store views are used.

# 1.1.15 (2015-02-04)
## Bug fixes
 - Manage now properly boolean values.

# 1.1.14 (2015-02-04)
## Bug fixes
 - Fix summary info on configurable to get variant group id and label.

# 1.1.13 (2015-01-30)
## New features
 - Add an option to prevent or allow removal of products with type non managed by Akeneo.

## Bug fixes
 - Fix price computation on configurable product export when variant axis option code is numeric

# 1.1.12 (2015-01-09)
## Bug fixes
 - When a category is moved in Akeneo but stay in the same parent category, change is now correctly passed on Magento.

# 1.1.11 (2015-01-06)
## New features
 - Add an option to avoid generating category URL_KEY and let Magento handle it.
 - Add an option to set the is_anchor property for all categories.
 - Add an option to force attribute set removal.
 - Add the SOAP URL to SoapFault error to add further diagnosis if necessary
 - Only used configurable attributes are now added to build configurable product.

## Bug fixes
 - option "Do nothing" didn't prevent removal of empty families, it now does.
 - job_execution.summary are now displayed correctly during export and in exports history.

# 1.1.8 (2014-12-01)
## Bug fixes
 - removes sending of url_key when updating product, as it breaks with Magento 1.3.1.0 (see http://www.magentocommerce.com/knowledge-base/entry/ee113-later-release-notes#ee113-11302-seo-uniqueness-rules)

## BC Breaks
 - URL key is no longer sent during product update.

# 1.1.7 (2014-11-29)
## Bug fixes
 - remove base64 image representation from error messages

# 1.1.1 (2014-11-12)
## New feature
 - url_key for products and category is generated now on Akeneo's side,
   to avoid duplicate url_key errors from the SOAP API

## Bug fixes
 - configurable images are now properly sent with their types (small, thumbnail, etc...)
 - required property on attribute conflicts with Configurables and has been removed

## BC Breaks
 - ConfigurableProcessor constructor has now an AttributeManager parameter
 - All Step elements services (writers, processors and readers) that uses the addWarning methods must
   have pim_magento_connector.item.magento_item_step has parent service
 - required property is not sent anymore to Magento, as the data is already checked

# 1.1.0 (2014-10-23)
## New feature
 - Add visibility option for products members of variant group
   for example to avoid displaying simple product only

## BC Breaks
 - ProductNormalizer and ConfigurableNormalizer constructors have now a new visibility parameter

# 1.0.1 (2014-09-30)
## Bug fixes
 - Fix association fixtures #252

# 1.0.0 (2014-09-19)

# 1.0.0-RC10 (2014-09-11)
## Bug fixes
- Fixes on media attribute when updating product

# 1.0.0-RC9 (2014-09-09)
## Bug fixes
- Fix check on Magento 1.9

# 1.0.0-RC8 -
## Bug fixes
- Product cleaner is cleaner
- Version detection fix
- Fix a bug with mappings

## Improvement
- Compatibility with pim-community 1.2.0-RC3
- Compatibility with ConnectorBundleBundle BETA-3
- Stop Compatibility with DeltaExportBundle BETA-2

## BC Breaks
- Stop compatibility with pim-community 1.1
- Stop Compatibility with ConnectorBundleBundle BETA-2
- Stop Compatibility with DeltaExportBundle BETA-1

# 1.0.0-RC7 -
## Features
- Custom entity support

## Bug fixes
- Products not assigned to an exported category are not assigned anymore

## Improvements
- Categories are now exported in the right order

# 1.0.0-RC6 -
## Bug fixes
- Fix bug with configurable product export

# 1.0.0-RC5 -
## Bug fixes
- Fix bug during localizable products export

## Improvements
- Fix some php doc
- Fix errors in README

# 1.0.0-RC4 -
- Attribute can be exported into families (AttributeSets)
- Groups can be added into AttributeSets
- Groups can be deleted
- Attribute can be removed from AttributeSets and groups
- AttributeSets can be deleted
- Add a full export job
- Add Magento v1.9 and v1.14 support

## Improvements
- Compatibility with pim-community 1.1
- Compatibility with magento enterprise edition
- delta export for products
- now use connector mapping bundle
- you can separately inform your magento url and wsdl url in export edit mode
- Added possibility to provide credential in edit mode for http authentication

# 1.0.0-RC3 -

## Features

## Improvements

- Option order on creation

## Bug fixes

- Attribute default value is now well normalized for simple and multi-selects

## BC breaks

# 1.0.0-alpha-2 -

## Features

- Added possibility to create, update and move categories
- Added possibility to export associated products' links
- Added possibility to export grouped products
- Added category assigment for simple and configurable products
- Added possibility to export options (create and remove)
- Products, categories and configurables prune after export
- Added possibility to export attributes
- Mapping system between Akeneo and Magento

## Improvements

- Price mapping validation for configurable products
- Fixtures improvements (configurables, linked products, categories, etc)
- Selects for currencies and locales
- Validation for currencies and locales
- New mappign field for attributes, storeviews and categories

## Bug fixes

- Price mapping fixes (computed price was wrong)

## BC breaks
