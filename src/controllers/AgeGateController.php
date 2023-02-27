<?php

namespace thekitchenagency\craftagegate\controllers;

use Craft;
use craft\web\Controller;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;


class AgeGateController extends Controller {

	public function actionIndex() {
		var_dump('test: action index controller');
		$this->renderTemplate('craft-agegate/index');
	}

	public function actionaFail() {

	}

	/**
	 * @throws SyntaxError
	 * @throws Exception
	 * @throws RuntimeError
	 * @throws LoaderError
	 */
	private function renderFrontEndTemplate(string $template, array $params = []) : String {
		$view = $this->getView();
		$oldMode = $view->getTemplateMode();
		$view->setTemplateMode($view::TEMPLATE_MODE_CP);
		$rendered = $view->renderTemplate($template, $params);
		$view->setTemplateMode($oldMode);
		return $rendered;
	}
}
