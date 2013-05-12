<?php
/**
 * @category	Plugins
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');

if(!class_exists('plgCommunityInvite'))
{
	class plgCommunityInvite extends CApplications
	{
		var $name		= 'Invite';
		var $_name		= 'invite';
	
	    function plgCommunityInvite(& $subject, $config)
	    {		
			parent::__construct($subject, $config);
	    }
		
		// detect GET['invite'] and add cookies 
		function onSystemStart() {
			
			$inviteid = JRequest::getVar('invite', '', 'GET');
			if( !empty( $inviteid ) ){
				setcookie('inviteId', $inviteid, time()+60*60*24, '/');
			}
			
		}
		
		function onUserRegisterFormDisplay(&$text) {
			$invite = JRequest::getVar('inviteId', '', 'COOKIE');
			$text = CString::str_ireplace('</form>', '<input type="hidden" name="invite" value="'. $invite .'"></form>', $text);
		}
		
		function onAfterUserRegistration() {
		}
	}	
}

