<?php

namespace M2E\TikTokShop\Block\Adminhtml\Widget;

use M2E\TikTokShop\Helper\Module;
use Magento\Framework\Math\Random;

/**
 * Class \M2E\TikTokShop\Block\Adminhtml\Widget\Info
 */
class Info extends \M2E\TikTokShop\Block\Adminhtml\Magento\AbstractBlock
{
    protected $_template = Module::IDENTIFIER . '::widget/info.phtml';

    protected $_info = [];

    /**
     * @var Random
     */
    private $randomMath;

    //########################################

    public function __construct(
        Random $random,
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->randomMath = $random;
    }

    //########################################

    public function getInfo()
    {
        return $this->_info;
    }

    public function setInfo(array $steps)
    {
        $this->_info = $steps;

        return $this;
    }

    //########################################

    public function getId()
    {
        if (!$this->hasData('id')) {
            $this->setData('id', 'id-' . $this->randomMath->getRandomString(20));
        }

        return $this->getData('id');
    }

    public function getInfoCount()
    {
        return count($this->getInfo());
    }

    public function getInfoPartWidth($index)
    {
        if (count($this->getInfo()) === 1) {
            return '100%';
        }

        return round(99 / $this->getInfoCount(), 2) . '%';
    }

    public function getInfoPartAlign($index)
    {
        if ($index === 0) {
            return 'left';
        }

        if (($this->getInfoCount() - 1) === $index) {
            return 'right';
        }

        return 'left';
    }

    //########################################

    protected function cutLongLines($line)
    {
        if (strlen($line) < 50) {
            return $line;
        }

        return substr($line, 0, 50) . '...';
    }

    //########################################
}
