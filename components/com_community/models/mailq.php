<?php
/**
 * @category	Model
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'models' . DS . 'models.php' );

class CommunityModelMailq extends JCCModel
{
	/**
	 * take an object with the send data
	 * $recipient, $body, $subject, 	 
	 */	 	
	public function add($recipient, $subject, $body , $templateFile = '' , $params = '' , $status = 0)
	{
		$my  = CFactory::getUser();
		
		// A user should not be getting a notification email of his own action
		$bookmarkStr = explode('.',$templateFile);
		if ($my->id == $recipient && $bookmarkStr[1] != 'bookmarks' )
		{
			return;
		}
		
		$db	 = &$this->getDBO();
		
		
		$date =& JFactory::getDate();
		$obj  = new stdClass();
		
		$obj->recipient = $recipient;
		$obj->body 		= $body;
		$obj->subject 	= $subject;
		$obj->template	= $templateFile;
		$obj->params	= ( is_object( $params ) && method_exists( $params , 'toString' ) ) ? $params->toString() : '';	
		$obj->created	= $date->toMySQL();
		$obj->status	= $status;
		
		$db->insertObject( '#__community_mailq', $obj );
	}
	
	/**
	 * Restrive some emails from the q and delete it
	 */	 	
	public function get($limit = 100 )
	{
		$db	 = &$this->getDBO();
		
		$sql = 'SELECT * FROM '.$db->nameQuote('#__community_mailq').' WHERE '.$db->nameQuote('status').'='.$db->Quote('0').' LIMIT 0,' . $limit;

		$db->setQuery( $sql );
		$result = $db->loadObjectList();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr() );
		}
		
		return $result;
	}
	
	/**
	 * Change the status of a message
	 */	 	
	public function markSent($id)
	{
		$db	 = &$this->getDBO();
		
		$sql = 'SELECT * FROM '.$db->nameQuote('#__community_mailq').' WHERE '.$db->nameQuote('id').'=' . $db->Quote($id);
		$db->setQuery( $sql );
		$obj = $db->loadObject();
		
		$obj->status = 1;
		$db->updateObject( '#__community_mailq', $obj, 'id' );
	}
	
	public function purge(){
	}
	
	public function remove(){
	}
}
