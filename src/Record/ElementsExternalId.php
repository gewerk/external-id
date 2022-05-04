<?php

/**
 * @link https://gewerk.dev/plugins/external-id
 * @copyright 2022 gewerk, Dennis Morhardt
 * @license https://github.com/gewerk/external-id/blob/main/LICENSE.md
 */

namespace Gewerk\ExternalId\Record;

use craft\base\Element;
use craft\db\ActiveRecord;
use DateTime;
use yii\db\ActiveQueryInterface;

/**
 * Record for attaching an external ID to an element
 *
 * @property int|null $id Element ID
 * @property string|null $externalId External ID
 * @property DateTime|null $dateUpdated Record updated on
 * @property DateTime|null $dateCreated Record created on
 * @property string|null $uid UUIDv4 for this record
 */
class ElementsExternalId extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%elements_external_ids%}}';
    }

    /**
     * Returns associated element to this record
     *
     * @return ActiveQueryInterface
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}
