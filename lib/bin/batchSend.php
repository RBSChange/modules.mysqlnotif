<?php
$notifIdArray = $_POST['argv'];
$tm = f_persistentdocument_TransactionManager::getInstance();
foreach (array_chunk($notifIdArray, 10) as $chunk)
{
	Framework::info(__FILE__ . ' -> ' . implode(', ', $chunk));
	try
	{
		$tm->beginTransaction();
		foreach ($chunk as $id)
		{
			try
			{
				if (mysqlnotif_InjectedNotificationService::getInstance()->sendMessageId($id))
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