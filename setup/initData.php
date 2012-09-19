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
	 * @return string[]
	 */
	public function getRequiredPackages()
	{
		return array();
	}
}