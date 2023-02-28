<?php

namespace thekitchenagency\craftagegate\resources;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class AgeGateAssets extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = '@thekitchenagency/craftagegate/resources/';

		$this->depends = [ CpAsset::class ];

		$this->js = [
			'js/AgeGate.js',
		];

		$this->css = [
			'css/AgeGate.css',
		];

		parent::init();
	}
}
