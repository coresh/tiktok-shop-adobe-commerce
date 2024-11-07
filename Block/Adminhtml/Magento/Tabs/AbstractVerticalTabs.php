<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Tabs;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Magento\Tabs\AbstractVerticalTabs
 */
abstract class AbstractVerticalTabs extends AbstractTabs
{
    protected $_template = 'M2E_TikTokShop::magento/tabs/vertical.phtml';

    protected $_groups = [];

    public function getGroups()
    {
        return $this->_groups;
    }

    /**
     * Magento method
     *
     * @param string $parentTab
     *
     * @return string
     */
    public function getAccordion($parentTab)
    {
        $html = '';
        foreach ($this->_tabs as $childTab) {
            if ($childTab->getParentTab() === $parentTab->getId()) {
                $html .= $this->getChildBlock('child-tab')->setTab($childTab)->toHtml();
            }
        }

        return $html;
    }
}
