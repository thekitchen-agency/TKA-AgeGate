<?php

namespace thekitchenagency\craftagegate\variables;

use thekitchenagency\craftagegate\AgeGate;

class AgeGateVariable
{
	public function renderAGElement()
	{
		Agegate::$plugin->ageGateService->getRenderAgeGate();
	}

	public function getAGSettings()
	{
		return Agegate::$plugin->ageGateService->getCurrentSiteAgeGateSettings();
	}
}
