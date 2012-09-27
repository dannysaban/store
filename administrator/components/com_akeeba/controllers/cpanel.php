<?php
/**
 * @package AkeebaBackup
 * @copyright Copyright (c)2009-2012 Nicholas K. Dionysopoulos
 * @license GNU General Public License version 3, or later
 *
 * @since 1.3
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * The Control Panel controller class
 *
 */
class AkeebaControllerCpanel extends FOFController
{
	public function execute($task) {
		if($task != 'switchprofile') $task = 'browse';
		parent::execute($task);
	}
	
	public function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();
		if($result) {
			$model = $this->getThisModel();
			$view = $this->getThisView();
			$view->setModel($model);
			
			$aeconfig = AEFactory::getConfiguration();

			// Invalidate stale backups
			AECoreKettenrad::reset( array('global'=>true,'log'=>false,'maxrun' => 180) );

			// Just in case the reset() loaded a stale configuration...
			AEPlatform::getInstance()->load_configuration();

			// Let's make sure the temporary and output directories are set correctly and writable...
			$wizmodel = FOFModel::getAnInstance('Confwiz','AkeebaModel');
			$wizmodel->autofixDirectories();

			// Check if we need to toggle the settings encryption feature
			$model->checkSettingsEncryption();
			// Update the magic component parameters
			$model->updateMagicParameters();

			// Check the last installed version
			$versionLast = null;
			if(file_exists(JPATH_COMPONENT_ADMINISTRATOR.'/akeeba.lastversion.php')) {
				include_once JPATH_COMPONENT_ADMINISTRATOR.'/akeeba.lastversion.php';
				if(defined('AKEEBA_LASTVERSIONCHECK')) $versionLast = AKEEBA_LASTVERSIONCHECK;
			}
			if(is_null($versionLast)) {
				$component = JComponentHelper::getComponent( 'com_akeeba' );
				if(is_object($component->params) && ($component->params instanceof JRegistry)) {
					$params = $component->params;
				} else {
					$params = new JParameter($component->params);
				}
				$versionLast = $params->get('lastversion','');
			}
			if(version_compare(AKEEBA_VERSION, $versionLast, 'ne') || empty($versionLast)) {
				$this->setRedirect('index.php?option=com_akeeba&view=postsetup');
				return;
			}
		}
		return $result;
	}
	
	public function switchprofile()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			$this->_csrfProtection();
		}
		
		$newProfile = FOFInput::getInt('profileid', -10, $this->input);

		if(!is_numeric($newProfile) || ($newProfile <= 0))
		{
			$this->setRedirect(JURI::base().'index.php?option=com_akeeba', JText::_('PANEL_PROFILE_SWITCH_ERROR'), 'error' );
			return;
		}

		$session = JFactory::getSession();
		$session->set('profile', $newProfile, 'akeeba');
		$url = '';
		$returnurl = FOFInput::getBase64('returnurl', '', $this->input);
		if(!empty($returnurl)) {
			$url = base64_decode($returnurl);
		}
		if(empty($url)) {
			$url = JURI::base().'index.php?option=com_akeeba';
		}
		$this->setRedirect($url, JText::_('PANEL_PROFILE_SWITCH_OK'));
	}


}