<?php
namespace Aravis\ItGoesForward\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Zend_Db_Exception;

class AddItGoesForwardToQuoteItem implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        try {
            $connection->addColumn(
                $this->moduleDataSetup->getTable('quote_item'),
                'it_goes_forward',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'It Goes Forward Product'
                ]
            );

            $connection->addColumn(
                $this->moduleDataSetup->getTable('quote_item'),
                'discount_value',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    'nullable' => true,
                    'comment' => 'Discount Value'
                ]
            );
        } catch (Zend_Db_Exception $e) {
            // Handle exception if needed
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
