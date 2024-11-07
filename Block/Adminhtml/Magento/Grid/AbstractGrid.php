<?php

namespace M2E\TikTokShop\Block\Adminhtml\Magento\Grid;

use Magento\Backend\Block\Widget\Grid\Extended;
use M2E\TikTokShop\Block\Adminhtml\Traits;

abstract class AbstractGrid extends Extended
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    protected $_template = 'magento/grid/extended.phtml';
    protected bool $customPageSize = false;

    public function __construct(
        \M2E\TikTokShop\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        parent::__construct($context, $backendHelper, $data);
    }

    // ----------------------------------------

    public function addColumn($columnId, $column)
    {
        if (is_array($column)) {
            if (!array_key_exists('header_css_class', $column)) {
                $column['header_css_class'] = 'grid-listing-column-' . $columnId;
            }

            if (!array_key_exists('column_css_class', $column)) {
                $column['column_css_class'] = 'grid-listing-column-' . $columnId;
            }
        }

        if (is_array($column)) {
            $this->getColumnSet()->setChild(
                $columnId,
                $this->getLayout()
                     ->createBlock(\M2E\TikTokShop\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite::class)
                     ->setData($column)
                     ->setId($columnId)
                     ->setGrid($this)
            );
            $this->getColumnSet()->getChildBlock($columnId)->setGrid($this);
        } else {
            throw new \Exception((string)__('Please correct the column format and try again.'));
        }

        $this->_lastColumnId = $columnId;

        return $this;
    }

    public function getMassactionBlockName(): string
    {
        return \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Massaction::class;
    }

    // ----------------------------------------

    public function isAllowedCustomPageSize(): bool
    {
        return $this->customPageSize;
    }

    public function setCustomPageSize($value): self
    {
        $this->customPageSize = $value;

        return $this;
    }

    // ----------------------------------------

    public function getCsv(): string
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = [];
        foreach ($this->getColumns() as $column) {
            if (!$column->getIsSystem()) {
                $data[] = '"' . $column->getExportHeader() . '"';
            }
        }
        $csv .= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $item) {
            $data = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $exportField = (string)$column->getRowFieldExport($item);
                    $data[] = '"' . str_replace(
                        ['"', '\\'],
                        ['""', '\\\\'],
                        is_numeric($exportField) ? $exportField : ($exportField ?: '')
                    ) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        if ($this->getCountTotals()) {
            $data = [];
            foreach ($this->getColumns() as $column) {
                if (!$column->getIsSystem()) {
                    $data[] = '"' . str_replace(
                        ['"', '\\'],
                        ['""', '\\\\'],
                        $column->getRowFieldExport($this->getTotals()) ?: ''
                    ) . '"';
                }
            }
            $csv .= implode(',', $data) . "\n";
        }

        return $csv;
    }

    /**
     * @return \M2E\TikTokShop\Block\Adminhtml\Magento\Grid\Massaction
     */
    public function getMassactionBlock()
    {
        return parent::getMassactionBlock();
    }

    public function setId(string $id): self
    {
        $this->setData('id', $id);

        return $this;
    }

    public function setUseAjax(bool $useAjax): self
    {
        $this->setData('use_ajax', $useAjax);

        return $this;
    }
}
