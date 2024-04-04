<?php

namespace littlemissrobot\craftsearchable;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\TemplateEvent;
use craft\services\UserPermissions;
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
	public static $instance;

	public string $schemaVersion = '1.0.0';

	// Public Methods
	// =========================================================================
	/**
	 * @inheritdoc
	 */
	public function __construct($id, $parent = null, array $config = [])
	{
		// Set this as the global instance
		static::setInstance($this);

		parent::__construct($id, $parent, $config);
	}

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
		self::$instance = $this;

		// Defer most setup tasks until Craft is fully initialized
		Craft::$app->onInit(function() {
			$this->attachEventHandlers();
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
		$this->installCpEventListeners();
		// $this->installElementEventHandlers();
	}

	protected function installElementEventHandlers()
	{
		// ...
	}

	protected function installCpEventListeners()
	{
		// Searchable: UserPermissions::EVENT_REGISTER_PERMISSIONS
		// Register custom user permissions
		Event::on(
			UserPermissions::class,
			UserPermissions::EVENT_REGISTER_PERMISSIONS,
			function(RegisterUserPermissionsEvent $event) {

				// Add permission for viewing and setting the searchable label
				$event->permissions[] = [
					'heading' => Craft::t('searchable', 'Searchable'),
					'permissions' => [
						'viewSearchable' => [
							'label' => Craft::t('searchable', 'Can see searchable label in field labels'),
						],
					],
				];
			}
		);

		// Searchable: View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE
		// Register Asset Bundle on TemplateEvent
		Event::on(
			View::class,
			View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
			function (TemplateEvent $event) {
				if ($event->templateMode !== View::TEMPLATE_MODE_CP) {
					return;
				}

				if (Craft::$app->user->checkPermission('viewSearchable')) {
					$this->registerAssetBundle();
				}
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
		// Create a data object with the search label
		$data = [
			'searchLabel' => Craft::t('searchable', 'This field\'s values are used as search keywords.'),
		];

		// Get all available fields
		$fields = Craft::$app->getFields()->getAllFields('global');

		// Add the id and their searchable attribute
		foreach ($fields as $field) {
			$fieldType = get_class($field);

			$data['fields'][$field->handle] = [
				"id" => $field->id,
				"searchable" => $field->searchable ?? false,
				"type" => $fieldType,
			];

			switch ($fieldType) {
				case "craft\\fields\\Matrix":
					$data = $this->processField($data, $field, 'matrix-', $fieldType);
					break;

				case "verbb\\supertable\\fields\\SuperTableField":
					$data = $this->processField($data, $field, 'superTable-', $fieldType);
					break;

				default:
					break;
			}
		}

		// Register the asset bundle and pass data object
		Craft::$app->getView()->registerAssetBundle(SearchableBundleAsset::class, \yii\web\View::POS_END);
		Craft::$app->getView()->registerJs('Craft.SearchablePlugin.init(' . \json_encode($data) . ');', \yii\web\View::POS_END);
	}

	function processField($data, $field, $blockFieldPrefix = '', $fieldType = '') {
		$blockFields = $field->getBlockTypeFields();

		foreach ($blockFields as $blockField) {
			$data['fields'][$blockFieldPrefix . $blockField->handle] = [
				"id" => $blockField->id,
				"searchable" => $blockField->searchable ?? false,
				"type" => $fieldType,
			];
		}

		return $data;
	}
}
