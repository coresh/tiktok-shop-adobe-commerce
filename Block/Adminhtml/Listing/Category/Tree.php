<?php

namespace M2E\TikTokShop\Block\Adminhtml\Listing\Category;

class Tree extends \M2E\TikTokShop\Block\Adminhtml\Magento\Category\AbstractCategory
{
    public bool $_isAjax = false;
    protected $_template = 'listing/category/tree.phtml';

    protected array $_selectedCategories = [];
    protected array $_highlightedCategories = [];
    protected $_callback = null;

    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingCategoryTree');
        // ---------------------------------------

        $this->_isAjax = $this->getRequest()->isXmlHttpRequest();
    }

    //########################################

    public function getSelectedCategories()
    {
        return $this->_selectedCategories;
    }

    public function setSelectedCategories($categories)
    {
        $this->_selectedCategories = $categories;

        return $this;
    }

    public function getHighlightedCategories()
    {
        return $this->_highlightedCategories;
    }

    public function setHighlightedCategories($categories)
    {
        $this->_highlightedCategories = $categories;

        return $this;
    }

    public function getCallback()
    {
        return $this->_callback;
    }

    public function setCallback($callback)
    {
        $this->_callback = $callback;

        return $this;
    }

    //########################################

    public function buildNodeName($node)
    {
        return $this->escapeHtml($node->getName());
    }

    public function getTreeJson($parentNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parentNodeCategory, 0));

        return \M2E\Core\Helper\Json::encode($rootArray['children'] ?? []);
    }

    public function _getNodeJson($node, $level = 0)
    {
        if (is_array($node)) {
            $node = new \Magento\Framework\Data\Tree\Node($node, 'entity_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['text'] = $this->buildNodeName($node);
        $item['id'] = $node->getId();
        $item['allowDrop'] = false;

        if ((int)$node->getChildrenCount() > 0) {
            $item['children'] = [];
        }

        $isParent = false;
        if ($node->hasChildren()) {
            $item['children'] = [];
            if (!($this->getUseAjax() && $node->getLevel() > 1 )) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level + 1);
                }
            }
        }

        if ($node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    //########################################
}
