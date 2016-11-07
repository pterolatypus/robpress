<?php

class Settings {

	/** Get all the settings */
	public static function getSettings() {		
		$model = new SettingsModel('Settings',new Database());
		$settings = $model->fetchList(array('setting','value'));
		return $settings;
	}

}

?>
