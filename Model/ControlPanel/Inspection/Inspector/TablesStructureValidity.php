<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Model\ControlPanel\Inspection\Inspector;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table as DdlTable;
use M2E\Core\Model\ControlPanel\Inspection\FixerInterface;
use M2E\Core\Model\ControlPanel\Inspection\InspectorInterface;

class TablesStructureValidity implements InspectorInterface, FixerInterface
{
    public const TABLE_MISSING = 'table_missing';
    public const TABLE_REDUNDANT = 'table_redundant';

    public const COLUMN_MISSING = 'column_missing';
    public const COLUMN_REDUNDANT = 'column_redundant';
    public const COLUMN_DIFFERENT = 'column_different';

    public const FIX_INDEX = 'index';
    public const FIX_COLUMN = 'properties';
    public const DROP_COLUMN = 'drop';
    public const CREATE_TABLE = 'create_table';

    private UrlInterface $urlBuilder;
    private ResourceConnection $resourceConnection;
    private FormKey $formKey;
    private \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory;
    private \M2E\TikTokShop\Helper\Module\Database\Structure $databaseHelper;
    private \M2E\TikTokShop\Model\Connector\Client\Single $serverClient;
    private \M2E\TikTokShop\Helper\Magento $magentoHelper;

    public function __construct(
        UrlInterface $urlBuilder,
        ResourceConnection $resourceConnection,
        FormKey $formKey,
        \M2E\Core\Model\ControlPanel\Inspection\IssueFactory $issueFactory,
        \M2E\TikTokShop\Helper\Module\Database\Structure $databaseHelper,
        \M2E\TikTokShop\Model\Connector\Client\Single $serverClient,
        \M2E\TikTokShop\Helper\Magento $magentoHelper
    ) {
        $this->magentoHelper = $magentoHelper;
        $this->serverClient = $serverClient;
        $this->databaseHelper = $databaseHelper;
        $this->urlBuilder = $urlBuilder;
        $this->resourceConnection = $resourceConnection;
        $this->formKey = $formKey;
        $this->issueFactory = $issueFactory;
    }

    public function process(): array
    {
        $issues = [];

        try {
            $diff = $this->getDiff();
        } catch (\Exception $exception) {
            $issues[] = $this->issueFactory->create($exception->getMessage());

            return $issues;
        }

        if (!isset($diff['diff'])) {
            $issues[] = $this->issueFactory->create('No info for this TikTokShop version');

            return $issues;
        }

        if (!empty($diff['diff'])) {
            $issues[] = $this->issueFactory->create(
                'Wrong tables structure validity',
                $this->renderMetadata($diff['diff'])
            );
        }

        return $issues;
    }

    private function getDiff(): array
    {
        $command = new \M2E\Core\Model\Server\Connector\System\TablesGetDiffCommand(
            $this->databaseHelper->getModuleTablesInfo()
        );
        /** @var \M2E\Core\Model\Connector\Response $response */
        $response = $this->serverClient->process($command);

        return $response->getResponseData();
    }

    private function renderMetadata($data): string
    {
        $currentUrl = $this->urlBuilder->getUrl(
            '*/controlPanel_tools_tikTokShop/install',
            ['action' => 'fixColumn']
        );
        $formKey = $this->formKey->getFormKey();

        $html = <<<HTML
 <form method="POST" action="{$currentUrl}">
    <input type="hidden" name="form_key" value="{$formKey}">
<table>
    <tr>
        <th style="width: 500px">Table</th>
        <th>Problem</th>
        <th>Info</th>
    </tr>
HTML;

        foreach ($data as $tableName => $checkResult) {
            foreach ($checkResult as $resultRow) {
                $additionalInfo = '';

                if (!isset($resultRow['info'])) {
                    continue;
                }

                $resultInfo = $resultRow['info'];
                $diffData = isset($resultInfo['diff_data']) ? $resultInfo['diff_data'] : [];

                if (isset($resultInfo['diff_data'])) {
                    foreach ($resultInfo['diff_data'] as $diffCode => $diffValue) {
                        $additionalInfo .= "<b>{$diffCode}</b>: '{$diffValue}'. ";
                        $additionalInfo .= "<b>original:</b> '{$resultInfo['original_data'][$diffCode]}'.";
                        $additionalInfo .= "<br/>";
                    }
                }

                $columnInfo['table_name'] = $tableName;
                $columnInfo['column_info'] = $resultInfo['original_data'];

                if ($resultRow['problem'] === self::TABLE_MISSING) {
                    $columnInfo['repair_mode'] = self::CREATE_TABLE;
                } elseif ($resultRow['problem'] === self::COLUMN_MISSING) {
                    $columnInfo['repair_mode'] = self::FIX_COLUMN;
                } elseif ($resultRow['problem'] === self::COLUMN_REDUNDANT) {
                    $columnInfo['repair_mode'] = self::DROP_COLUMN;
                    $columnInfo['column_info'] = $resultInfo['current_data'];
                } elseif (isset($diffData['key'])) {
                    $columnInfo['repair_mode'] = self::FIX_INDEX;
                } elseif ($resultRow['problem'] === self::COLUMN_DIFFERENT) {
                    $columnInfo['repair_mode'] = self::FIX_COLUMN;
                }

                $repairInfo = \M2E\TikTokShop\Helper\Json::encode($columnInfo);
                $input = "<input type='checkbox' name='repair_info[]' value='" . $repairInfo . "'>";
                $html .= <<<HTML
<tr>
    <td>{$input} {$tableName}</td>
    <td>{$resultRow['message']}</td>
    <td>{$additionalInfo}</td>
</tr>
HTML;
            }
        }
        $html .= '<button type="button" onclick="ControlPanelInspectionObj.removeRow(this)">Repair</button>
</table>
</form>';

        return $html;
    }

    public function fix(array $data): void
    {
        switch ($data['repair_mode']) {
            case self::FIX_COLUMN:
                $this->fixColumnProperties($data['table_name'], $data['column_info']);
                break;
            case self::FIX_INDEX:
                $this->fixColumnIndex($data['table_name'], $data['column_info']);
                break;
            case self::DROP_COLUMN:
                $this->dropColumn($data['table_name'], $data['column_info']);
                break;
            case self::CREATE_TABLE:
                $this->createTable($data['table_name'], $data['column_info']);
                break;
        }
    }

    private function fixColumnIndex($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'], $columnInfo['key'])) {
            return;
        }

        $writeConnection = $this->resourceConnection->getConnection();
        $tableName = $this->databaseHelper->getTableNameWithPrefix($tableName);

        if (empty($columnInfo['key'])) {
            $writeConnection->dropIndex($tableName, $columnInfo['name']);

            return;
        }

        $indexType = AdapterInterface::INDEX_TYPE_PRIMARY;
        $columnInfo['key'] == 'mul' && $indexType = AdapterInterface::INDEX_TYPE_INDEX;
        $columnInfo['key'] == 'uni' && $indexType = AdapterInterface::INDEX_TYPE_UNIQUE;

        $writeConnection->addIndex($tableName, $columnInfo['name'], $columnInfo['name'], $indexType);
    }

    private function fixColumnProperties($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'])) {
            return;
        }

        $definition = $this->convertArrayDefinitionToString($columnInfo);

        $writeConnection = $this->resourceConnection->getConnection();
        $tableName = $this->databaseHelper->getTableNameWithPrefix($tableName);

        $magentoVersion = $this->magentoHelper->getVersion();
        $isConvertColumnDefinitionToArray = version_compare($magentoVersion, '2.3.0', '>=');

        if ($writeConnection->tableColumnExists($tableName, $columnInfo['name']) === false) {
            if ($isConvertColumnDefinitionToArray) {
                $writeConnection->addColumn(
                    $tableName,
                    $columnInfo['name'],
                    $this->convertColumnDefinitionToArray($definition)
                );
            } else {
                $writeConnection->addColumn($tableName, $columnInfo['name'], $definition);
            }

            return;
        }

        if ($isConvertColumnDefinitionToArray) {
            $writeConnection->changeColumn(
                $tableName,
                $columnInfo['name'],
                $columnInfo['name'],
                $this->convertColumnDefinitionToArray($definition)
            );
        } else {
            $writeConnection->changeColumn($tableName, $columnInfo['name'], $columnInfo['name'], $definition);
        }
    }

    private function dropColumn($tableName, array $columnInfo)
    {
        if (!isset($columnInfo['name'])) {
            return;
        }

        $writeConnection = $this->resourceConnection->getConnection();
        $tableName = $this->databaseHelper->getTableNameWithPrefix($tableName);

        $writeConnection->dropColumn($tableName, $columnInfo['name']);
    }

    private function convertColumnDefinitionToArray($definition): array
    {
        $pattern = "#^(?P<type>[a-z]+(?:\(\d+,?\d?\))?)";
        $pattern .= '(?:';
        $pattern .= "(?P<unsigned>\sUNSIGNED)?";
        $pattern .= "(?P<nullable>\s(?:NOT\s)?NULL)?";
        $pattern .= "(?P<default>\sDEFAULT\s[^\s]+)?";
        $pattern .= "(?P<auto_increment>\sAUTO_INCREMENT)?";
        $pattern .= "(?P<primary_key>\sPRIMARY\sKEY)?";
        $pattern .= "(?P<after>\sAFTER\s[^\s]+)?";
        $pattern .= ')?#i';

        $matches = [];
        if (preg_match($pattern, $definition, $matches) === false || !isset($matches['type'])) {
            return $definition;
        }

        $typeMap = [
            DdlTable::TYPE_SMALLINT => ['TINYINT', 'SMALLINT'],
            DdlTable::TYPE_INTEGER => ['INT'],
            DdlTable::TYPE_FLOAT => ['FLOAT'],
            DdlTable::TYPE_DECIMAL => ['DECIMAL'],
            DdlTable::TYPE_DATETIME => ['DATETIME'],
            DdlTable::TYPE_TEXT => ['VARCHAR', 'TEXT', 'LONGTEXT'],
            DdlTable::TYPE_BLOB => ['BLOB', 'LONGBLOB'],
        ];

        $size = null;
        $type = $matches['type'];
        if (strpos($type, '(') !== false) {
            $size = str_replace(['(', ')'], '', substr($type, strpos($type, '(')));
            $type = substr($type, 0, strpos($type, '('));
        }

        if (strtoupper('LONGTEXT') === strtoupper($type)) {
            $size = 16777217;
        }

        $definitionData = [];
        foreach ($typeMap as $ddlType => $types) {
            if (!in_array(strtoupper($type), $types)) {
                continue;
            }

            if ($ddlType == DdlTable::TYPE_TEXT || $ddlType == DdlTable::TYPE_BLOB) {
                $definitionData['length'] = $size;
            }

            if (
                ($ddlType == DdlTable::TYPE_FLOAT || $ddlType == DdlTable::TYPE_DECIMAL) &&
                strpos($size, ',') !== false
            ) {
                [$precision, $scale] = array_map('trim', explode(',', $size, 2));
                $definitionData['precision'] = (int)$precision;
                $definitionData['scale'] = (int)$scale;
            }

            $definitionData['type'] = $ddlType;
            break;
        }

        if (!empty($matches['unsigned'])) {
            $definitionData['unsigned'] = true;
        }

        if (!empty($matches['nullable'])) {
            $definitionData['nullable'] = strpos(
                strtolower($matches['nullable']),
                'not null'
            ) == !false ? false : true;
        }

        if (!empty($matches['default'])) {
            [, $defaultData] = explode(' ', trim($matches['default']), 2);
            $defaultData = trim($defaultData);
            $definitionData['default'] = strtolower($defaultData) == 'null' ? null : $defaultData;
        }

        if (!empty($matches['auto_increment'])) {
            $definitionData['auto_increment'] = true;
        }

        if (!empty($matches['primary_key'])) {
            $definitionData['primary'] = true;
        }

        if (!empty($matches['after'])) {
            [, $afterColumn] = explode(' ', trim($matches['after']), 2);
            $definitionData['after'] = trim($afterColumn, " \t\n\r\0\x0B`");
        }

        $definitionData['comment'] = 'field';

        return $definitionData;
    }

    private function convertArrayDefinitionToString($columnInfo): string
    {
        $definition = "{$columnInfo['type']} ";
        $columnInfo['null'] == 'no' && $definition .= 'NOT NULL ';
        $columnInfo['default'] != '' && $definition .= "DEFAULT '{$columnInfo['default']}' ";
        ($columnInfo['null'] == 'yes' && $columnInfo['default'] == '') && $definition .= 'DEFAULT NULL ';
        $columnInfo['key'] == 'pri' && $definition .= 'PRIMARY KEY ';
        $columnInfo['extra'] == 'auto_increment' && $definition .= 'AUTO_INCREMENT ';
        !empty($columnInfo['after']) && $definition .= "AFTER `{$columnInfo['after']}`";

        return $definition;
    }

    private function createTable($tableName, $columnsInfo)
    {
        $connection = $this->resourceConnection->getConnection();

        $table = $connection->newTable(
            $this->databaseHelper->getTableNameWithPrefix($tableName)
        );

        foreach ($columnsInfo as $columnInfo) {
            $columnDefinition = $this->convertColumnDefinitionToArray(
                $this->convertArrayDefinitionToString($columnInfo)
            );
            $option = [
                'unsigned' => isset($columnDefinition['unsigned']) ? $columnDefinition['unsigned'] : null,
                'precision' => isset($columnDefinition['precision']) ? $columnDefinition['precision'] : null,
                'scale' => isset($columnDefinition['scale']) ? $columnDefinition['scale'] : null,
                'primary' => isset($columnDefinition['primary']) ? $columnDefinition['primary'] : null,
                'auto_increment' => isset($columnDefinition['auto_increment'])
                    ? $columnDefinition['auto_increment'] : null,
            ];

            if (isset($columnDefinition['default'])) {
                $option['default'] = $columnDefinition['default'];
            }
            if (isset($columnDefinition['nullable'])) {
                $option['nullable'] = $columnDefinition['nullable'];
            }
            $table->addColumn(
                $columnInfo['name'],
                $columnDefinition['type'],
                isset($columnDefinition['length']) ? $columnDefinition['length'] : '',
                $option,
                $columnDefinition['comment']
            );
            if (!empty($columnInfo['key']) && $columnInfo['key'] !== 'pri') {
                $columnInfo['key'] == 'mul' && $index['type'] = AdapterInterface::INDEX_TYPE_INDEX;
                $columnInfo['key'] == 'uni' && $index['type'] = AdapterInterface::INDEX_TYPE_UNIQUE;

                $table->addIndex(
                    $columnInfo['name'],
                    $columnInfo['name'],
                    $index
                );
            }
        }

        $connection->createTable($table);
    }
}
