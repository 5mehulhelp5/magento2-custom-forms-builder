<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace Alekseon\CustomFormsBuilder\Setup\Patch\Schema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Alekseon\AlekseonEav\Setup\EavSchemaSetupFactory;

/**
 *
 */
class CreateEavTablesV2 implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;
    /**
     * @var EavSchemaSetupFactory
     */
    private $eavSetupFactory;

    /**
     * EnableSegmentation constructor.
     *
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        EavSchemaSetupFactory $eavSetupFactory
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @return CreateEavTables|void
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $setup = $this->schemaSetup;

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->createFullEavStructure(
            'alekseon_custom_form_attribute',
            'alekseon_custom_form_entity',
            null,
            'alekseon_custom_form'
        );

        $eavSetup->createFullEavStructure(
            'alekseon_custom_form_record_attribute',
            'alekseon_custom_form_record_entity',
            null,
            'alekseon_custom_form_record'
        );

        // fix for old module version
        $this->updateAttributeCodeColumnSize($setup, 'alekseon_custom_form_attribute');
        $this->updateAttributeCodeColumnSize($setup, 'alekseon_custom_form_record_attribute');

        $this->addFormIdColumn($setup);
        $this->addIdentifierColumn($setup);
        $this->addInputVisibilityColumn($setup);

        $this->schemaSetup->endSetup();
    }


    /**
     * @param SchemaSetupInterface $setup
     * @param string $attributeTableName
     * @return void
     */
    private function updateAttributeCodeColumnSize(SchemaSetupInterface $setup, string $attributeTableName)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable($attributeTableName),
            'attribute_code',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'default' => '',
                'length' => 255,
                'comment' => 'Attribute Code'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addIdentifierColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('alekseon_custom_form_record_attribute'),
            'identifier',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Identifier',
                'nullable' => true,
            ]
        );
        $setup->getConnection()->addIndex(
            $setup->getTable('alekseon_custom_form_record_attribute'),
            $setup->getIdxName(
                'alekseon_custom_form_record_attribute',
                ['identifier', 'form_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['identifier', 'form_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addInputVisibilityColumn(SchemaSetupInterface $setup)
    {
        $recordAttributeTable = $setup->getTable('alekseon_custom_form_record_attribute');

        $columnDefinition = [
            'type' => Table::TYPE_SMALLINT,
            'comment' => 'Input Visibility',
            'nullable' => false,
            'default' => 1,
        ];

        /**
         * rename in_enabled column to input_visibility for previous module versions
         * or add new column for new installations
         */
        if ($setup->getConnection()->tableColumnExists($recordAttributeTable, 'is_enabled')) {
            $setup->getConnection()->changeColumn(
                $recordAttributeTable,
                'is_enabled',
                'input_visibility',
                $columnDefinition
            );
        } else {
            $setup->getConnection()->addColumn(
                $recordAttributeTable,
                'input_visibility',
                $columnDefinition
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addFormIdColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('alekseon_custom_form_record_attribute'),
            'form_id',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'comment' => 'Form Id'
            ]
        );

        $setup->getConnection()->addForeignKey(
            $setup->getConnection()->getForeignKeyName(
                $setup->getTable('alekseon_custom_form_record_attribute'),
                'form_id',
                $setup->getTable('alekseon_custom_form'),
                'entity_id'
            ),
            $setup->getTable('alekseon_custom_form_record_attribute'),
            'form_id',
            $setup->getTable('alekseon_custom_form'),
            'entity_id',
            AdapterInterface::FK_ACTION_CASCADE
        );
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }
}
