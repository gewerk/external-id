<?php

/**
 * @link https://gewerk.dev/plugins/external-id
 * @copyright 2022 gewerk, Dennis Morhardt
 * @license https://github.com/gewerk/external-id/blob/main/LICENSE.md
 */

namespace Gewerk\ExternalId\Migration;

use craft\db\Migration;
use craft\db\Table;
use Gewerk\ExternalId\Record;

/**
 * Migrates all tables for this plugin
 *
 * @package Gewerk\ExternalId\Migration
 */
class InstallMigration extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Record\ElementsExternalId::tableName(), [
            'id' => $this->primaryKey(),
            'externalId' => $this->string(255),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(
            null,
            Record\ElementsExternalId::tableName(),
            ['externalId'],
            false
        );

        $this->addForeignKey(
            null,
            Record\ElementsExternalId::tableName(),
            ['id'],
            Table::ELEMENTS,
            ['id'],
            'CASCADE',
            null
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(Record\ElementsExternalId::tableName());

        return true;
    }
}
