<?php

require_once __DIR__ . '/../libs/common.php';  // globale Funktionen

if (!defined('IPS_BOOLEAN')) {
    define('IPS_BOOLEAN', 0);
}
if (!defined('IPS_INTEGER')) {
	define('IPS_INTEGER', 1);
}
if (!defined('IPS_FLOAT')) {
	define('IPS_FLOAT', 2);
}
if (!defined('IPS_STRING')) {
	define('IPS_STRING', 3);
}

class GoogleMaps extends IPSModule
{
	use GoogleMapsCommon;

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyString('api_key', '');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $api_key = $this->ReadPropertyString('api_key');

		$this->SetStatus($$api_key == '' ? 104 : 102);
    }

}
