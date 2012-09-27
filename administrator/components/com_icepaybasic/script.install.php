<?php



defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('JPATH_VM_ADMINISTRATOR') or define('JPATH_VM_ADMINISTRATOR', JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart');

// hack to prevent defining these twice in 1.6 installation
if (!defined('_VM_SCRIPT_INCLUDED')) {
	define('_VM_SCRIPT_INCLUDED', true);

class com_icepaybasicInstallerScript
{

	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function __constructor($adapter){

        }
 
	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($route, $adapter){
        
            
        }
 
	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent=null) {
            
            if (!$this->loadVm()) return false;
            

            $this->installPlugin('VM - Payment, ICEPAY (Basic)', 'plugin','icepaybasic', 'vmpayment');

            return true;
        }
 
	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install($loadVm = true){
            
            
        }
 
	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update($adapter){
            
        }
 
	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall($adapter=null){
            
        }
        
        
        public function loadVm() {
                $this->path = JInstaller::getInstance()->getPath('extension_administrator');

                
                
                if (!class_exists( 'VmConfig' )){
                    $file = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php';
                    if (file_exists($file)){
                        require($file);
                    } else {
                        $app = JFactory::getApplication();
			$app -> enqueueMessage( get_class( $this ).':: VirtueMart2 must be installed ');
                        return false;
                    }
                }
                    
                    
                //require_once(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'config.php');
//                
//                if (class_exists( 'VmConfig' )){
//				$pluginfilename = $dst.DS.$element.'.php';
//				require ($pluginfilename);
//
//				//plgVmpaymentPaypal
//				$pluginClassname = 'plg'.ucfirst($group).ucfirst($element);
//
//				//Let's get the global dispatcher
//				$dispatcher = JDispatcher::getInstance();
//				$config = array('type'=>$group,'name'=>$group,'params'=>'');
//				$plugin = new $pluginClassname($dispatcher,$config);;
//				// 				$updateString = $plugin->getVmPluginCreateTableSQL();
//// 				if(function_exists('getTableSQLFields')){
//
//					$_psType = substr($group, 2);
//					$tablename = '#__virtuemart_'.$_psType .'_plg_'. $element;
//
//
//					$update[$tablename]= array($plugin->getTableSQLFields(), array(),array());
//					$app = JFactory::getApplication();
//					$app -> enqueueMessage( get_class( $this ).':: VirtueMart2 update '.$tablename);
//
//					if(!class_exists('GenericTableUpdater')) require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'tableupdater.php');
//					$updater = new GenericTableUpdater();
//
//					$updater->updateMyVmTables($update);
//// 				} else {
//
//// 					$app = JFactory::getApplication();
//// 					$app -> enqueueMessage( get_class( $plugin ).':: VirtueMart2 function getTableSQLFields not found');
//
//// 				}
//
//			} else {
//				$app = JFactory::getApplication();
//				$app -> enqueueMessage( get_class( $this ).':: VirtueMart2 must be installed, or the tables cant be updated '.$error);
//
//			}
//                
//                JTable::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'tables');
//                JModel::addIncludePath(JPATH_VM_ADMINISTRATOR.DS.'models');
                return true;
        }

        public function checkIfUpdate(){

                return false;
        }
        
        public function createIndexFolder($path){
                if(!class_exists('JFile')) require(JPATH_VM_LIBRARIES.DS.'joomla'.DS.'filesystem'.DS.'file.php');
                if(JFolder::create($path)) {
                        if(!JFile::exists($path .DS. 'index.html')){
                                JFile::copy(JPATH_ROOT.DS.'components'.DS.'index.html', $path .DS. 'index.html');
                        }
                        return true;
                }
                return false;
        }

        private function recurse_copy($src,$dst ) {

			$dir = opendir($src);
			$this->createIndexFolder($dst);

			if(is_resource($dir)){
				while(false !== ( $file = readdir($dir)) ) {
					if (( $file != '.' ) && ( $file != '..' )) {
						if ( is_dir($src .DS. $file) ) {
							$this->recurse_copy($src .DS. $file,$dst .DS. $file);
						}
						else {
							if(JFile::exists($dst .DS. $file)){
								if(!JFile::delete($dst .DS. $file)){
									$app = JFactory::getApplication();
									$app -> enqueueMessage('Couldnt delete '.$dst .DS. $file);
								}
							}
							if(!JFile::move($src .DS. $file,$dst .DS. $file)){
								$app = JFactory::getApplication();
								$app -> enqueueMessage('Couldnt move '.$src .DS. $file.' to '.$dst .DS. $file);
							}
						}
					}
				}
				closedir($dir);
				if (is_dir($src)) JFolder::delete($src);
			} else {
				$app = JFactory::getApplication();
				$app -> enqueueMessage('Couldnt read dir '.$dir.' source '.$src);
			}

		}
        
        
        
        
        private function installPlugin($name, $type, $element, $group){

			$task = JRequest::getCmd('task');
			if($task!='updateDatabase'){
			$data = array();

			if(version_compare(JVERSION,'1.7.0','ge')) {

				// Joomla! 1.7 code here
				$table = JTable::getInstance('extension');
				$data['enabled'] = 1;
				$data['access']  = 1;
				$tableName = '#__extensions';
				$idfield = 'extension_id';
			} elseif(version_compare(JVERSION,'1.6.0','ge')) {

				// Joomla! 1.6 code here
				$table = JTable::getInstance('extension');
				$data['enabled'] = 1;
				$data['access']  = 1;
				$tableName = '#__extensions';
				$idfield = 'extension_id';
			} else {

				// Joomla! 1.5 code here
				$table = JTable::getInstance('plugin');
				$data['published'] = 1;
				$data['access']  = 0;
				$tableName = '#__plugins';
				$idfield = 'id';
			}

			$data['name'] = $name;
			$data['type'] = $type;
			$data['element'] = $element;
			$data['folder'] = $group;

			$data['client_id'] = 0;


			$src= $this->path .DS. 'plugins' .DS. $group .DS.$element;


			$db = JFactory::getDBO();
			$q = 'SELECT '.$idfield.' FROM `'.$tableName.'` WHERE `name` = "'.$name.'" ';
			$db->setQuery($q);
			$count = $db->loadResult();

			if(!empty($count)){
				$table->load($count);
				if(empty($table->manifest_cache)){
					if(version_compare(JVERSION,'1.6.0','ge')) {
						$data['manifest_cache'] = json_encode(JApplicationHelper::parseXMLInstallFile($src.DS.$element.'.xml'));
					}
				}
			}

			if(!$table->bind($data)){
				$app = JFactory::getApplication();
				$app -> enqueueMessage('VMInstaller table->bind throws error for '.$name.' '.$type.' '.$element.' '.$group);
			}

			if(!$table->check($data)){
				$app = JFactory::getApplication();
				$app -> enqueueMessage('VMInstaller table->check throws error for '.$name.' '.$type.' '.$element.' '.$group);

			}

			if(!$table->store($data)){
				$app = JFactory::getApplication();
				$app -> enqueueMessage('VMInstaller table->store throws error for '.$name.' '.$type.' '.$element.' '.$group);
			}

			$errors = $table->getErrors();
			foreach($errors as $error){
				$app = JFactory::getApplication();
				$app -> enqueueMessage( get_class( $this ).'::store '.$error);
			}

			}
			if(version_compare(JVERSION,'1.7.0','ge')) {
				// Joomla! 1.7 code here
				$dst= JPATH_ROOT . DS . 'plugins' .DS. $group.DS.$element;

			} elseif(version_compare(JVERSION,'1.6.0','ge')) {
				// Joomla! 1.6 code here
				$dst= JPATH_ROOT . DS . 'plugins' .DS. $group.DS.$element;
			} else {
				// Joomla! 1.5 code here
				$dst= JPATH_ROOT . DS . 'plugins' .DS. $group;
			}

                        /* Copy plugin files */
                        $src = $this->path .DS. 'files'.DS.'plugins'.DS.'vmpayment';
                        $this->recurse_copy($src,$dst);
                        
                        /* Copy admin */
                        $src = $this->path .DS. 'files'.DS.'administrator';
                        $dst = JPATH_ROOT . DS . 'administrator';
                        $this->recurse_copy($src,$dst);
                        
                        /* Copy component files */
                        $src = $this->path .DS. 'files'.DS.'components';
                        $dst = JPATH_ROOT . DS . 'components';
                        $this->recurse_copy($src,$dst);
                        
                        /* Copy images */
                        $src = $this->path .DS. 'files'.DS.'images';
                        $dst = JPATH_ROOT . DS . 'images';
                        $this->recurse_copy($src,$dst);


		}
        
        
        
}




/* 1.5 support */
function com_install() {
    

        $vmInstall = new com_icepaybasicInstallerScript();
        $upgrade = $vmInstall->checkIfUpdate();

        if(version_compare(JVERSION,'1.6.0','ge')) {
                // Joomla! 1.6 code here
        } else {
                // Joomla! 1.5 code here
                $method = ($upgrade) ? 'update' : 'install';

                $vmInstall->$method();
                $vmInstall->postflight($method);
        }

        return true;
}

/**
 * Legacy j1.5 function to use the 1.6 class uninstall
 *
 * @return boolean True on success
 * @deprecated
 */
function com_uninstall() {
        $vmInstall = new com_icepaybasicInstallerScript();

        if(version_compare(JVERSION,'1.6.0','ge')) {
                // Joomla! 1.6 code here
        } else {
                $vmInstall->uninstall();
                $vmInstall->postflight('uninstall');
        }

        return true;
}

}