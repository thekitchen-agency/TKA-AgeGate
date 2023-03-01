<?php

namespace thekitchenagency\craftagegate\variables;

use craft\web\View;
use thekitchenagency\craftagegate\AgeGate;
use thekitchenagency\craftagegate\services\AgeGateService;
use Craft;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use verbb\formie\options\Age;
use yii\base\Exception;
use yii\log\Logger;

class AgeGateVariable
{
	/**
	 * @throws SyntaxError
	 * @throws Exception
	 * @throws RuntimeError
	 * @throws LoaderError
	 */
	public function renderAGElement()
	{
		Agegate::$plugin->ageGateService->getRenderAgeGate();
		// var_dump(Agegate::$plugin->ageGateService);
		// return AgeGate::$plugin->ageGateService->getRenderAgeGate();
		// return 'test';
		// return Craft::$app->agegate->renderAgeGate();
		// return ;
		// echo Craft::$app->view->renderTemplate('_agegate/index.twig', ['settings' => AgeGate::$plugin->getSettings()], View::TEMPLATE_MODE_SITE);
	}
}
