<?php
class mysqlnotif_BackgroundSendNotifTask extends task_SimpleSystemTask
{
	/**
	 * @see task_SimpleSystemTask::execute()
	 */
	protected function execute()
	{
		$chunkSize = 100;
		$errors = array();
		$nns = notification_NotificationService::getInstance();
		if ($nns instanceof mysqlnotif_InjectedNotificationService)
		{
			$batchPath = 'modules/mysqlnotif/lib/bin/batchSend.php';
			$startId = 0;
			do 
			{
				$this->plannedTask->ping();
				$ids = $nns->getChunkIdsToSend($startId, $chunkSize);
				if (count($ids))
				{
					$result = f_util_System::execScript($batchPath, $ids);
					if (substr($result, -2) != 'OK')
					{
						$errors[] = $result;
					}
					$startId = end($ids);
				}
			}
			while (count($ids) === $chunkSize);
		}
		else
		{
			$errors[] = 'notificationService is not instance of mysqlnotif_InjectedNotificationService';
		}		
		if (count($errors))
		{
			throw new Exception(implode("\n", $errors));
		}
	}
}