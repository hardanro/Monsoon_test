<?php
/**
 * Class Monsoon_Test_Model_Observer
 */
class Monsoon_Test_Model_Observer extends Mage_Catalog_Model_Observer {

    const NOLINK = 'javascript:;';

    /**
     * customizeTopMenu observer - called on page_block_html_topmenu_gethtml_before in config.xml
     *
     * @param   Varien_Event_Observer $observer
     */

    public function customizeTopMenu(Varien_Event_Observer $observer) {
        $block = $observer->getEvent()->getBlock();
        $block->addCacheTag(Mage_Catalog_Model_Category::CACHE_TAG);
        $this->_addCategoriesToMenu(
            Mage::helper('catalog/category')->getStoreCategories(), $observer->getMenu(), $block, true
        );

    }

    /**
     * Override Mage_Catalog_Model_Observer _addCategoriesToMenu method to remove the links for the categories having unclickable attribute set
     *
     * @param Varien_Data_Tree_Node_Collection|array $categories
     * @param Varien_Data_Tree_Node $parentCategoryNode
     * @param Mage_Page_Block_Html_Topmenu $menuBlock
     * @param bool $addTags
     */

    protected function _addCategoriesToMenu($categories, $parentCategoryNode, $menuBlock, $addTags = false)
    {
        $categoryModel = Mage::getModel('catalog/category');
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $categoryModel->setId($category->getId());
            if ($addTags) {
                $menuBlock->addModelTags($categoryModel);
            }

            $tree = $parentCategoryNode->getTree();

            //See if the category is set as unclickable in admin
            $unClickable = Mage::getModel('catalog/category')->load($category->getId())->getData('unclickable_menu_category');

            //Set javascript:; as category url if category attribute is unclickable
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                'url' => !empty($unClickable) ? (self::NOLINK) : Mage::helper('catalog/category')->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category)
            );

            $categoryNode = new Varien_Data_Tree_Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            $flatHelper = Mage::helper('catalog/category_flat');
            if ($flatHelper->isEnabled() && $flatHelper->isBuilt(true)) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode, $menuBlock, $addTags);
        }
    }

}