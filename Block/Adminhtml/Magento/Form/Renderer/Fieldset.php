<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Form\Renderer;

use Magento\Backend\Block\Widget\Form\Renderer\Fieldset as MagentoFieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Fieldset extends MagentoFieldset
{
    protected function getTooltipHtml($content, $directionClass)
    {
        return <<<HTML
<div class="TikTokShop-field-tooltip TikTokShop-field-tooltip-{$directionClass}
TikTokShop-fieldset-tooltip admin__field-tooltip">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content">
        {$content}
    </div>
</div>
HTML;
    }

    public function render(AbstractElement $element)
    {
        $element->addClass('TikTokShop-fieldset');

        $tooltip = $element->getData('tooltip');

        if ($tooltip === null) {
            return parent::render($element);
        }

        $element->addField(
            'help_block_' . $element->getId(),
            \M2E\TikTokShop\Block\Adminhtml\Magento\Form\AbstractForm::HELP_BLOCK,
            [
                'content' => $tooltip,
                'tooltiped' => true,
            ],
            '^'
        );

        $directionClass = $element->getData('direction_class');

        $element->setLegend(
            $element->getLegend() . $this->getTooltipHtml($tooltip, empty($directionClass) ? 'right' : $directionClass)
        );

        return parent::render($element);
    }

    /**
     * @param array|string $data
     * @param null $allowedTags
     *
     * @return array|string
     * @throws \M2E\TikTokShop\Model\Exception\Logic
     * Starting from version 2.2.3 Magento forcibly escapes content of tooltips. But we are using HTML there
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return \M2E\TikTokShop\Helper\Data::escapeHtml(
            $data,
            ['div', 'a', 'strong', 'br', 'i', 'b', 'ul', 'li', 'p'],
            ENT_NOQUOTES
        );
    }
}
