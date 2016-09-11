INSERT pim_magento_attribute_mapping (id, attribute_id, magento_url, magento_attribute_id)
SELECT id, attribute_id, magento_url, magento_attribute_id
FROM pim_magento_attribute_mapping_old;

INSERT pim_magento_category_mapping (id, category_id, magento_url, magento_category_id)
SELECT id, category_id, magento_url, magento_category_id
FROM pim_magento_category_mapping_old;

INSERT pim_magento_family_mapping (id, family_id, magento_url, magento_family_id)
SELECT id, family_id, magento_url, magento_family_id
FROM pim_magento_family_mapping_old;

DROP TABLE pim_magento_attribute_mapping_old;
DROP TABLE pim_magento_category_mapping_old;
DROP TABLE pim_magento_family_mapping_old;
