<?php

namespace littlemissrobot\craftsearchable;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\TemplateEvent;
use craft\web\View;

use yii\base\Event;

/**
 * Searchable plugin
 *
 * @method static Plugin getInstance()
 * @author Little Miss Robot <craft@littlemissrobot.com>
 * @copyright Little Miss Robot
 * @license MIT
 */
class Plugin extends BasePlugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });

        Craft::info(
            Craft::t(
                'searchable',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                if ($event->templateMode !== View::TEMPLATE_MODE_CP) {
                    return;
                }
                $this->registerAssetBundle();
            }
        );

    }

    /**
     * @return void
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function registerAssetBundle(): void
    {
        // Create a data object with the searc label
        $data = [
            'searchLabel' => Craft::t('searchable', 'This field\'s values are used as search keywords.'),
        ];

        // Get all available fields
        $fields = Craft::$app->getFields()->getAllFields('global');

        // Add the id and their searchable attribute
        foreach ($fields as $field) {
            $data['fields'][$field->handle] = [
                "id" => $field->id,
                "searchable" => $field->searchable ?? false
            ];
        }

        // Register the asset bundle and pass data object
        Craft::$app->getView()->registerAssetBundle(SearchableBundleAsset::class, \yii\web\View::POS_END);
        Craft::$app->getView()->registerJs('Craft.SearchablePlugin.init(' . \json_encode($data) . ');', \yii\web\View::POS_END);
    }
}
