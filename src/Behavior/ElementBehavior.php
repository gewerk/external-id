<?php

/**
 * @link https://gewerk.dev/plugins/external-id
 * @copyright 2022 gewerk, Dennis Morhardt
 * @license https://github.com/gewerk/external-id/blob/main/LICENSE.md
 */

namespace Gewerk\ExternalId\Behavior;

use craft\base\ElementInterface;
use yii\base\Behavior;

/**
 * Extends the element with external ID properties and methods
 *
 * @property ElementInterface $owner
 * @package Gewerk\ExternalId\Behavior
 */
class ElementBehavior extends Behavior
{
    /**
     * @var string|null
     */
    public $externalId = null;

    /**
     * Returns the external ID
     *
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * Sets the external ID
     *
     * @param string|null $externalId
     * @return ElementInterface
     */
    public function setExternalId(?string $externalId = null): ElementInterface
    {
        $this->externalId = $externalId;

        return $this->owner;
    }
}
