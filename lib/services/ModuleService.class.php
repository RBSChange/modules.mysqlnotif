<?php
/**
 * @package modules.mysqlnotif.lib.services
 */
class mysqlnotif_ModuleService extends ModuleBaseService
{
	/**
	 * Singleton
	 * @var mysqlnotif_ModuleService
	 */
	private static $instance = null;

	/**
	 * @return mysqlnotif_ModuleService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = self::getServiceClassInstance(get_class());
		}
		return self::$instance;
	}
	
	
	/**
	 * @return void
	 */
	public function addBackgroundSendNotifTask()
	{
		$tasks = task_PlannedtaskService::getInstance()->getBySystemtaskclassname('mysqlnotif_BackgroundSendNotifTask');
		if (count($tasks) == 0)
		{
			$task = task_PlannedtaskService::getInstance()->getNewDocumentInstance();
			$task->setSystemtaskclassname('mysqlnotif_BackgroundSendNotifTask');
			$task->setLabel('mysqlnotif_BackgroundSendNotifTask');
			$task->setMaxduration(2);
			$task->save(ModuleService::getInstance()->getSystemFolderId('task', 'mysqlnotif'));
		}
		
		$tasks = task_PlannedtaskService::getInstance()->getBySystemtaskclassname('mysqlnotif_BackgroundCleanNotifTask');
		if (count($tasks) == 0)
		{
			$task = task_PlannedtaskService::getInstance()->getNewDocumentInstance();
			$task->setSystemtaskclassname('mysqlnotif_BackgroundCleanNotifTask');
			$task->setLabel('mysqlnotif_BackgroundCleanNotifTask');
			$task->setHour(-1);
			$task->setMinute(-1);
			$task->setMaxduration(2);
			$task->save(ModuleService::getInstance()->getSystemFolderId('task', 'mysqlnotif'));			
		}
	}
	
}