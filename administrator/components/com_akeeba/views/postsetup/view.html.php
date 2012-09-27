<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2012 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 * @since 3.3.b1
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * MVC View for Profiles management
 *
 */
class AkeebaViewPostsetup extends FOFViewHtml
{
	public function onBrowse($tpl = null)
	{
		$this->_setSRPStatus();
		$this->_setAutoupdateStatus();
		$this->_setConfWizStatus();
		$this->assign('showsrp', $this->isMySQL());

		AkeebaHelperIncludes::includeMedia(false);
		
		return true;
	}
	
	private function _setAutoupdateStatus()
	{
		if($this->_setConfWizStatus()) {
			$this->assign('enableautoupdate', true);
			return;
		}
		
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true)
			->select($db->nq('enabled'))
			->from($db->nq('#__extensions'))
			->where($db->nq('element').' = '.$db->q('oneclickaction'))
			->where($db->nq('folder').' = '.$db->q('system'));
		$db->setQuery($query);
		$enabledOCA = $db->loadResult();
		
		$query = $db->getQuery(true)
			->select($db->nq('enabled'))
			->from($db->nq('#__extensions'))
			->where($db->nq('element').' = '.$db->q('akeebaupdatecheck'))
			->where($db->nq('folder').' = '.$db->q('system'));
		$db->setQuery($query);
		$enabledAUC = $db->loadResult();
		
		if(!AKEEBA_PRO) {
			$enabledAUC = false;
			$enabledOCA = false;
		}
		
		$this->assign('enableautoupdate', $enabledAUC && $enabledOCA);
	}
	
	private function _setSRPStatus()
	{
		if($this->_setConfWizStatus()) {
			$this->assign('enablesrp', $this->isMySQL());
			return;
		}
		
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true)
			->select($db->nq('enabled'))
			->from($db->nq('#__extensions'))
			->where($db->nq('element').' = '.$db->q('srp'))
			->where($db->nq('folder').' = '.$db->q('system'));
		$db->setQuery($query);
		$enableSRP = $db->loadResult();
		
		if(!AKEEBA_PRO) {
			$enableSRP = false;
		}
		if(!$this->isMySQL()) {
			$enableSRP = false;
			return;
		}
		
		$this->assign('enablesrp', $enableSRP ? true : false);	
	}
	
	private function _setConfWizStatus()
	{
		static $enableconfwiz;
		
		if(empty($enableconfwiz)) {
			$component = JComponentHelper::getComponent( 'com_akeeba' );
			if(is_object($component->params) && ($component->params instanceof JRegistry)) {
				$params = $component->params;
			} else {
				$params = new JParameter($component->params);
			}
			$lv = $params->get( 'lastversion', '' );
			$minStability = $params->get( 'minstability', 'stable' );
			
			$enableconfwiz = empty($lv);
		}
		
		$this->assign('enableconfwiz', $enableconfwiz);
		$this->assign('minstability', $minStability);
		return $enableconfwiz;
	}
	
	private function isMySQL()
	{
		$db = JFactory::getDbo();
		return strtolower(substr($db->name, 0, 5)) == 'mysql';
	}
}