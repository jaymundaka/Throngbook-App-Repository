<?php
/**
 * @category 	Library
 * @package		JomSocial
 * @subpackage	Core 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php 
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );
jimport('joomla.event.dispatcher');

require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'defines.community.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'models'.DS.'models.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'error.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'apps.php' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'cache.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'storage.php' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'parameter.php' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'helpers'.DS.'plugins.php' );
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_community'.DS.'tables'.DS.'cache.php');

JTable::addIncludePath( JPATH_ROOT . DS . 'administrator'.DS.'components'.DS.'com_community'.DS.'tables' );
JTable::addIncludePath( COMMUNITY_COM_PATH . DS . 'tables' );

//define ZEND_PATH
if( !defined( 'ZEND_PATH' ) ) {
	define('ZEND_PATH',CPluginHelper::getPluginPath('system', 'zend'));
}

// In case our core.php files are loaded by 3rd party extensions, we need to define the plugin path environments for Zend.
$paths	= explode( PATH_SEPARATOR , get_include_path() );

if( !in_array( ZEND_PATH, $paths ) )
{
	set_include_path('.'
	    . PATH_SEPARATOR . ZEND_PATH
	    . PATH_SEPARATOR . get_include_path()
	);
}


if (!class_exists('Zend_Cache'))
{
	$zendAutoLoader	= ZEND_PATH.DS.'Zend'.DS.'Loader'.DS.'Autoloader.php';
	$isZendEnabled	= JPluginHelper::isEnabled('system', 'zend');
	$isLoaderExists	= JFile::exists($zendAutoLoader);
	
	if ($isZendEnabled && $isLoaderExists)
	{
		// Only include the zend loader if it has not been loaded first
		include_once($zendAutoLoader);
		// register auto-loader
		$loader = Zend_Loader_Autoloader::getInstance();
	} 
	else if( JRequest::getVar('option', '', 'REQUEST') != 'com_community' ) 
	{
		// No need to show this message if we're in option=com_community since
		// main entry point, community.php already display this message
		$message 	= JText::_('COM_COMMUNITY_ZEND_PLUGIN_DISABLED');
		$mainframe	= JFactory::getApplication();
		$mainframe->enqueueMessage($message, 'error');
	}
}

$lang =& JFactory::getLanguage();
$lang->load('com_community');

class CFactory
{
	
	/**
	 * Function to allow caller to get a user object while
	 * it is not authenticated provided that it has a proper tokenid
	 **/	 
	public function getUserFromTokenId( $tokenId , $userId )
	{
		$db		=& JFactory::getDBO();
		
		$query	= 'SELECT COUNT(*) '
				. 'FROM ' . $db->nameQuote( '#__community_photos_tokens') . ' ' 
				. 'WHERE ' . $db->nameQuote( 'token') . '=' . $db->Quote( $tokenId ) . ' '
				. 'AND ' . $db->nameQuote( 'userid') . '=' . $db->Quote( $userId );

		$db->setQuery( $query );
		
		$count	= $db->loadResult();
		
		// We can assume that the user parsed in correct token and userid. So,
		// we return them the proper user object.

		if( $count >= 1 )
		{
			$user	= CFactory::getUser( $userId );
			
			return $user;			
		}

		// If it doesn't bypass our tokens, we assume they are really trying
		// to hack or got in here somehow.
		$user	= CFactory::getUser( null );
		
		return $user;
	}
	
	/**
	 * Load multiple users at a same time to save up on the queries.
	 * @return	boolean		True upon success
	 * @param	Array	$userIds	An array of user ids to be loaded.
	 */
	public function loadUsers($userIds)
	{
		if(empty($userIds))
			return;
		
		$ids = implode(",", $userIds);
		$db		=& JFactory::getDBO();
		$query  = 'SELECT  '
				. '	a.' . $db->nameQuote('userid') .' as _userid ,'
				. '	a.' . $db->nameQuote('status') .' as _status , '
				. '	a.' . $db->nameQuote('points') .' as _points, '
				. '	a.' . $db->nameQuote('posted_on') .' as _posted_on, ' 	
				. '	a.' . $db->nameQuote('avatar') .'	as _avatar , '
				. '	a.' . $db->nameQuote('thumb') .'	as _thumb , '
				. '	a.' . $db->nameQuote('invite') .'	as _invite, '
				. '	a.' . $db->nameQuote('params') .'	as _cparams,  '
				. '	a.' . $db->nameQuote('view') .'	as _view, '
				. ' a.' . $db->nameQuote('friends') .' as _friends, '
				. ' a.' . $db->nameQuote('alias') .'	as _alias, '
				. ' a.' . $db->nameQuote('profile_id') .' as _profile_id, '
				. ' a.' . $db->nameQuote('friendcount') .' as _friendcount, '
				. ' a.' . $db->nameQuote('storage') .' as _storage, '
				. ' a.' . $db->nameQuote('watermark_hash') .' as _watermark_hash, '
				. ' a.' . $db->nameQuote('search_email') .' AS _search_email, '
				. ' s.' . $db->nameQuote('userid') .' as _isonline, u.* '
				. ' FROM ' . $db->nameQuote('#__community_users') .' as a '
				. ' LEFT JOIN ' . $db->nameQuote('#__users') .' u '
	 			. ' ON u.' . $db->nameQuote('id') .'=a.' . $db->nameQuote('userid') 
				. ' LEFT OUTER JOIN ' . $db->nameQuote('#__session') . 's '
	 			. ' ON s.' . $db->nameQuote('userid') .'=a.' . $db->nameQuote('userid')
				. ' WHERE a.' . $db->nameQuote('userid') .' IN (' . $ids.')';
				
		$db->setQuery($query);
		$objs = $db->loadObjectList();
		
		foreach($objs as $obj){
		
			$user 			= new CUser($obj->_userid);
			$isNewUser		= $user->init($obj);
			$user->getThumbAvatar();
			
			// technically, we should not fetch any new user here
			if( $isNewUser )
			{
				// New user added to jomSocial database
				// trigger event onProfileInit
				$appsLib	= CAppPlugins::getInstance();
				$appsLib->loadApplications();
				
				$args 	= array();
				$args[] = $user;
				$appsLib->triggerEvent( 'onProfileCreate' , $args );
			}
			
			CFactory::getUser($obj->_userid, $user);
		}
	}
	
	/**
	 * Retrieves a CUser object given the user id.
	 * @return	CUser	A CUser object
	 * @param	int		$id		A user id (optional)
	 * @param	CUser	$obj	An existing user object (optional)
	 */
	public function getUser($id=null, $obj = null)
	{
		static $instances = array();
		
		if($id != 0 && !is_null($obj)){
			$instances[$id] = $obj;
			return;
		}
		
		if($id === 0)
		{
			$user =& JFactory::getUser(0);
			$id = $user->id;
		}
		else
		{
			if($id == null)
			{
				$user =& JFactory::getUser();
				$id = $user->id;
			}
			
			if($id != null && !is_numeric($id)) 
			{
				$db		=& JFactory::getDBO();
				$query ='SELECT ' . $db->nameQuote('id') .' FROM ' . $db->nameQuote('#__users') .' WHERE UCASE(' . $db->nameQuote('username').') like UCASE('.$db->Quote($id).')';
				$db->setQuery($query);
				$id = $db->loadResult();
			}
		}
			
		if( empty($instances[$id]) )
		{
			if( !is_numeric($id) && !is_null($id)) 
			{
				JError::raiseError( 500, JText::sprintf('COM_COMMUNITY_CANNOT_LOAD_USER', $id) );
			}
			
			$instances[$id] = new CUser($id);
			$isNewUser		= $instances[$id]->init();
			$instances[$id]->getThumbAvatar();
			
			if( $isNewUser )
			{
				// New user added to jomSocial database
				// trigger event onProfileInit
				$appsLib	= CAppPlugins::getInstance();
				$appsLib->loadApplications();
				
				$args 	= array();
				$args[] = $instances[$id];
				$appsLib->triggerEvent( 'onProfileCreate' , $args );
			}
			
			// Guess need to have avatar as well.
			if($id == 0)
			{
				$lang =& JFactory::getLanguage();
				$lang->load('com_community');

				$instances[$id]->name = JText::_('COM_COMMUNITY_ACTIVITIES_GUEST');
				$instances[$id]->username = JText::_('COM_COMMUNITY_ACTIVITIES_GUEST');
				$instances[$id]->_avatar 	= 'components/com_community/assets/default.jpg';
				$instances[$id]->_thumb 	= 'components/com_community/assets/default_thumb.jpg';
			}
		}
		
		return $instances[$id];
	}
	
	
	/**
	 * Retrieves CConfig configuration object
	 * @return	object	CConfig object
	 * @param
	 */
	public function getConfig()
	{
		return CConfig::getInstance();
	}
	
	/**
	 * Return fast, memory-based JCache object. For now, only APC cache
	 * will be supported	 
	 */	 	
	public function getFastCache($type = 'output'){
		static $instance = null;
		
		if(is_null($instance)){
			jimport( 'joomla.cache.cache' );
			
			// Check for APC
			$options = array('storage' => 'apc', 'lifetime' => '300');
			if( extension_loaded('apc') ){
				$options['caching'] = true;
			} else{
				// Disable caching
				$options['caching'] = false;
			}
			
			$cache	    =	JCache::getInstance( $type , $options );
			$instance   =	new CFastCache($cache);
		}
		
		return $instance;
	}
	
	/**
	 * Returns a configured version of Zend_Cache object
	 * 	 
	 * @param	string	$frontendEngine		Frontend engine to use
	 * @param	string	$frontendOptions	Additional options for the frontend object
	 * @return	object	Zend_Cache object	 
	 */
	public function getCache($frontendEngine, $frontendOptions= array())
	{
		$jConfig	=& JFactory::getConfig();
	
		// If Joomla cache folder does not exist, try to create them.
		if( !JFolder::exists( JPATH_ROOT . DS . 'cache') )
		{
			JFolder::create( JPATH_ROOT . DS . 'cache' );
			JFile::copy( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'index.html' , JPATH_ROOT . DS . 'cache' . DS . 'index.html' );
		}
		
		// Configure additional options for frontend
		$defaultOptions = array(
					'lifetime' => 86400, // cache lifetime of 24 hours (time is in seconds)
					'automatic_serialization' => true,  	//default is false
					'cache_id_prefix' => '_com_community', //prefix
					'caching' => $jConfig->getValue('caching') // enable or disable caching
					); 
					 
		switch($frontendEngine) {
			case 'Core':
				break;
			case 'Output':
				break;
			case 'Class':
				break;
			case 'Function':
				break;
				
			
		}
		$frontendOptions = array_merge($defaultOptions, $frontendOptions);
		
		$backendOptions = array();
		$backendEngine = '';
		
		switch($jConfig->getValue('cache_handler')){
			case 'file':
				$backendOptions = array(
					'cache_dir' => JPATH_ROOT.DS.'cache'.DS);
				$backendEngine = 'File';
				break;
			case 'apc':
				$backendOptions = array(
					'cache_dir' => JPATH_ROOT.DS.'cache');
				$backendEngine = 'Apc';
				break;
				
			// not supportted for now, return a dummy cache object
			case 'xcache':
			case 'eaccelerator':
			case 'memcache':
				$backendOptions = array(
					'cache_dir' => JPATH_ROOT.DS.'cache'.DS);
				$backendEngine = 'File';
				$frontendOptions['caching'] = FALSE;
				break;
		}
		
		$zend_cache = Zend_Cache::factory(
			$frontendEngine, $backendEngine, 
			$frontendOptions, $backendOptions);
		return $zend_cache;

	}	
	
	/**
	 * Register autoload functions, using JImport::JLoader
	 */	 	
	public function autoload()
	{
		//JLoader::register('classname', 'filename');
	}	
	
	
	/**
	 * Return the model object, responsible for all db manipulation. Singleton
	 * 	 
	 * @param	string		model name
	 * @param	string		any class prefix
	 * @return	object		model object	 
	 */	 	
	public function getModel( $name = '', $prefix = '', $config = array() )
	{
		static $modelInstances = null;
		
		if(!isset($modelInstances)){
			$modelInstances = array();
			include_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'error.php');
		}
		
		if(!isset($modelInstances[$name.$prefix]))
		{
			include_once( JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'models'.DS.'models.php');
			
			// @rule: We really need to test if the file really exists.
			$modelFile	= JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'models'.DS. strtolower( $name ) .'.php';
			if( !JFile::exists( $modelFile ) )
			{
				$modelInstances[ $name . $prefix ]	= false;
			}
			else
			{
				include_once( $modelFile );
				$classname = $prefix.'CommunityModel'.$name;
				$modelInstances[$name.$prefix] = new CCachingModel(new $classname);
			}			
		}
		
		return $modelInstances[$name.$prefix];
	}

	// @getBookmarks deprecated.
	// since 1.5
	public function getBookmarks( $uri )
	{
		static $bookmark = null;
		
		if( is_null($bookmark) )
		{
			CFactory::load( 'libraries' , 'bookmarks' );
			$bookmark	= new CBookmarks( $uri );
		}
		return $bookmark;
	}
	
	public function getToolbar()
	{
		// We need to load the language code here since some plugin
		// apparently modify this before language code is loaded
		$lang =& JFactory::getLanguage();
		$lang->load( 'com_community' );
		
		static $toolbar = null ;
		
		if( is_null( $toolbar ) )
		{
			CFactory::load( 'libraries' , 'toolbar' );
			
			$toolbar = new CToolbar();
			
		}
		return $toolbar;
	}
	
	/**
	 * Return the view object, responsible for all db manipulation. Singleton
	 * 	 
	 * @param	string		model name
	 * @param	string		any class prefix
	 * @return	object		model object	 
	 */	 	
	public function getView( $name='', $prefix='', $viewType='' )
	{
		static $viewInstances = null;
		
		if(!isset($viewInstances)){
			$viewInstances = array();
			include_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'error.php');
		}
 
 		$viewType = JRequest::getVar('format', 'html', 'REQUEST');

 		// if(CTemplate::mobileTemplate())
 		// 	$viewType = 'mobile';

		if($viewType=='json')
			$viewType = 'html';
	
		if(!isset($viewInstances[$name.$prefix.$viewType]))
		{
			jimport( 'joomla.filesystem.file' );
			
			$viewFile	= JPATH_COMPONENT . DS . 'views' . DS . $name . DS . 'view.' . $viewType . '.php';

			if( JFile::exists($viewFile) )
			{
				include_once( $viewFile );
			}
			else
			{
				//@rule: when feed is not available, we include the main view file.
				if( $viewType == 'feed' )
				{
					include_once( JPATH_COMPONENT . DS . 'views' . DS . $name . DS . 'view.html.php' );
				}
			}
			
			// if( $viewType == 'mobile' )
			// {
			// 	$classname = $prefix.'CommunityViewMobile' . ucfirst($name);

			// 	// Temporary fallback for mobile view
			// 	if (!class_exists($classname))
			// 	{
			// 		$viewFile	= JPATH_COMPONENT . DS . 'views' . DS . $name . DS . 'view.html.php';

			// 		if( JFile::exists($viewFile) )
			// 		{
			// 			include_once( $viewFile );
			// 			$classname = $prefix.'CommunityView'. ucfirst($name);						
			// 		}					
			// 	}
			// }
			// else
			// {
			// 	$classname = $prefix.'CommunityView'. ucfirst($name);
			// }

			$classname = $prefix.'CommunityView'. ucfirst($name);
			$viewInstances[$name.$prefix.$viewType] = new $classname;
		}
		
		return $viewInstances[$name.$prefix.$viewType];
	}
		
	/**
	 * return the currently viewed user profile object, 
	 * for now, just return an object with username, id, email
	 * @deprecated since 1.6.x
	 */
	public function getActiveProfile(){

		$my = & JFactory::getUser();
		$uid = JRequest::getVar('userid', 0, 'REQUEST');
			
		if($uid == 0)
		{
			$uid = JRequest::getVar('activeProfile', null, 'COOKIE');
		}
		
		$obj = CFactory::getUser($uid);

		return $obj;
	}
	
	/**
	 * Returns the current user requested via JRequest::getVar. 'userid' should be part of the request
	 * parameter
	 *
	 * @param	 
	 * @return	object	Current CUser object 	 
	 */
	public function getRequestUser()
	{
		$id = JRequest::getVar('userid', '');

		return CFactory::getUser($id);
	}
	
	/**
	 * Return standard joomla filter objects
	 *
	 * @param	boolean		$allowHTML	True if you want to allow safe HTML through
	 * @param	boolean		$simpleHTML	True if you want to allow simple HTML tags	 
	 * @return	object		JFilterInput object
	 */
	public function getInputFilter($allowHTML = false, $simpleHTML = false)
	{
		jimport('joomla.filter.filterinput');
		$safeTags	= array();
		$safeAttr 	= array();
		
		if($allowHTML)
		{
			$safeAttr = array('abbr', 'accept', 'accept-charset', 'accesskey', 'action', 'align', 'alt', 'axis', 'border', 'cellpadding', 'cellspacing', 'char', 'charoff', 'charset', 'checked', 'cite', 'class', 'clear', 'cols', 'colspan', 'color', 'compact', 'coords', 'datetime', 'dir', 'disabled', 'enctype', 'for', 'frame', 'headers', 'height', 'href', 'hreflang', 'hspace', 'id', 'ismap', 'label', 'lang', 'longdesc', 'maxlength', 'media', 'method', 'multiple', 'name', 'nohref', 'noshade', 'nowrap', 'prompt', 'readonly', 'rel', 'rev', 'rows', 'rowspan', 'rules', 'scope', 'selected', 'shape', 'size', 'span', 'src', 'start', 'style', 'summary', 'tabindex', 'target', 'title', 'type', 'usemap', 'valign', 'value', 'vspace', 'width');
			$safeTags = array('a', 'abbr', 'acronym', 'address', 'area', 'b', 'big', 'blockquote', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'fieldset', 'font', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'map', 'menu', 'ol', 'optgroup', 'option', 'p', 'pre', 'q', 's', 'samp', 'select', 'small', 'span', 'strike', 'strong', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'tr', 'tt', 'u', 'ul', 'var');
		}
		$safeHtmlFilter =  JFilterInput::getInstance($safeTags, $safeAttr);
		
		return $safeHtmlFilter;
	}
	
	/**
	 * Set current active profile
	 * @param	integer id	Current user id
	 * @deprecated	since 1.6.x
	 */	 	
	public function setActiveProfile($id = '')
	{	
		if( empty($id) )
		{
			$my = CFactory::getUser();
			$id = $my->id;
		}
		$jConfig	=& JFactory::getConfig();
		$lifetime	= $jConfig->getValue('lifetime');
			
		setcookie('activeProfile', $id, time() + ($lifetime * 60) , '/');
	}
	
	/**
	 * @deprecated since 1.6.x
	 */	 	 	 	
	public function unsetActiveProfile()
	{
		$jConfig	=& JFactory::getConfig();
		$lifetime	= $jConfig->getValue('lifetime');
		
		setcookie('activeProfile', false , time() + ($lifetime * 60 ), '/');
	}

	/**
	 * Sets the current requested URI in the cookie so the system knows where it should
	 * be redirected to.
	 *
	 * @param	 
	 * @return
	 */
	public function setCurrentURI()
	{
		$uri 		=& JFactory::getURI();
		$current	= $uri->toString();

		setcookie( 'currentURI' , $current , time() + 60 * 60 * 24 , '/' );
	}

	/**
	 * Gets the last accessed URI from the cookie if user is coming from another page.
	 * 	 
	 * @param
	 * @return	string	The last accessed URI in the cookie (E.g http://site.com/index.php)
	 */
	public function getLastURI()
	{
		$uri	= JRequest::getVar( 'currentURI' , null , 'COOKIE' );
		
		if( is_null( $uri ) )
		{
			$uri	= JURI::root();
		}
		return $uri;
	}
	
	/**
	 * Return the view object, responsible for all db manipulation. Singleton
	 * 	 
	 * @param	string		type	libraries/helper
	 * @param	string		name 	class prefix
	 */	 	
	public function load( $type, $name )
	{
		include_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'error.php');
		
		// Test if file really exists before php throws errors.
		$path	= JPATH_ROOT.DS.'components'.DS.'com_community'.DS.$type.DS. strtolower($name) .'.php';
		if( JFile::exists( $path ) )
		{
			include_once( $path );
		}
				
		// If it is a library, we call the object and call the 'load' method
		if( $type == 'libraries' )
		{
			$classname = 'C'.$name ;
			if(	class_exists($classname) ) {
				// OK, class exist
			}
		}
	}
		
}

/**
 * Global Asset manager
 */
class CAssets 
{
	/**
	 * Centralized location to attach asset to any page. It avoids duplicate 
	 * attachement
	 */
	static function attach( $path, $type , $assetPath = ''){
		
		$document =& JFactory::getDocument();

		if ($document->getType()!='html')
			return;
		
		CFactory::load( 'helpers' , 'template' );
		CFactory::load( 'libraries' , 'template' );
		
		$template = new CTemplateHelper();
		$config   = CFactory::getConfig();

		// (Temporary)
		// Quick fix to prevent loading of unwanted stuff
		// on mobile view because it'll take a bit of work
		// to identify where they came from
		// (Temporary)
		// if (CTemplate::mobileTemplate())
		// {
		// 	if (strstr($path, 'window-1.0') || strstr($path, 'style.css') || strstr($path, 'script-1.2') || strstr($path, 'window.css') )
		// 		return;
		// }


		if (!defined('C_ASSET_JQUERY'))
		{
			$jQuery = $template->getTemplateAsset('joms.jquery', 'js');
			$document->addScript($jQuery->url);
			define('C_ASSET_JQUERY', 1);
		}



		static $added=false;
		if (!$added)
		{
			// Ensure our script is loaded before anything else.
			// if (CTemplate::mobileTemplate())
			// {
			// 	$script = $template->getTemplateAsset('script.mobile-1.0', 'js');
			// }
			// else
			// {
			// 	$script = $template->getTemplateAsset('script-1.2', 'js');
			// }

			$script = $template->getTemplateAsset('script-1.2', 'js');
			$document->addScript($script->url);

			$signature = md5($script->url);
			define('C_ASSET_' . $signature, 1);
			$added	= true;
		}

		if( !empty($assetPath) )
		{
			$path = $assetPath . $path;
		}
		else
		{
			$path = JURI::root() . 'components/com_community/' . JString::ltrim( $path , '/' );
		}
	
		if( !defined( 'C_ASSET_' . md5($path) ) ) 
		{
			define( 'C_ASSET_' . md5($path), 1 );
			
			switch( $type )
			{
				case 'js':
					$document->addScript($path);
					break;
					
				case 'css':
					$document->addStyleSheet($path);
			}
		}
	}	 
}	

/**
 * Provide global access to JomSocial configuration and paramaters
 */ 


jimport('joomla.html.parameter');

/**
 * Provide global access to JomSocial configuration and paramaters
 */ 
class CConfig /* extends JParameter*/
{
	var $_defaultParam;
	private $_jparam = null;
	
	/**
	 * Return reference to global config object
	 * 	 
	 * @return	object		JParams object	 
	 */
	static public function &getInstance()
	{
		static $instance = null;
		if(!$instance)
		{
			jimport('joomla.filesystem.file');
			
			// First we need to load the default INI file so that new configuration,
			// will not cause any errors.
			$ini	= JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_community' . DS . 'default.ini';
			$data	= JFile::read($ini);
			
			$instance = new CConfig();
			$instance->_jparam = new CParameter($data);

			JTable::addIncludePath( COMMUNITY_COM_PATH . DS . 'tables' );
			$config	=& JTable::getInstance( 'configuration' , 'CommunityTable' );
			$config->load( 'config' );
			
			$instance->_jparam->bind( $config->params );

			// call trigger to allow configuration override
			$appsLib	= CAppPlugins::getInstance();
			$appsLib->loadApplications();
			
			$args 	= array();
			$args[]	= $instance;
			$appsLib->triggerEvent( 'onAfterConfigCreate' , $args );

			 /*
                         * To fix the date format when setting format is set to Fixed.
                         * This function retrieves the wrong format and set it to the correct one.
                         */
                        
                        if(C_JOOMLA_15==0){
                             $instance->_jparam->set('activitiesdayformat', 'F d');
                             $instance->_jparam->set('activitiestimeformat', 'H:i A');
                        }
		}
		
		return $instance;
	}
	
	/**
	 * Sets a specific property of the config value
	 * 
	 * @param	string	$key
	 * @param	string	$value
	 * 
	 **/	 	 	 	 	 	
	public function set( $key , $value )
	{
		$this->_jparam->set( $key , $value );
	}
	
	/**
	 * Redirect all call to JParams object to $this->_jparam->
	 * @param <type> $name
	 * @param <type> $arguments
	 */
	public function __call($name, $arguments) {
        // call_user_func($this->_jparam->$name, $arguments);
    }


	
	/**
	 * Get a value
	 *
	 * @access	public
	 * @param	string The name of the param
	 * @param	mixed The default value if not found
	 * @return	string
	 */
	public function get($key, $default = '', $group = '_default')
	{
		$value = $this->_jparam->get($key, $default, $group);
		
		// Backward compatibility support since now configuration words are split by an underscore.
		if( empty( $value ) )
		{
			$key	= CString::str_ireplace('_' , '' , $key );
			$value	= $this->_jparam->get( $key , $default , $group );
		}
		
		return $value;
	}
	
	public function getString($key, $default = '', $group = '_default')
	{
		$value = $this->get($key, $default, $group);
		return (string) $value;
	}
	
	public function getBool($key, $default = '', $group = '_default')
	{
		$value = $this->get($key, $default, $group);
		return (bool) $value;
	}
	
	public function getInt($key, $default = '', $group = '_default')
	{
		$value = $this->get($key, $default, $group);
		return (int) $value;
	}
}




class CApplications extends JPlugin
{
	//@todo: Do some stuff so the childs get inheritance?
	var $params	= '';

	public function CApplications(& $subject, $config = null)
	{
		// Set the params for the current object
		parent::__construct($subject, $config);
		//$this->_getUserParams(  );
	}

	/**
	 * Function is Deprecated.
	 * -	 Should only be used in profile area.
	 **/	 	
	public function loadUserParams()
	{
		$model	= CFactory::getModel( 'apps' );
		
		$my		= CFactory::getUser();
		$userid	= JRequest::getVar('userid', $my->id);
		$user	= CFactory::getUser($userid);
		
		//$user	= CFactory::getActiveProfile();
		$appName = $this->_name;
		
		$position = $model->getUserAppPosition($user->id, $appName);		
		$this->params->set('position', $position , 'content');
		
		$params	= $model->getUserAppParams( $model->getUserApplicationId( $appName , $user->id ) );

		$this->userparams	= new CParameter( $params );
	}
	
	public function setNewLayout($position="")
	{
		$layout = !empty($position)? $position : 'content';
		$this->params->set('newlayout', $layout);
		return true;
	}
	
	public function getLayout()
	{
		$newLayout = $this->params->get('newlayout', '');
		$currentLayout = $this->params->get('position');
		
		$layout = empty($newLayout)? $currentLayout : $newLayout;
		return $layout;
	}
	
	public function getRefreshAction($script=array())
	{
		return $script;
	}
}

class CUser extends JUser 
implements CGeolocationInterface {
	
	var $_userid	= 0;
	var $_status = '';
	var $_cparams		= null;
	var $_tooltip		= null;
	var $_points		= 0;
	var $_init			= false;
	var $_thumb			= '';
	var $_avatar		= '';
	var $_isonline		= false;
	var $_view			= 0;
	var $_posted_on		= null;
	var $_invite		= 0;
	var $_friendcount	= 0;
	var $_alias			= null;
	var $_profile_id	= 0;
	var $_storage		= 'file';
	var $_watermark_hash	= '';
	var $_search_email  = 1;
	var $_friends		= null;
	var $_groups		= null;
	
	/* interfaces */
	var $latitude	= null;
	var $longitude	= null;
	
	/**
	 * Constructor.
	 * Perform a shallow copy of JUser object	 
	 */	 	
	public function CUser($id){
		
		if($id == null) {
			$user =& JFactory::getUser($id);
			$id = $user->id;
		}
			
		$this->id = $id;	
	}
	
	/**
	 * Method to set the property when the properties are already
	 * assigned
	 * 
	 * @param property	The property value that needs to be changed
	 * @param value		The value that needs to be set
	 * 	 	 	 	 	 
	 **/	 	 	
	public function set( $property , $value )
	{
		CError::assert( $property , '' , '!empty' , __FILE__ , __LINE__ );
		$this->$property	= $value;
	}
	
	public function getAlias()
	{
		// If the alias is not yet defined, return the default value
		if(empty( $this->_alias ))
		{
			$config	= CFactory::getConfig();
			$name	= $config->get( 'displayname' );
			$alias	= JFilterOutput::stringURLSafe( $this->$name );
			$this->_alias	= $this->id . ':' . $alias;
			
			// Save the alias into the database when the user alias is not generated.l
			$this->save();
		}

		return $this->_alias;
	}
	
	public function delete()
	{
		$db		=& JFactory::getDBO();

		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_users' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'userid' ) . '=' . $db->Quote( $this->id );
		$db->setQuery( $query );
		$db->query();
		
		return parent::delete();
	}

	public function getAppParams( $appName )
	{
		$model	= CFactory::getModel( 'apps' );
		$params	= $model->getUserAppParams( $model->getUserApplicationId( $appName , $this->id ) );
		$params	= new CParameter( $params );
		
		return $params;
	}
	
	/**
	 * Inititalize the user JUser object
	 * return true if the user is a new 	 
	 */	 	
	public function init($initObj = null) {
		$isNewUser = false;
		
		if(!$this->_init) {
			$db		=& JFactory::getDBO();
			$obj = $initObj;
			
			if($initObj == null ){
				$query  = 'SELECT  '
						. '	a.' . $db->nameQuote('userid') .' as _userid , '
						. '	a.' . $db->nameQuote('status') .' as _status , '
						. '	a.' . $db->nameQuote('points') .'	as _points, '
						. '	a.' . $db->nameQuote('posted_on') .' as _posted_on, ' 	
						. '	a.' . $db->nameQuote('avatar') .'	as _avatar , '
						. '	a.' . $db->nameQuote('thumb') .'	as _thumb , '
						. '	a.' . $db->nameQuote('invite') .'	as _invite, '
						. '	a.' . $db->nameQuote('params') .'	as _cparams,  '
						. '	a.' . $db->nameQuote('view') .'	as _view, '
						. ' a.' . $db->nameQuote('friends') .' as _friends, '
						. ' a.' . $db->nameQuote('groups') .' as _groups, '
						. ' a.' . $db->nameQuote('friendcount') .' as _friendcount, '
						. ' a.' . $db->nameQuote('alias') .' as _alias, '
						. ' a.' . $db->nameQuote('profile_id') .' as _profile_id, '
						. ' a.' . $db->nameQuote('storage') .' as _storage, '
						. ' a.' . $db->nameQuote('watermark_hash') .' as _watermark_hash, '
						. ' a.' . $db->nameQuote('search_email') .' as _search_email, '
						. ' s.' . $db->nameQuote('userid') .' as _isonline, u.* '
						. ' FROM ' . $db->nameQuote('#__community_users') .' as a '
						. ' LEFT JOIN ' . $db->nameQuote('#__users') .' u '
			 			. ' ON u.' . $db->nameQuote('id') .'=a.' . $db->nameQuote('userid')
						. ' LEFT OUTER JOIN ' . $db->nameQuote('#__session') .' s '
			 			. ' ON s.' . $db->nameQuote('userid') .'=a.' . $db->nameQuote('userid')
			 			. ' AND s.' . $db->nameQuote('client_id') .' !=' . $db->Quote('1')
						. ' WHERE a.' . $db->nameQuote('userid') .'=' . $db->Quote($this->id) ;
						
				$db->setQuery($query);
				$obj = $db->loadObject();
				if($db->getErrorNum())
				{
					JError::raiseError( 500, $db->stderr());
			    }
			} 

			// Initialise new user
			if(empty($obj))
			{
				if( !$obj && ($this->id != 0) )
				{
					// @rule: ensure that the id given is correct and exists in #__users
					$existQuery	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__users' ) . ' '
								. 'WHERE ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $this->id );
							
					$db->setQuery( $existQuery );

					$isValid	= $db->loadResult() > 0 ? true : false;
					
					if( $isValid )
					{
						// We need to create a new record for this specific user first.
						$config	= CFactory::getConfig();
						
						$obj = new stdClass();
						
						$obj->userid	= $this->id;
						$obj->points	= $this->_points;
						$obj->thumb		= '';
						$obj->avatar	= '';
						
						// Load default params				
						$obj->params = "notifyEmailSystem=" . $config->get('privacyemail') . "\n"
									 . "privacyProfileView=" . $config->get('privacyprofile') . "\n"
									 . "privacyPhotoView=" . $config->get('privacyphotos') . "\n"
									 . "privacyFriendsView=" . $config->get('privacyfriends') . "\n"
									 . "privacyGroupsView=" . $config->get('privacy_groups_list') . "\n"
									 . "privacyVideoView=" . $config->get('privacyvideos') . "\n"
									 . "notifyEmailMessage=" . $config->get('privacyemailpm') . "\n"
									 . "notifyEmailApps=" . $config->get('privacyapps') . "\n"
									 . "notifyWallComment=" . $config->get('privacywallcomment') . "\n";
						
						$db->insertObject( '#__community_users' , $obj );
		
						if($db->getErrorNum())
						{
							JError::raiseError( 500, $db->stderr());
					    }
					    
					    // Reload the object
						$db->setQuery($query);
						$obj = $db->loadObject();
						
						$isNewUser = true;
						
					}
					
				}
			}


			if($obj) {
				$thisVars = get_object_vars($this);
				// load cparams
				$this->_cparams = new CParameter($obj->_cparams);
				
				// fix a bug where privacyVideoView = 1
				if ($this->_cparams->get('privacyVideoView')==1)
				{
					$this->_cparams->set('privacyVideoView', 0);
				}
				
				unset($obj->_cparams);
				
				// load user params
				$this->_params = new CParameter($obj->params);
				unset($obj->params);
				
				foreach( $thisVars as $key=>$val ) {
					if( isset($obj->$key) ) {
						$this->$key = $obj->$key ;
					}
				}
							
				#correct the friendcount here because blocked user are still counted in "friendcount"
				$model	= CFactory::getModel( 'friends' );
				$realfriendcount = $model->getFriendsCount($this->id);
				$this->_friendcount = $realfriendcount;
				//============

			} else {
				// this is a visitor, we still have to create params object for them
				$this->_cparams = new CParameter('');
				$this->_params  = new CParameter('');
			}
				
			$this->_init = true;
		}
		
		return $isNewUser;

	}

	/**
	 * True if this person is a friend with the given userid
	 * @param <type> $id
	 */
	public function isFriendWith( $id )
	{
		if($this->id == 0)
			return false;
			
		// I am friend with myself! :)
		if($this->id == $id)
			return true;
		
		// $this->_friends will be null if if is not initialized yet
		// If friendcount doesn't match friend list, it is not initilized yet
		if( $this->_friendcount != 0 && empty($this->_friends) )
		{
			// If user has no friend yet, the data might not have been initialized
			// Query and update the record
			$this->updateFriendList();
		}
		$friendsIds = explode(',', $this->_friends);

		return in_array($id, $friendsIds);
	}

	public function updateFriendList()
	{
		$friendModel = CFactory::getModel('friends');
		$friendsIds = $friendModel->getFriendIds($this->id);
		$this->_friends = implode(',', $friendsIds);
		$this->_friendcount = count($friendsIds);
		$this->save();
	}
	
	/**
	 * Return current user status
	 * @return	string	user status	 
	 */	 	
	public function getStatus( $rawFormat = false )
	{
		jimport( 'joomla.filesystem.file' );
		
		// @rule: If user requested for a raw format, we should pass back the raw status.
		$statusmodel				= CFactory::getModel('status');
		$statusmodel				= $statusmodel->get($this->_userid);
		$status			 			= $statusmodel->status;
		if( $rawFormat )
		{
			return $status;
		}

		// @rule: We need to escape any unwanted stuffs here before proceeding.
		CFactory::load( 'helpers' , 'string' );
		$status	=   CStringHelper::escape( $status );
		
		if(JFile::Exists(CPluginHelper::getPluginURI('community','wordfilter').DS.'wordfilter.php') && JPluginHelper::isEnabled('community', 'wordfilter'))
		{
			require_once( CPluginHelper::getPluginURI('community','wordfilter').DS.'wordfilter.php' );
			if(class_exists('plgCommunityWordfilter'))
			{
				$dispatcher = & JDispatcher::getInstance();
				$plugin 	=& JPluginHelper::getPlugin('community', 'wordfilter');
				$instance 	= new plgCommunityWordfilter($dispatcher, (array)($plugin));
			}
			$status		= $instance->_censor( $status );
		}
		
		// @rule: Create proper line breaks.
		$status	= CStringHelper::nl2br( $status );
		
		// @rule: Auto link statuses
		CFactory::load( 'helpers' , 'linkgenerator' );
		$status	= CLinkGeneratorHelper::replaceURL( $status );

		return $status;
	}
	
	public function getViewCount(){
		return $this->_view;
	}
	
	/**
	 * Returns the storage method for the particular user.
	 * It allows the remote storage to be able to identify which storage
	 * the user is currently on.
	 * 
	 * @param	none
	 * @return	String	The storage method. 'file' or 'remote'
	 **/
	public function getStorage(){
		return $this->_storage;
	}
		 	 	 	 	 	 	
	/**
	 * Return the html formatted tooltip
	 */	 	
	public function getTooltip()
	{
		if(!$this->_tooltip)
		{
			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'tooltip.php' );
			$this->_tooltip =  cAvatarTooltip($this);
		}
		return $this->_tooltip;
	}
	
	/**
	 *
	 */	 	
	public function getKarmaPoint() {
		return $this->_points;
	} 
	
	/**
	 * Return the the name for display, either username of name based on backend config
	 */	 	
	public function getDisplayName( $rawFormat = false )
	{
		$config = CFactory::getConfig();
		$nameField = $config->getString('displayname'); 

		
		if( $rawFormat )
		{
			return $this->$nameField;
		}
		
		CFactory::load( 'helpers' , 'string' );

		return CStringHelper::escape( $this->$nameField );
	}
	
	/**
	 * Retrieve the current timezone the user is at.
	 * 
	 * @return	int The current offset the user is located.	 	 
	 **/	 	
	public function getTimezone()
	{
		$mainframe	=& JFactory::getApplication();
		$config		= CFactory::getConfig();		
		$timezone	= $mainframe->getCfg('offset');
		$my			=& JFactory::getUser();
		
		if(!empty($my->params))
		{
			$timezone	= $my->getParam('timezone', $timezone );
		} 
		
		return $timezone;
	}
	
	/**
	 * Return current user UTC offset
	 */	 	
	public function getUtcOffset()
	{
		
		if (!C_JOOMLA_15) {
			//J1.6 handle timezone by its framework
			return true;
		}
		$mainframe	=& JFactory::getApplication();
		$config		= CFactory::getConfig();	
	
		$timeZoneOffset = $mainframe->getCfg('offset');
		$dstOffset		= $config->get('daylightsavingoffset');

		$my		=& JFactory::getUser();
		
		if(!empty($my->params))
		{
			$timeZoneOffset = $my->getParam('timezone', $timeZoneOffset);

			$myParams	= $this->getParams();
			$dstOffset	= $myParams->get('daylightsavingoffset', $dstOffset);
		} 
		return ($timeZoneOffset + $dstOffset);
	}

	/**
	 * Return the count of the friends
	 **/	 	
	public function getFriendCount()
	{
		/*if(!empty($this->_friends)){
			$numfriend = count(explode(',', $this->_friends));
		}else{
			$numfriend = 0;
		}
		
		
		// if the count is not the same, the friend list might be out of sync
		// update it (which will also update _friendcount)
		if($numfriend != $this->_friendcount){
		    $this->updateFriendList();
		}*/

		return $this->_friendcount;
	}
	
		 	
	/**
	 * Return path to avatar image
	 **/
	public function getAvatar()
	{
		// @rule: Check if the current user's watermark matches the current system's watermark.
		$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		$match			= $multiprofile->isHashMatched( $this->_profile_id , $this->_watermark_hash );

		if( !$match )
		{
			// @rule: Since the admin may have changed the watermark for the specific user profile type, we need to also update
			// the user's watermark as well.
			CFactory::load( 'helpers' , 'image' );
			$hashName	= CImageHelper::getHashName( $this->id . time() );
			
			$multiprofile->updateUserAvatar( $this , $hashName );
			$multiprofile->updateUserThumb( $this , $hashName );
		}
		
		if( JString::stristr($this->_avatar, 'default.jpg') )
		{
			$this->_avatar = '';
		}
		
		// For user avatars that are stored in a remote location, we should return the proper path.
		// @rule: For default avatars and watermark avatars we don't want to alter the url behavior.
		// as it should be stored locally.
		if( $this->_storage != 'file' && !empty($this->_avatar) && JString::stristr( $this->_avatar , 'images/watermarks' ) === false)
		{
			$storage = CStorage::getStorage($this->_storage);
			return $storage->getURI( $this->_avatar );
		}

		
		CFactory::load('helpers', 'url');
		$avatar = CUrlHelper::avatarURI($this->_avatar, 'user.png');

		return $avatar;
	}
	 
	/**
	 * Return path to thumb image
	 */
	public function getThumbAvatar()
	{
		// @rule: Check if the current user's watermark matches the current system's watermark.
		$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		$match			= $multiprofile->isHashMatched( $this->_profile_id , $this->_watermark_hash );

		if( !$match )
		{
			// @rule: Since the admin may have changed the watermark for the specific user profile type, we need to also update
			// the user's watermark as well.
			CFactory::load( 'helpers' , 'image' );
			$hashName	= CImageHelper::getHashName( $this->id . time() );
			
			$multiprofile->updateUserAvatar( $this , $hashName );
			$multiprofile->updateUserThumb( $this , $hashName );
		}
		
		if(JString::stristr($this->_thumb, 'default_thumb.jpg') )
		{
			$this->_thumb = '';
		}
		
		// For user avatars that are stored in a remote location, we should return the proper path.
		// @rule: For default avatars and watermark avatars we don't want to alter the url behavior.
		// as it should be stored locally.
		if( $this->_storage != 'file' && !empty($this->_thumb) && JString::stristr( $this->_thumb , 'images/watermarks' ) === false )
		{
			$storage = CStorage::getStorage($this->_storage);
			return $storage->getURI( $this->_thumb );
		}
		
		
		CFactory::load('helpers', 'url');
		$thumb = CUrlHelper::avatarURI($this->_thumb, 'user_thumb.png');

		return $thumb;
	}
	
	/**
	 * Return the custom profile data based on the given field code
	 *
	 * @param	string	$fieldCode	The field code that is given for the specific field.
	 */	 	
	public function getInfo( $fieldCode )
	{
		// Run Query to return 1 value
		$db		=& JFactory::getDBO();
		$my		= CFactory::getUser();

		$query	= 'SELECT b.* FROM ' . $db->nameQuote( '#__community_fields' ) . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote( '#__community_fields_values' ) . ' AS b '
				. 'ON b.' . $db->nameQuote( 'field_id' ) . '=a.' . $db->nameQuote( 'id' ) . ' '
				. 'AND b.' . $db->nameQuote( 'user_id' ) . '=' . $db->Quote( $this->id ) . ' '
				. 'INNER JOIN ' . $db->nameQuote( '#__community_users' ) . ' AS c '
				. 'ON c.' . $db->nameQuote( 'userid' ) . '= b.' . $db->nameQuote( 'user_id' ) . ' '
				. 'LEFT JOIN ' . $db->nameQuote( '#__community_profiles_fields' ) . ' AS d '
				. 'ON c.' . $db->nameQuote( 'profile_id' ) . ' = d.' . $db->nameQuote( 'parent' ) . ' '
				. 'AND d.' . $db->nameQuote( 'field_id' ) . ' = b.' . $db->nameQuote( 'field_id' ) . ' '
				. 'WHERE a.' . $db->nameQuote( 'fieldcode' ) . ' =' . $db->Quote( $fieldCode ); 
		
		$db->setQuery( $query );
		$result	= $db->loadObject();

		$field	=& JTable::getInstance( 'FieldValue' , 'CTable' );
		$field->bind( $result );
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		$config	= CFactory::getConfig();

		// @rule: Only trigger 3rd party apps whenever they override extendeduserinfo configs
		if( $config->getBool( 'extendeduserinfo' ) )
		{
			CFactory::load( 'libraries' , 'apps' );
			$apps	= CAppPlugins::getInstance();
			$apps->loadApplications();
			
			$params		= array();
			$params[]	= $fieldCode;
			$params[]	=& $field->value;
			
			$apps->triggerEvent( 'onGetUserInfo' , $params );
		}

		// Respect privacy settings.
		CFactory::load( 'libraries' , 'privacy' );
		if( !CPrivacy::isAccessAllowed( $my->id , $this->id , 'custom' , $field->access ) )
		{
			return false;
		}
		
		return $field->value;
	}
	
	/**
	 * Counterpart of CUser::getInfo. Sets a specific field's value given the field code.
	 * 	 
	 * @param	string	$fieldCode	The field code.
	 * @return	boolean	True on success false otherwise	 
	 **/
	public function setInfo( $fieldCode , $value )
	{
		$model	= CFactory::getModel( 'Profile' );
		
		return $model->updateUserData( $fieldCode , $this->id , $value );
	}
	
	/**
	 * Return the given user's address string
	 * We will build the address using known FIELD CODE
	 * FIELD_STREET, FIELD_CITY, FIELD_STATE, FIELD_ZIPCODE, FIELD_COUNTRY,
	 * If it is not defined, we just skip it	  
	 */ 	
	public function getAddress()
	{

		$config	    =	CFactory::getConfig();
		$fields	    =	$config->get('user_address_fields');

		$fieldsInArray	= explode( ',', $fields );
		$address    =	'';

		foreach ( $fieldsInArray as $key => $value) {
		    $info	=   $this->getInfo( $value );
		    $address	.=  ( !empty( $info ) ) ? "$info,": '';
		}

		// Trim
		$address = JString::trim($address, " ,");
		
		return $address;
	}
	
	/**
	 * Return path to avatar image
	 **/
	private function _getLargeAvatar()
	{
		//@compatibility: For older releases
		// can be removed at a later time.
		if( empty( $this->_avatar ) && ($this->id != 0) )
		{
			
			// Copy old data.
			$model	= CFactory::getModel( 'avatar' );

			// Save new data.
			$this->_avatar	= $model->getLargeImg( $this->id );

			// We only want the relative path , specific fix for http://dev.jomsocial.com
			$this->_avatar	= str_ireplace( JURI::base() , '' , $this->_avatar );
			
			// Test if this is default image as we dont want to save default image
			if( stristr( $this->_avatar , 'default.jpg' ) === FALSE )
			{
				$userModel	= CFactory::getModel( 'user' );

				// Fix the .jpg_thumb for older release
				$userModel->setImage( $this->id , $this->_avatar , 'avatar' );
			}
			else
			{
				return $this->_avatar;
			}
		}
		
		return $this->_avatar;		
	}
	
	private function _getMediumAvatar()
	{
		//@compatibility: For older releases
		// can be removed at a later time.
		if( empty( $this->_thumb ) && ($this->id != 0) )
		{
			// default guest image.
			
			// Copy old data.
			$model	= CFactory::getModel( 'avatar' );

			// Save new data.
			$this->_thumb	= $model->getSmallImg( $this->id );

			// We only want the relative path , specific fix for http://dev.jomsocial.com
			$this->_thumb	= str_ireplace( JURI::base() , '' , $this->_thumb );

			// Test if this is default image as we dont want to save default image
			if( stristr( $this->_thumb , 'default_thumb.jpg' ) === FALSE )
			{
				$userModel	= CFactory::getModel( 'user' );

				// Fix the .jpg_thumb for older release
				$userModel->setImage( $this->id , $this->_thumb , 'thumb' );
			}

		}
		
		return $this->_thumb;
	}
	/**
	 * Return the combined params of JUser and CUser
	 * @return	JParameter	 
	 */	 	
	public function getParams()
	{
		return $this->_cparams;
	}
	
	public function isOnline() {
		return ($this->_isonline != null);
	} 

	/**
	 * Check if the user is blocked
	 *
	 * @param	null
	 * return	boolean True user is blocked
	 */	 
	public function isBlocked()
	{
		return ( $this->block == '1' );
	}
	
	/**
	 * Determines whether the current user is allowed to create groups
	 * 
	 * @param	none
	 * @return	Boolean		True if user is allowed and false otherwise.	 	 	 
	 **/	 	
	public function canCreateGroups()
	{
		// @rule: If user registered prior to version 2.0.1, there is no multiprofile setup.
		// We need to ensure that they are still allowed to create groups.
		if( $this->getProfileType() == COMMUNITY_DEFAULT_PROFILE )
		{
			return true;
		}
		
		$profile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		$profile->load( $this->getProfileType() );

		return ( bool ) $profile->create_groups;
	}
	
	/**
	 * Increment view count. 
	 * Only increment the view count if the view is from a different session	 
	 */	 	
	public function viewHit(){
		
		$session =& JFactory::getSession();
		if( $session->get('view-'. $this->id, false) == false ) {
			
			$db		=& JFactory::getDBO();
			$query = 'UPDATE ' . $db->nameQuote('#__community_users')
			. ' SET ' . $db->nameQuote('view') .' = ( ' . $db->nameQuote('view') .' + 1 )'
			. ' WHERE ' . $db->nameQuote('userid') .'=' . $db->Quote($this->id);
			$db->setQuery( $query );
			$db->query();
			$this->_view++;
		}
		
		$session->set('view-'. $this->id, true);
	}
	
	/**
	 * Store the user data.
	 * @params	string group	If specified, jus save the params for the given
	 * 							group								 	 
	 */	 	
	public function save( $group ='' )
	{
		parent::save();
		
		// Store our own data
		$obj = new stdClass();
		
		$obj->userid    	= $this->id;
		$obj->status    	= $this->_status;
		$obj->points    	= $this->_points;
		$obj->posted_on 	= $this->_posted_on;
		$obj->avatar    	= $this->_avatar;
		$obj->thumb     	= $this->_thumb;
		$obj->invite    	= $this->_invite;
		$obj->alias			= $this->_alias;
		$obj->params		= $this->_cparams->toString();
		$obj->profile_id	= $this->_profile_id;
		$obj->storage		= $this->_storage;
		$obj->watermark_hash	= $this->_watermark_hash;
		$obj->search_email		= $this->_search_email;
		$obj->friends			= $this->_friends;
		$obj->groups			= $this->_groups;

		$model = CFactory::getModel('user');
		return $model->updateUser($obj);
	}

	/**
	 * Return the profile type the user is currently on
	 * 	 
	 * @return	int	The current profile type the user is in.
	 */	 
	public function getProfileType()
	{
		static $profileType	= false;
		
		if( $profileType === false )
		{
			$profile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
			$profile->load( $this->_profile_id );
			$config		= CFactory::getConfig();
			
			if( !$profile->published || !$config->get('profile_multiprofile') )
			{
				$profileType	= 0;
			}
			else
			{
				$profileType	= $profile->id;
			}
		}

		return $profileType;
	}
		
	/**
	 * Sets the status for the current user
	 **/
	public function setStatus( $status = '' )
	{
		if( $this->id != 0 )
		{
			$this->set( '_status' , $status );
			$this->save();
		}
	}
	
	
	/** Interface fucntions **/
	
	
	public function resolveLocation($address)
	{
		CFactory::load('libraries', 'mapping');
		$data = CMapping::getAddressData($address);
		print_r($data);
		if($data){
			if($data->status == 'OK')
			{
				$this->latitude  = $data->results[0]->geometry->location->lat;
				$this->longitude = $data->results[0]->geometry->location->lng; 
			}
		}
	}
	
}

class CRoute 
{
	var $menuname = 'mainmenu';

	/**
	 * Method to wrap around getting the correct links within the email
	 * DEPRECATED since 1.5
	 */
	static function emailLink( $url , $xhtml = false )
	{
		return CRoute::getExternalURL( $url , $xhtml );
	}

	/**
	 * Method to wrap around getting the correct links within the email
	 * 
	 * @return string $url
	 * @param string $url
	 * @param boolean $xhtml
	 */	
	static function getExternalURL( $url , $xhtml = false )
	{
		$uri	=& JURI::getInstance();
		$base	= $uri->toString( array('scheme', 'host', 'port'));
		
		return $base . CRoute::_( $url , $xhtml );
	}
	
	static function getURI( $xhtml = true )
	{
		$url		= '';
		
		// In the worst case scenario, QUERY_STRING is not defined at all.
		$url		.= 'index.php?';
		$segments	=& $_GET;
			
		$i			= 0;
		$total		= count( $segments );
		foreach( $segments as $key => $value )
		{
			++$i;
			$url	.= $key . '=' . $value;
			
			if( $i != $total )
			{
				$url	.= '&';
			}					
		}

		// @rule: clean url
		$url	= urldecode( $url );
		$url 	= str_replace('"', '&quot;',$url);
		$url 	= str_replace('<', '&lt;',$url);
		$url	= str_replace('>', '&gt;',$url);
		$url	= preg_replace('/eval\((.*)\)/', '', $url);
		$url 	= preg_replace('/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', $url);
		$url	= preg_replace( '/&Itemid=[0-9]*/' , '' , $url );

		return CRoute::_( $url , $xhtml );
	}
	
	/**
	 * Wrapper to JRoute to handle itemid
	 * We need to try and capture the correct itemid for different view	 
	 */	 	
	static function _($url, $xhtml = true, $ssl = null) 
	{
		global $Itemid;
		
		$cache = CFactory::getFastCache();
		$cacheid = __FILE__ . __LINE__ . serialize(func_get_args()) . $Itemid ;
		if($data = $cache->get( $cacheid ) )
		{
			$data = JRoute::_($data, $xhtml, $ssl);
			return $data;
		}
		
		static $itemid = array();

		parse_str( JString::str_ireplace( 'index.php?' , '' , $url  ) );

		if(empty($view))
		{
			$view = 'frontpage';
		}
		
		if( isset( $option ) && $option != 'com_community' )
		{
			if( !$Itemid )
			{
				$db		= JFactory::getDBO();
				$query	= 'SELECT ' . $db->nameQuote( 'id' ) . ' FROM ' . $db->nameQuote( '#__menu' ) . ' '
						. 'WHERE ' . $db->nameQuote( 'link' ) . ' LIKE ' . $db->Quote( '%' . $url . '%' );
				$db->setQuery( $query );
				$id		= $db->loadResult();
				
				$url	.= '&Itemid=' . $id;
			}
			
			return JRoute::_( $url , $xhtml , $ssl );
		}
		
		if(empty($itemid[$view]))
		{
			global $Itemid;
			$isValid = false;
			
			$currentView 	= JRequest::getVar('view', 'frontpage');
			$currentOption 	= JRequest::getVar('option');

 			// If the current Itemid match the expected Itemid based on view
 			// we'll just use it
 			$db		=& JFactory::getDBO();
			$viewId =CRoute::_getViewItemid($view);		
				
			// if current itemid 
			if($currentOption == 'com_community' && $currentView == $view )
			{
				$itemid[$view] = $Itemid;
				$isValid = true;
			} 
			else if($viewId === $Itemid && !is_null($viewId))
			{
				$itemid[$view] = $Itemid;
				$isValid = true;
			}
			else if($viewId !== 0 && !is_null($viewId))
			{
				$itemid[$view] = $viewId;
				$isValid = true;
			}
			
			
			if(!$isValid)
			{
				$id = CRoute::_getDefaultItemid();
				if($id !== 0 && !is_null($id))
				{
					$itemid[$view] =$id;
				}
				$isValid = true;
			}
			
			// Search the mainmenu for the 1st itemid of jomsocial we can find
			if(!$isValid)
			{
				$db		= JFactory::getDBO();
				$query	= 'SELECT ' . $db->nameQuote('id') .' FROM ' . $db->nameQuote('#__menu') .' WHERE '
						. $db->nameQuote( 'link' ) . ' LIKE ' . $db->Quote('%com_community%') 
						. 'AND ' . $db->nameQuote( 'published' ) . '=' . $db->Quote( 1 ) . ' '
						. 'AND ' . $db->nameQuote( 'menutype' ) . '=' . $db->Quote('{CRoute::menuname}')
						. 'AND ' . $db->nameQuote( 'menutype' ) . '!=' . $db->Quote( $config->get( 'toolbar_menutype' ) ) . ' '
						. 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( 'component' );
				$db->setQuery($query);
				$isValid = $db->loadResult();
				
				if(!empty($isValid))
				{
					$itemid[$view] = $isValid;
				}
			}			
			
			// If not in mainmenu, seach in any menu
			if(!$isValid)
			{
				$query	= 'SELECT ' . $db->nameQuote('id') .' FROM ' . $db->nameQuote('#__menu') .' WHERE '
						. $db->nameQuote( 'link' ) . ' LIKE ' . $db->Quote('%com_community%')
						. 'AND ' . $db->nameQuote( 'published' ) . '=' . $db->Quote( 1 ) . ' '
						. 'AND ' . $db->nameQuote( 'menutype' ) . '!=' . $db->Quote( $config->get( 'toolbar_menutype' ) ) . ' '
						. 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( 'component' );
				$db->setQuery($query);
				$isValid = $db->loadResult();	
				if(!empty($isValid))
					$itemid[$view] = $isValid;
			}
			
			
		}
		
		$pos = strpos($url, '#');
		if ($pos === false)
		{
			if( isset( $itemid[$view] ) )
				$url .= '&Itemid='.$itemid[$view];
		}
		else 
		{
			if( isset( $itemid[$view] ) )
				$url = str_ireplace('#', '&Itemid='.$itemid[$view].'#', $url);
		}		
		
		$data =  JRoute::_($url, $xhtml, $ssl);
		$cache->store($url, $cacheid);
        return $data; 
	}
	
	/**
	 * Return the Itemid specific for the given view. 
	 */	 	
	static function _getViewItemid($view)
	{
		static $itemid = array();
		
		if(empty($itemid[$view]))
		{
			$db		=& JFactory::getDBO();
			$config	= CFactory::getConfig();
			$url 	= $db->quote('%option=com_community&view=' . $view . '%');
			$type = $db->quote('component');
			
			$query	= 'SELECT ' . $db->nameQuote('id') .' FROM ' . $db->nameQuote( '#__menu' ) . ' '
					. 'WHERE ' . $db->nameQuote( 'link' ) . ' LIKE ' . $url . ' '
					. 'AND ' . $db->nameQuote( 'published' ) . '=' . $db->Quote( 1 ) . ' '
				 	. 'AND ' . $db->nameQuote( 'menutype' ) . '!=' . $db->Quote( $config->get( 'toolbar_menutype' ) ) . ' '
				 	. 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( 'component' );
			$db->setQuery($query);
			$val = $db->loadResult();
			$itemid[$view] = $val;
		} else{
			$val = $itemid[$view];
		}
		return $val;
	}
	
	/**
	 * Retrieve the Itemid of JomSocial's menu. If you are creating a link to JomSocial, you
	 * will need to retrieve the Itemid.
	 **/	 	
	static function getItemId()
	{
		return CRoute::_getDefaultItemid();
	}
	
	/**
	 * Return the Itemid for default view, frontpage
	 */	 	
	static function _getDefaultItemid()
	{
		static $defaultId = null ;
		
		if($defaultId != null)
			return $defaultId;
			
		$db		=& JFactory::getDBO();
		
		$url = $db->quote("index.php?option=com_community&view=frontpage");
		$type = $db->quote('component');
		
		$query  = 'SELECT ' . $db->nameQuote('id') .' FROM ' . $db->nameQuote('#__menu') 
					.' WHERE ' . $db->nameQuote('link') .' = ' . $url .' AND ' . $db->nameQuote('published') .'=' . $db->Quote(1) . ' '
					. 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( 'component' );					
		$db->setQuery($query);
		$val = $db->loadResult();
		
		if(!$val)
		{
			$url = $db->quote("%option=com_community%");
			
			$query  = 'SELECT ' . $db->nameQuote('id') .' FROM ' . $db->nameQuote('#__menu') 
				.' WHERE ' . $db->nameQuote('link') .' LIKE ' . $url . ' AND ' . $db->nameQuote('published') .'=' . $db->Quote(1) . ' '
				. 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( 'component' );
			$db->setQuery($query);
			$val = $db->loadResult();
		}
		
		$defaultId = $val;
		return $val;
	}
}

class CCachingModel
{
	
	private $oModel;
	
	/**
	 * Store the actual object model.
	 **/
	public function __construct($oModel)
	{
		$this->oModel = $oModel;
	}
	                            
	/**
	 * Triggered when invoking inaccessible method in an object.
	 * 	 
	 **/
	public function __call($methodName, $arguments)
	{   
		// Cache is set in the model class.
		if (($oCache = CCache::load($this->oModel)) && ($aSetting = $oCache->getMethod($methodName))) {
			$cacheAction = $aSetting['action'];
			$aCacheTag   = $aSetting['tag'];
			
			$oZendCache  = CFactory::getCache('core');
			$className   = get_class($this->oModel);
			
			// Genereate cache file.
			if ($cacheAction == CCache::ACTION_SAVE) {
				if (!$data = $oZendCache->load($className . '_' . $methodName . '_' . md5(serialize($arguments)))) {
					$data  = call_user_func_array(array($this->oModel, $methodName), $arguments);
					$oZendCache->save($data, NULL, $aCacheTag);
				}

			// Remove cache file.
			} elseif ($cacheAction == CCache::ACTION_REMOVE) {
				$oCache->remove($aCacheTag);
				$data  = call_user_func_array(array($this->oModel, $methodName), $arguments);
			}
		} else {
			$data = call_user_func_array(array($this->oModel, $methodName), $arguments);
		}
		
		return $data;	
	
    }
    
	/**
     * Is utilized for reading data from inaccessible properties. 
     */	     
    public function __get($var){
		return $this->oModel->$var;
	}
	/**
	 *  Is run when writing data to inaccessible properties. 
	 */	 	
    public function __set($var, $val){
		$this->oModel->$var = $val;
	}

}
class CDispatcher extends JDispatcher {
	/**
	 * Contructor, inherrit from parrent.
	 **/
	public function __construct()
	{
		parent::__construct();
	}	
	/**
	 * Overide JDispatcher::getInstance function.
	 **/
	public static function getInstanceStatic()
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new CDispatcher();
			//link JDispatcher's properties to CDispatcher's properties
			$dispatcher =& JDispatcher::getInstance();
			$arr_p = get_object_vars($dispatcher);
			foreach($arr_p as $key => $value){
				$instance->$key =& $dispatcher->$key;
			}
		}
		return $instance;
	}	
	/**
	 * Contructor, inherrit from parrent.
	 **/
	public function getObservers()
	{
		return $this->_observers;
	}	
}

class CPluginWrapper extends JPlugin {
	private $JplgObj;
	/**
	 * Contructor, inherrit from parrent.
	 **/
	public function __construct($JplgObj)
	{
		if (is_object($JplgObj) && is_subclass_of($JplgObj,'JPlugin')) {
			$this->JplgObj = $JplgObj;
		}
	}	
	
	public function getPluginType() {
		if ($this->JplgObj){
			return $this->JplgObj->_type;
		}
		return '';
	}
	public function getPluginName() {
		if ($this->JplgObj){
			return $this->JplgObj->_name;
		}
		return '';
	}
}

class CACL {
	public static function getInstance () {
		static $instance;
		if (!is_object($instance)) {
			if (C_JOOMLA_15) {
				$instance = new CACLAuthorization(); 
			} else {
				$instance = new CACLAccess();
			}
		}
		return $instance;
	}
}
if(!C_JOOMLA_15) { 
	class CACLAccess extends JAccess {
		public function getGroupsByUserId($userId) {
			$groupIds = JAccess::getGroupsByUser($userId);
			return $this->getGroupName(end($groupIds));
		}
		public function getGroupUser($userId) {
			$groupIds = JAccess::getGroupsByUser($userId,false);
			return $this->getGroupName($groupIds[0]);
		}
		public function getGroupID($groupname){
			$db = JFactory::getDbo();
			$query = 'Select '
					. $db->nameQuote('id') . ' from '
					. $db->nameQuote('#__usergroups') . ' where '
					. $db->nameQuote('title') . '=' 
					. $db->quote($groupname);
			$db->setQuery($query);
			return $db->loadResult();
		}
		public function getGroupName($groupId) {
			if ($groupId !== '') {
				$db = JFactory::getDbo();
				$query = 'Select '
						. $db->nameQuote('title') . ' from '
						. $db->nameQuote('#__usergroups') . ' where '
						. $db->nameQuote('id') . '=' 
						. $db->quote($groupId);
				$db->setQuery($query);
				return $db->loadResult();
			}
			return '';
		}
		public function is_group_child_of( $grp_src,  $grp_tgt){
			$gid_src = $this->getGroupID($grp_src);
			$gid_tgt = $this->getGroupID($grp_tgt);
			if ($gid_src && $gid_tgt ) {
				$group_path = JAccess::getGroupPath($gid_src);
				if (is_array($group_path) && in_array($gid_tgt,$group_path)) {
					return true;
				}
			}
			return false;
		}
	}
} else {
	class CACLAuthorization {
		public function getGroupsByUserId($userId) {
			$acl			=& JFactory::getACL();
			$objectID 	= $acl->get_object_id( 'users', $userId, 'ARO' );
			$groups 	= $acl->get_object_groups( $objectID, 'ARO' );
			$this_group = $acl->get_group_name( $groups[0], 'ARO' );
			return $this_group;
		}
		public function getGroupUser($userId) {
			$acl			=& JFactory::getACL();
			$grp = $acl->getAroGroup($userId);
			if(isset($grp->name)) { $grp->name = '';}
			return $grp->name;
		}
		public function getGroupID($groupname) {
			$acl			=& JFactory::getACL();
			return $acl->get_group_id( '', $groupname, 'ARO' );
		}
		public function getGroupName($groupId) {
			$acl			=& JFactory::getACL();
			return $acl->get_group_name( $groupId, 'ARO' );
		}
		public function is_group_child_of( $grp_src,  $grp_tgt){
			$acl			=& JFactory::getACL();
			return $acl->is_group_child_of( $grp_src,  $grp_tgt);
		}
	}
}
class CString {
	public function str_ireplace($search, $replace, $str, $count = NULL)
	{
		if ( $count === FALSE ) {
			return self::_utf8_ireplace($search, $replace, $str);
		} else {
			return self::_utf8_ireplace($search, $replace, $str, $count);
		}
	}
	function _utf8_ireplace($search, $replace, $str, $count = NULL){
	
	    if ( !is_array($search) ) {
	
	        $slen = strlen($search);
	        $lendif = strlen($replace) - $slen;
	        if ( $slen == 0 ) {
	            return $str;
	        }
	
	        $search = JString::strtolower($search);
	
	        $search = preg_quote($search, '/');
	        $lstr = JString::strtolower($str);
	        $i = 0;
	        $matched = 0;
	        while ( preg_match('/(.*)'.$search.'/Us',$lstr, $matches) ) {
	            if ( $i === $count ) {
	                break;
	            }
	            $mlen = strlen($matches[0]);
	            $lstr = substr($lstr, $mlen);
	            $str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
	            $matched += $mlen + $lendif;
	            $i++;
	        }
	        return $str;
	
	    } else {
	
	        foreach ( array_keys($search) as $k ) {
	
	            if ( is_array($replace) ) {
	
	                if ( array_key_exists($k,$replace) ) {
	
	                    $str = CString::_utf8_ireplace($search[$k], $replace[$k], $str, $count);
	
	                } else {
	
	                    $str = CString::_utf8_ireplace($search[$k], '', $str, $count);
	
	                }
	
	            } else {
	
	                $str = CString::_utf8_ireplace($search[$k], $replace, $str, $count);
	
	            }
	        }
	        return $str;
	
	    }
	}
}
