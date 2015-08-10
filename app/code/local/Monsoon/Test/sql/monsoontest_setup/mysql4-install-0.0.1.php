<?php
/**
 * Install script
 * Creating a new attribute called unclickable_menu_category
 * It is changable in admin for every category
 * If it's true, the category will not be clickable in the navigation menu
 */
$installer = $this;
$installer->startSetup();

$attribute = array(
    'type'      => 'int',
    'label'     => 'Unclickable in navigation menu',
    'input'     => 'select',
    'visible'   => true,
    'required'  => false,
    'default'   => 1,
    'group'     => "General Information",
    'source'    => 'eav/entity_attribute_source_boolean',
    'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'  => false,
    'default'   => 0,
    'note'      => 'Where or not the category will not be unclickable in frontend navigation menu.'
);
$installer->addAttribute('catalog_category', 'unclickable_menu_category', $attribute);
$installer->endSetup();