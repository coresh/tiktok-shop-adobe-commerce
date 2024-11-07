<?php

namespace M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector;

use M2E\TikTokShop\Model\ControlPanel\Inspection\FixerInterface;
use M2E\TikTokShop\Model\ControlPanel\Inspection\InspectorInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use M2E\TikTokShop\Model\ControlPanel\Inspection\Issue\Factory as IssueFactory;

class NonexistentTemplates implements InspectorInterface, FixerInterface
{
    public const FIX_ACTION_SET_NULL = 'set_null';
    public const FIX_ACTION_SET_PARENT = 'set_parent';
    public const FIX_ACTION_SET_TEMPLATE = 'set_template';

    private array $_simpleTemplates = [
        'template_category_id' => 'category',
    ];

    private array $_difficultTemplates = [
        [
            'table' => \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_TEMPLATE_SELLING_FORMAT,
            'code' => \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SELLING_FORMAT,
        ],
        [
            'table' => \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_TEMPLATE_DESCRIPTION,
            'code' => \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_DESCRIPTION,
        ],
        [
            'table' => \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_TEMPLATE_SYNCHRONIZATION,
            'code' => \M2E\TikTokShop\Model\TikTokShop\Template\Manager::TEMPLATE_SYNCHRONIZATION,
        ],
    ];

    private UrlInterface $urlBuilder;
    private ResourceConnection $resourceConnection;
    private IssueFactory $issueFactory;

    private \M2E\TikTokShop\Helper\Module\Database\Structure $dbStructureHelper;
    private \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \M2E\TikTokShop\Model\ResourceModel\Product\CollectionFactory $listingProductCollectionFactory,
        \M2E\TikTokShop\Helper\Module\Database\Structure              $dbStructureHelper,
        UrlInterface                                                  $urlBuilder,
        ResourceConnection                                            $resourceConnection,
        IssueFactory                                                  $issueFactory
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->resourceConnection = $resourceConnection;
        $this->issueFactory = $issueFactory;
        $this->dbStructureHelper = $dbStructureHelper;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    //########################################

    public function process()
    {
        $nonexistentTemplates = [];
        $issues = [];

        //foreach ($this->_simpleTemplates as $templateIdField => $templateName) {
        //    $tempResult = $this->getNonexistentTemplatesBySimpleLogic($templateName, $templateIdField);
        //    !empty($tempResult) && $nonexistentTemplates[$templateName] = $tempResult;
        //}

        foreach ($this->_difficultTemplates as $data) {
            $tableName = $data['table'];
            $code = $data['code'];
            $tempResult = $this->getNonexistentTemplatesByDifficultLogic($code, $tableName);
            if (!empty($tempResult)) {
                $nonexistentTemplates[$code] = $tempResult;
            }
        }

        if (!empty($nonexistentTemplates)) {
            $issues[] = $this->issueFactory->create(
                'Has nonexistent templates',
                $this->renderMetadata($nonexistentTemplates)
            );
        }

        return $issues;
    }

    private function renderMetadata($data)
    {
        $tableContent = <<<HTML
<tr>
    <th>Listing ID</th>
    <th>Listing Product ID</th>
    <th>Policy ID</th>
    <th>Policy ID Field</th>
    <th>My Mode</th>
    <th>Parent Mode</th>
    <th>Actions</th>
</tr>
HTML;

        $alreadyRendered = [];
        foreach ($data as $templateName => $items) {
            $tableContent .= <<<HTML
<tr>
    <td colspan="15" align="center">{$templateName}</td>
</tr>
HTML;

            foreach ($items as $index => $itemInfo) {
                $myModeWord = '--';
                $parentModeWord = '--';
                $actionsHtml = '';
                $params = [
                    'template' => $templateName,
                    'field_value' => $itemInfo['my_needed_id'],
                    'field' => $itemInfo['my_needed_id_field'],
                    'action' => 'repairNonexistentTemplates',
                ];

                if (!isset($itemInfo['my_mode']) && !isset($itemInfo['parent_mode'])) {
                    $params['action'] = self::FIX_ACTION_SET_NULL;
                    $url = $this->urlBuilder->getUrl(
                        'TikTokShop/controlPanel_module_integration/tts',
                        $params
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set null</a><br>
HTML;
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 0) {
                    $myModeWord = 'parent';
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 1) {
                    $myModeWord = 'custom';
                    $params['action'] = self::FIX_ACTION_SET_PARENT;
                    $url = $this->urlBuilder->getUrl(
                        'TikTokShop/controlPanel_module_integration_tts/repairNonexistentTemplates',
                        $params
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set parent</a><br>
HTML;
                }

                if (isset($itemInfo['my_mode']) && $itemInfo['my_mode'] == 2) {
                    $myModeWord = 'template';
                    $params['action'] = self::FIX_ACTION_SET_PARENT;
                    $url = $this->urlBuilder->getUrl(
                        'TikTokShop/controlPanel_module_integration_tts/repairNonexistentTemplates',
                        $params
                    );

                    $actionsHtml .= <<<HTML
<a href="{$url}">set parent</a><br>
HTML;
                }

                if (isset($itemInfo['parent_mode']) && $itemInfo['parent_mode'] == 1) {
                    $parentModeWord = 'custom';
                    $params['action'] = self::FIX_ACTION_SET_TEMPLATE;
                    $url = $this->urlBuilder->getUrl(
                        'TikTokShop/controlPanel_module_integration_tts/repairNonexistentTemplates',
                        $params
                    );
                    $onClick = <<<JS
var result = prompt('Enter Template ID');
if (result) {
    window.location.href = '{$url}' + '?template_id=' + result;
}
return false;
JS;
                    $actionsHtml .= <<<HTML
<a href="javascript:void();" onclick="{$onClick}">set template</a><br>
HTML;
                }

                if (isset($itemInfo['parent_mode']) && $itemInfo['parent_mode'] == 2) {
                    $parentModeWord = 'template';
                    $params['action'] = self::FIX_ACTION_SET_TEMPLATE;
                    $url = $this->urlBuilder->getUrl(
                        'TikTokShop/controlPanel_module_integration_tts/repairNonexistentTemplates',
                        $params
                    );
                    $onClick = <<<JS
var result = prompt('Enter Template ID');
if (result) {
    window.location.href = '{$url}' + '?template_id=' + result;
}
return false;
JS;
                    $actionsHtml .= <<<HTML
<a href="javascript:void();" onclick="{$onClick}">set template</a><br>
HTML;
                }

                $key = $templateName . '##' . $myModeWord . '##' . $itemInfo['listing_id'];
                if ($myModeWord === 'parent' && in_array($key, $alreadyRendered)) {
                    continue;
                }

                $alreadyRendered[] = $key;
                $tableContent .= <<<HTML
<tr>
    <td>{$itemInfo['listing_id']}</td>
    <td>{$itemInfo['my_id']}</td>
    <td>{$itemInfo['my_needed_id']}</td>
    <td>{$itemInfo['my_needed_id_field']}</td>
    <td>{$myModeWord}</td>
    <td>{$parentModeWord}</td>
    <td>
        {$actionsHtml}
    </td>
</tr>
HTML;
            }
        }

        $html = <<<HTML
        <table width="100%">
            {$tableContent}
        </table>
HTML;

        return $html;
    }

    //########################################

    private function getNonexistentTemplatesByDifficultLogic(string $templateCode, string $tableName)
    {
        $databaseHelper = $this->dbStructureHelper;

        $subSelect = $this->resourceConnection
            ->getConnection()
            ->select()
            ->from(
                [
                    'mlp' => $databaseHelper->getTableNameWithPrefix(
                        \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT
                    ),
                ],
                [
                    'my_id' => 'id',
                    'my_mode' => "template_{$templateCode}_mode",
                    'my_template_id' => "template_{$templateCode}_id",

                    'my_needed_id' => new \Zend_Db_Expr(
                        "CASE
                        WHEN mlp.template_{$templateCode}_mode = 2 THEN mlp.template_{$templateCode}_id
                        WHEN mlp.template_{$templateCode}_mode = 1 THEN mlp.template_{$templateCode}_id
                        WHEN mlp.template_{$templateCode}_mode = 0 THEN mel.template_{$templateCode}_id
                        END"
                    ),
                    'my_needed_id_field' => "template_{$templateCode}_id",
                ]
            )
            ->joinLeft(
                [
                    'mel' => $databaseHelper->getTableNameWithPrefix(
                        \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_LISTING
                    ),
                ],
                'mlp.listing_id = mel.id',
                [
                    'listing_id' => 'id',
                    'parent_template_id' => "template_{$templateCode}_id",
                ]
            );

        $templateIdName = 'id';

        $result = $this
            ->resourceConnection
            ->getConnection()->select()
            ->from(
                [
                    'subselect' => $subSelect,
                ],
                [
                    'subselect.my_id',
                    'subselect.listing_id',
                    'subselect.my_mode',
                    'subselect.my_needed_id',
                    'subselect.my_needed_id_field',
                ]
            )
            ->joinLeft(
                [
                    'template' => $databaseHelper->getTableNameWithPrefix($tableName),
                ],
                "subselect.my_needed_id = template.$templateIdName",
                []
            )
            ->where("template.$templateIdName IS NULL")
            ->query()
            ->fetchAll();

        return $result;
    }

    private function getNonexistentTemplatesBySimpleLogic($templateCode, $templateIdField)
    {
        $databaseHelper = $this->dbStructureHelper;

        $select = $this->resourceConnection
            ->getConnection()
            ->select()
            ->from(
                [
                    'melp' => $databaseHelper->getTableNameWithPrefix(
                        \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT
                    ),
                ],
                [
                    'my_id' => 'listing_product_id',
                    'my_needed_id' => $templateIdField,
                    'my_needed_id_field' => new \Zend_Db_Expr("'{$templateIdField}'"),
                ]
            )
            ->joinLeft(
                [
                    'mlp' => $databaseHelper->getTableNameWithPrefix(
                        \M2E\TikTokShop\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT
                    ),
                ],
                'melp.listing_product_id = mlp.id',
                ['listing_id' => 'listing_id']
            )
            ->joinLeft(
                [
                    'template' => $databaseHelper->getTableNameWithPrefix("template_$templateCode"),
                ],
                "melp.$templateIdField = template.id",
                []
            )
            ->where("melp.$templateIdField IS NOT NULL")
            ->where("template.id IS NULL");

        return $select->query()->fetchAll();
    }

    //########################################

    public function fix($data)
    {
        if ($data['action'] === self::FIX_ACTION_SET_NULL) {
            $collection = $this->listingProductCollectionFactory->create();
            $collection->addFieldToFilter($data['field'], $data['field_value']);

            foreach ($collection->getItems() as $listingProduct) {
                $listingProduct->setData($data['field'], null);
                $listingProduct->save();
            }
        }

        if ($data['action'] === self::FIX_ACTION_SET_PARENT) {
            $collection = $this->listingProductCollectionFactory->create();
            $collection->addFieldToFilter($data['field'], $data['field_value']);

            foreach ($collection->getItems() as $listingProduct) {
                $listingProduct->setData(
                    "template_{$data['template']}_mode",
                    \M2E\TikTokShop\Model\TikTokShop\Template\Manager::MODE_PARENT
                );

                $listingProduct->setData($data['field'], null);
                $listingProduct->save();
            }
        }

        if (
            $data['action'] === self::FIX_ACTION_SET_TEMPLATE &&
            $data['template_id']
        ) {
            $collection = $this->listingProductCollectionFactory->create();
            $collection->addFieldToFilter($data['field'], $data['field_value']);

            foreach ($collection->getItems() as $listing) {
                $listing->setData(
                    "template_{$data['template']}_mode",
                    \M2E\TikTokShop\Model\TikTokShop\Template\Manager::MODE_TEMPLATE
                );
                $listingProduct->setData($data['field'], null);
                $listingProduct->setData(
                    "template_{$data['template']}_id",
                    (int)$data['template_id']
                );
            }
        }
    }

    //########################################
}
