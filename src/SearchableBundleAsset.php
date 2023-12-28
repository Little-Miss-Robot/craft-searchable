<?php

namespace littlemissrobot\craftsearchable;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Searchable Bundle asset bundle
 */
class SearchableBundleAsset extends AssetBundle
{
    public $sourcePath = '@littlemissrobot/craftsearchable/resources';
    public $depends = [];
    public $js = [];
    public $css = [];

    public function init()
    {
        $this->sourcePath = $this->sourcePath;

        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'searchable.js',
        ];

        $this->css = [
            'searchable.css',
        ];

        parent::init();
    }
}
