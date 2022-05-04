<?php

/**
 * @link https://gewerk.dev/plugins/external-id
 * @copyright 2022 gewerk, Dennis Morhardt
 * @license https://github.com/gewerk/external-id/blob/main/LICENSE.md
 */

namespace Gewerk\ExternalId\Event;

use yii\base\Event;

/**
 * This event will be used for registering all element details hooks
 *
 * @package Gewerk\ExternalId\Event
 */
class RegisterElementHooksEvent extends Event
{
    /**
     * @var string[] List of registered element details hooks
     */
    public $elementHooks = [];
}
