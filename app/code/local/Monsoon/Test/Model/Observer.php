<?php
/**
 * Class Monsoon_Test_Model_Observer
 */
class Monsoon_Test_Model_Observer extends Mage_Catalog_Model_Observer {

    const NOLINK = 'javascript:;';

    protected $unClickableIds;

    /**
     * customizeTopMenu observer - called on page_block_html_topmenu_gethtml_before in config.xml
     *
     * @param   Varien_Event_Observer $observer
     */

    public function customizeTopMenu(Varien_Event_Observer $observer) {
        $block = $observer->getEvent()->getBlock();
        $block->addCacheTag(Mage_Catalog_Model_Category::CACHE_TAG);

        //Get collection of unclickable category ids in the menu
        if(null !== Mage::getModel('catalog/resource_eav_attribute')->loadByCode('catalog_category','unclickable_menu_category')->getId()) {
            $collection = Mage::getModel('catalog/category')->getCollection();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('unclickable_menu_category', array('eq' => 1));
            $this->unClickableIds = $collection->getAllIds();
        }

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
        //Get collection of unclickable category ids in the menu

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

            //Set javascript:; as category url if category attribute is unclickable
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                'url' => in_array($category->getId(),$this->unClickableIds) ? (self::NOLINK) : Mage::helper('catalog/category')->getCategoryUrl($category),
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