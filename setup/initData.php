<?php
/**
 * @package modules.mysqlnotif.setup
 */
class mysqlnotif_Setup extends object_InitDataSetup
{
	public function install()
	{
		// $this->executeModuleScript('init.xml');
		self::tmpAddProjectConfigurationNamedEntry('injection', 'notification_NotificationService', 'mysqlnotif_InjectedNotificationService');
		
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
	
	/**
	 * @param string $path
	 * @param string $value
	 * @return string old value
	 */
	private static function tmpAddProjectConfigurationNamedEntry($path, $entryName, $value)
	{
		$sections = array('config');
		foreach (explode('/', $path) as $name)
		{
			if (trim($name) != '') {
				$sections[] = trim($name);
			}
		}
	
		$oldValue = null;
		$configProjectPath = implode(DIRECTORY_SEPARATOR, array(WEBEDIT_HOME, 'config', 'project.xml'));
		if (!is_readable($configProjectPath))
		{
			return false;
		}
	
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->formatOutput = true;
		$dom->preserveWhiteSpace = false;
		$dom->load($configProjectPath);
		$dom->formatOutput = true;
		if ($dom->documentElement == null)
		{
			return false;
		}
		$sectionNode = $dom->documentElement;
	
		foreach ($sections as $sectionName)
		{
			$childSectionNode = null;
			foreach ($sectionNode->childNodes as $entryNode)
			{
				if ($entryNode->nodeType === XML_ELEMENT_NODE && $entryNode->nodeName === $sectionName)
				{
					$childSectionNode = $entryNode;
					break;
				}
			}
			if ($childSectionNode === null)
			{
				$childSectionNode = $sectionNode->appendChild($dom->createElement($sectionName));
			}
			$sectionNode = $childSectionNode;
		}
	
		foreach ($sectionNode->childNodes as $entryNode)
		{
			if ($entryNode->nodeType === XML_ELEMENT_NODE && $entryNode->getAttribute('name') === $entryName)
			{
				$oldValue = $entryNode->textContent;
				break;
			}
		}
		if ($oldValue !== $value)
		{
			if ($value === null)
			{
				$sectionNode->removeChild($entryNode);
	
				while (!$sectionNode->hasChildNodes() && $sectionNode->nodeName !== 'config')
				{
					$pnode = $sectionNode->parentNode;
					$pnode->removeChild($sectionNode);
					$sectionNode = $pnode;
				}
			}
			elseif ($oldValue === null)
			{
				$entryNode = $sectionNode->appendChild($dom->createElement('entry'));
				$entryNode->setAttribute('name', $entryName);
				$entryNode->appendChild($dom->createTextNode($value));
			}
			else
			{
				while ($entryNode->hasChildNodes())
				{
					$entryNode->removeChild($entryNode->firstChild);
				}
				$entryNode->appendChild($dom->createTextNode($value));
			}
			$dom->save($configProjectPath);
		}
	
		return $oldValue;
	}
}