<?php

/**
 * @link https://gewerk.dev/plugins/external-id
 * @copyright 2022 gewerk, Dennis Morhardt
 * @license https://github.com/gewerk/external-id/blob/main/LICENSE.md
 */

namespace Gewerk\ExternalId;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Plugin;
use craft\db\MigrationManager;
use craft\elements\db\ElementQuery;
use craft\events\CancelableEvent;
use craft\events\DefineBehaviorsEvent;
use craft\events\ModelEvent;
use craft\helpers\Db;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use craft\i18n\PhpMessageSource;
use Gewerk\ExternalId\Event\RegisterElementHooksEvent;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Inits the plugin and acts as service locator
 *
 * @package Gewerk\ExternalId
 */
class ExternalId extends Plugin
{
    public const EVENT_REGISTER_ELEMENT_HOOKS = 'registerElementHooks';

    /**
     * Current plugin instance
     *
     * @var self
     */
    public static $plugin;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Save current instance
        self::$plugin = $this;

        // Set alias
        Craft::setAlias('@external-id', $this->getRootPath());

        // Load translations
        Craft::$app->getI18n()->translations['external-id'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'basePath' => '@external-id/translations',
            'forceTranslation' => true,
            'allowOverrides' => true,
        ];

        // Register all hooks, behaviors and event handlers
        $this->registerElementMetaHooks();
        $this->registerBehaviors();
        $this->registerElementAfterSaveEventHandlers();
        $this->registerElementQueryEventHandlers();
    }

    /**
     * Returns the plugin root path
     *
     * @return string
     */
    public function getRootPath()
    {
        return dirname(dirname(__FILE__));
    }

    /**
     * @inheritdoc
     */
    public function getMigrator(): MigrationManager
    {
        /** @var MigrationManager */
        $migrationManager = $this->get('migrator');
        $migrationManager->migrationPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'Migration';
        $migrationManager->migrationNamespace = 'Gewerk\\ExternalId\\Migration';

        return $migrationManager;
    }

    /**
     * @inheritdoc
     */
    protected function createInstallMigration()
    {
        return new Migration\InstallMigration();
    }

    /**
     * Registers all element meta hooks
     *
     * @return void
     * @throws InvalidConfigException
     */
    private function registerElementMetaHooks(): void
    {
        // Get all element types
        $elementHooks = [
            'cp.assets.edit.meta',
            'cp.categories.edit.meta',
            'cp.entries.edit.meta',
        ];

        $event = new RegisterElementHooksEvent([
            'elementHooks' => $elementHooks,
        ]);

        $this->trigger(self::EVENT_REGISTER_ELEMENT_HOOKS, $event);

        foreach ($event->elementHooks as $elementHook) {
            Craft::$app->view->hook(
                $elementHook,
                function (array &$context) {
                    /** @var ElementInterface */
                    $element = $context['element'];

                    if ($externalId = $element->getExternalId()) {
                        $heading = Html::tag(
                            'h5',
                            Craft::t('external-id', 'External ID'),
                            ['class' => 'heading']
                        );

                        $value = Html::tag(
                            'div',
                            $element->getExternalId(),
                            ['class' => 'value']
                        );

                        return Html::tag(
                            'div',
                            $heading . $value,
                            ['class' => 'data']
                        );
                    }

                    return '';
                }
            );
        }
    }

    /**
     * Registers all behaviors
     *
     * @return void
     */
    private function registerBehaviors(): void
    {
        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                /** @var ElementQuery */
                $sender = $event->sender;
                $sender->attachBehaviors([
                    Behavior\ElementQueryBehavior::class,
                ]);
            }
        );

        Event::on(
            Element::class,
            Element::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                /** @var Element */
                $sender = $event->sender;
                $sender->attachBehaviors([
                    Behavior\ElementBehavior::class,
                ]);
            }
        );
    }

    /**
     * Registers the after save event handlers to all elements
     *
     * @return void
     */
    private function registerElementAfterSaveEventHandlers(): void
    {
        Event::on(
            Element::class,
            Element::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                /**
                 * @var Element
                 * @mixin Behavior\ElementBehavior
                 */
                $element = $event->sender;

                Db::upsert(
                    Record\ElementsExternalId::tableName(),
                    [
                        'id' => $element->id,
                    ],
                    [
                        'externalId' => $element->externalId,
                    ]
                );
            }
        );
    }

    /**
     * Registers all element query event handlers
     *
     * @return void
     */
    private function registerElementQueryEventHandlers(): void
    {
        Event::on(
            ElementQuery::class,
            ElementQuery::EVENT_AFTER_PREPARE,
            function (CancelableEvent $event) {
                /**
                 * @var ElementQuery
                 * @mixin Behavior\ElementQueryBehavior
                 */
                $elementQuery = $event->sender;

                // Add left join to subquery
                $elementQuery->subQuery->leftJoin(
                    Record\ElementsExternalId::tableName(),
                    '[[elements_external_ids.id]] = [[elements.id]]'
                );

                // Add left to main query
                if (count($elementQuery->query->select) > 1) {
                    $elementQuery->query
                        ->leftJoin(
                            Record\ElementsExternalId::tableName(),
                            '[[elements_external_ids.id]] = [[subquery.elementsId]]'
                        )
                        ->addSelect([
                            '[[elements_external_ids.externalId]]',
                        ]);
                }

                // Filter by external ID
                if ($elementQuery->externalId) {
                    $elementQuery->subQuery->andWhere(Db::parseParam(
                        '[[elements_external_ids.externalId]]',
                        $elementQuery->externalId,
                        '=',
                        false,
                        'string'
                    ));
                }
            }
        );
    }
}
