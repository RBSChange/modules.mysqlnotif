<?php
/**
 * @package modules.mysqlnotif.setup
 */
class mysqlnotif_Setup extends object_InitDataSetup
{
	public function install()
	{
		// Add injection of notification_NotificationService.
		$this->addInjectionInProjectConfiguration('notification_NotificationService', 'mysqlnotif_InjectedNotificationService');
		
		// Add tasks.
		mysqlnotif_ModuleService::getInstance()->addBackgroundSendNotifTask();
	}

	/**
	 * @return String[]
	 */
	public function getRequiredPackages()
	{
		// Return an array of packages name if the data you are inserting in
		// this file depend on the data of other packages.
		// Example:
		// return array('modules_website', 'modules_users');
		return array();
	}
}