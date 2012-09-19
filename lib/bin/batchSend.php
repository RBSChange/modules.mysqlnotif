<?php
$arguments = isset($arguments) ? $arguments : array();
$tm = f_persistentdocument_TransactionManager::getInstance();
foreach (array_chunk($arguments, 10) as $chunk)
{
	try
	{
		$tm->beginTransaction();
		foreach ($chunk as $id)
		{
			try
			{
				if (notification_NotificationService::getInstance()->sendMessageId($id))
				{
					echo '+';
				}
				else
				{
					echo $id, '-';
				}
			} 
			catch (Exception $e) 
			{
				echo $id, '--';
				Framework::exception($e);
			}
		}
		$tm->commit();
	}
	catch (Exception $e)
	{
		$tm->rollBack($e);
	}
}
echo 'OK';