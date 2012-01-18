<?php
/**
 * @package modules.mysqlnotif.setup
 */
class mysqlnotif_Setup extends object_InitDataSetup
{
	public function install()
	{
		$nnsName = Framework::getConfigurationValue('injection/notification_NotificationService', 'notification_NotificationService');
		if ($nnsName != 'notification_NotificationService' && $nnsName != 'mysqlnotif_InjectedNotificationService')
		{
			$this->addWarning($nnsName . ' must be extend mysqlnotif_InjectedNotificationService !');
		}
		else
		{
			$this->addProjectConfigurationEntry('injection/notification_NotificationService', 'mysqlnotif_InjectedNotificationService');
		}		
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