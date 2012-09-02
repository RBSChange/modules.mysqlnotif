<?php
/**
 * @package modules.v.lib.services
 */
class mysqlnotif_InjectedNotificationService extends notification_NotificationService
{
	/**
	 *
	 * @param $mailService MailService       	
	 * @param $sender string       	
	 * @param $replyTo string       	
	 * @param $toArray string[]       	
	 * @param $ccArray string[]       	
	 * @param $bccArray string[]       	
	 * @param $subject string       	
	 * @param $htmlBody string       	
	 * @param $textBody string       	
	 * @param $senderModuleName string       	
	 * @return true
	 */
	protected function composeMailMessage($mailService, $sender, $replyTo, $toArray, $ccArray, $bccArray, $subject, $htmlBody, $textBody, $senderModuleName, $attachments = array())
	{
		$msName = get_class($mailService);
		$values = array('sn' => $msName, 'smn' => $senderModuleName, 's' => $sender, 'rt' => $replyTo, 'to' => $toArray, 'cc' => $ccArray, 
			'bcc' => $bccArray, 'su' => $subject, 'hb' => $htmlBody, 'tb' => $textBody, 'at' => $attachments);
		$lob = JsonService::getInstance()->encode($values);
		$tm = $this->getTransactionManager();
		if ($tm->hasTransaction())
		{
			try
			{
				$this->insertMessage($lob);
			}
			catch (Exception $e)
			{
				Framework::exception($e);
				return false;
			}
		}
		else
		{
			try
			{
				$tm->beginTransaction();
				$this->insertMessage($lob);
				$tm->commit();
			}
			catch (Exception $e)
			{
				$tm->rollback($e);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * @param $id integer 
	 * @return boolean  	
	 */
	public function sendMessageId($id)
	{
		$result = false;
		$tm = $this->getTransactionManager();
		if ($tm->hasTransaction())
		{
			try
			{
				$result = $this->sendMessageIdInternal($id);
			}
			catch (Exception $e)
			{
				Framework::exception($e);
			}
		}
		else
		{
			try
			{
				$tm->beginTransaction();
				$result = $this->sendMessageIdInternal($id);
				$tm->commit();
			}
			catch (Exception $e)
			{
				$tm->rollback($e);
			}
		}
		return $result;
	}
	
	/**
	 * @param integer $id
	 * @return boolean  
	 */
	protected function sendMessageIdInternal($id)
	{
		$lob = $this->getMsgById($id);
		if (empty($lob))
		{
			Framework::warn(__METHOD__ . ' not found: ' . $id);
			return false;
		}
		
		$data = JsonService::getInstance()->decode($lob);
		$msName = $data['sn'];
		$senderModuleName = $data['smn'];
		$sender = $data['s'];
		$replyTo = $data['rt'];
		$toArray = $data['to'];
		$ccArray = $data['cc'];
		$bccArray = $data['bcc'];
		$subject = $data['su'];
		$htmlBody = $data['hb'];
		$textBody = $data['tb'];
		$attachments = $data['at'];
		try
		{
			$mailService = f_util_ClassUtils::callMethod($msName, 'getInstance');
			$mailMessage = parent::composeMailMessage($mailService, $sender, $replyTo, $toArray, $ccArray, $bccArray, $subject, $htmlBody, $textBody, $senderModuleName, $attachments);
			if ($this->sendMailMessage($mailService, $mailMessage))
			{
				$this->setSendMsgById($id);
				return true;
			}
		}
		catch (Exception $e)
		{
			Framework::exception($e);
		}
		$this->updateCountMsgById($id);
		return false;
	}
	
	/**
	 * @param string $msgData
	 * @param string $insertDate
	 */
	protected function insertMessage($msgData, $insertDate = null)
	{
		if ($insertDate === null)
		{
			$insertDate = date_Calendar::getInstance()->toString();
		}
		$sql = "INSERT INTO `m_mysqlnotif_mod_msg` (`msg_data` , `insert_date`) VALUES (:msg_data, :insert_date)";
		$pdo = $this->getPersistentProvider()->getDriver();
		$stmt = $pdo->prepare($sql);

		$stmt->bindValue(':msg_data', $msgData, PDO::PARAM_STR);
		$stmt->bindValue(':insert_date', $insertDate, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	/**
	 * @param $minId integer       	
	 * @param $chunkSize integer       	
	 * @return string[]
	 */
	public function getChunkIdsToSend($minId = 0, $chunkSize = 100)
	{
		$sql = "SELECT `msg_id` FROM `m_mysqlnotif_mod_msg` WHERE `msg_id` > :msg_id AND `send_date` IS NULL ORDER BY `msg_id` LIMIT 0, " . intval($chunkSize);
		$pdo = $this->getPersistentProvider()->getDriver();
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':msg_id', $minId, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_COLUMN);
	}
	
	/**
	 * @param string $before
	 * @param boolean $sendedOnly
	 * @return integer
	 */
	public function cleanNotif($before, $sendedOnly = true)
	{
		$sql = "DELETE FROM `m_mysqlnotif_mod_msg` WHERE `insert_date` < :insert_date";
		if ($sendedOnly)
		{
			$sql . " AND `send_date` IS NOT NULL";
		}
		$pdo = $this->getPersistentProvider()->getDriver();
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':insert_date', $before, PDO::PARAM_STR);
		if ($stmt->execute())
		{
			return $stmt->rowCount();
		}
		return -1;
	}
	
	/**
	 *
	 * @param $id integer       	
	 * @return string
	 */
	protected function getMsgById($id)
	{
		$sql = "SELECT `msg_data` FROM `m_mysqlnotif_mod_msg` WHERE `msg_id` = :msg_id";
		$pdo = $this->getPersistentProvider()->getDriver();
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':msg_id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return (is_array($rows) && count($rows)) ? $rows[0] : null;
	}
	
	/**
	 *
	 * @param $id integer       	
	 * @param string $sendDate
	 * @return string
	 */
	protected function setSendMsgById($id, $sendDate = null)
	{
		if ($sendDate === null)
		{
			$sendDate = date_Calendar::getInstance()->toString();
		}
		$sql = "UPDATE `m_mysqlnotif_mod_msg` SET `send_date`= :send_date, `send_count` = `send_count` + 1 WHERE `msg_id` = :msg_id";
		$pdo = $this->getPersistentProvider()->getDriver();
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':send_date', $sendDate, PDO::PARAM_STR);
		$stmt->bindValue(':msg_id', $id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	/**
	 *
	 * @param $id integer       	
	 * @return string
	 */
	protected function updateCountMsgById($id)
	{

		$sql = "UPDATE `m_mysqlnotif_mod_msg` SET `send_count` = `send_count` + 1 WHERE `msg_id` = :msg_id";
		$pdo = $this->getPersistentProvider()->getDriver();
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':msg_id', $id, PDO::PARAM_INT);
		$stmt->execute();
	}
}