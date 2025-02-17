<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\TikTokShop\Template\Compliance\Edit\Form\ComplianceResponsiblePerson;

class FormElementRender extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    protected $_template = 'tiktokshop/template/compliance/responsible_persons.phtml';

    protected $element;

    public function getElement()
    {
        return $this->element;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->element = $element;

        return $this->toHtml();
    }
}
