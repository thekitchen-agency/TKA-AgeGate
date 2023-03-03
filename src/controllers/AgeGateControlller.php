<?php

namespace thekitchenagency\craftagegate\controllers;

use Craft;
use craft\web\Controller;

class AgeGateController extends Controller
{
	public function actionIndex()
	{
		Craft::$app->view->registerAssetBundle(\thekitchenagency\craftagegate\assetbundles\agegate\AgeGateAsset::class);
	}
}
