<?php

/**
 * @link https://gewerk.dev/plugins/external-id
 * @copyright 2022 gewerk, Dennis Morhardt
 * @license https://github.com/gewerk/external-id/blob/main/LICENSE.md
 */

namespace Gewerk\ExternalId\Behavior;

use craft\elements\db\ElementQuery;
use yii\base\Behavior;

/**
 * Extends the element query
 *
 * @property ElementQuery $owner
 * @package Gewerk\ExternalId\Behavior
 */
class ElementQueryBehavior extends Behavior
{
    /**
     * @var string|string[]|null
     */
    public $externalId = null;

    /**
     * Adds external id to query
     *
     * @param string|string[]|null $externalId
     * @return ElementQuery
     */
    public function externalId($externalId = null): ElementQuery
    {
        $this->externalId = $externalId;

        return $this->owner;
    }
}
