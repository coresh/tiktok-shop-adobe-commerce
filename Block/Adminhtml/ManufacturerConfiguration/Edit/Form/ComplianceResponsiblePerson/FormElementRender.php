<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Block\Adminhtml\ManufacturerConfiguration\Edit\Form\ComplianceResponsiblePerson;

class FormElementRender extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    protected $_template = 'manufacturer_configuration/responsible_persons.phtml';

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
