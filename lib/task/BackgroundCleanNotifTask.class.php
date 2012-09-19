<?php
class mysqlnotif_BackgroundCleanNotifTask extends task_SimpleSystemTask
{
	/**
	 * @see task_SimpleSystemTask::execute()
	 */
	protected function execute()
	{
		$chunkSize = 100;
		$errors = array();
		$nns = notification_NotificationService::getInstance();
		if (method_exists($nns, 'cleanNotif'))
		{
			$maxDayAge = Framework::getConfiguration('modules/mysqlnotif/max-days-age');
			$str = $this->plannedTask->getParameters();
			if (!empty($str))
			{
				$array = JsonService::getInstance()->decode($str);
				if (isset($array['max-days-age']))
				{
					$maxDayAge = intval($array['max-days-age']);
				}
			}
			if ($maxDayAge > 0)
			{
				$before = date_Calendar::getInstance()->sub(date_Calendar::DAY, $maxDayAge)->toString();
				Framework::info(__METHOD__ . ' before: ' . $before . ' max-days-age: ' . $maxDayAge);
				$cleaned = $nns->cleanNotif($before);
				Framework::info(__METHOD__ . ' cleaned: ' . $cleaned);
			}
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