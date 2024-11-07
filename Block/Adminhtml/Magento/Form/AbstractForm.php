<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Form;

use Magento\Backend\Block\Widget\Form\Generic;
use M2E\TikTokShop\Block\Adminhtml\Traits;
use M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\CustomContainer;
use M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\HelpBlock;
use M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\Messages;
use M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\Select;
use M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\Separator;
use M2E\TikTokShop\Block\Adminhtml\Magento\Form\Element\StoreSwitcher;

abstract class AbstractForm extends Generic
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    public const CUSTOM_CONTAINER = CustomContainer::class;
    public const HELP_BLOCK = HelpBlock::class;
    public const MESSAGES = Messages::class;
    public const SELECT = Select::class;
    public const SEPARATOR = Separator::class;

    public const STORE_SWITCHER = StoreSwitcher::class;

    /** @var \Magento\Framework\Data\Form\Element\Factory */
    protected $elementFactory;

    /** @var \Magento\Cms\Model\Wysiwyg\Config */
    protected $wysiwygConfig;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->wysiwygConfig = $context->getWysiwygConfig();

        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        $this->elementFactory = $context->getElementFactory();

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        \Magento\Framework\Data\Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Form\Renderer\Element::class)
        );

        \Magento\Framework\Data\Form::setFieldsetRenderer(
            $this->getLayout()->createBlock(\M2E\TikTokShop\Block\Adminhtml\Magento\Form\Renderer\Fieldset::class)
        );

        return $this;
    }
}
