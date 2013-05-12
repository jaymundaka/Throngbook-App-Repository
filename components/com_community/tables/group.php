<?php
/**
 * @category	Tables
 * @package		JomSocial
 * @subpackage	Activities 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

class CTableGroup extends CTableCache
{

	var $id 		= null;
	var $published		= null;
	var $ownerid 		= null;
	var $categoryid 	= null;
	var $name 		= null;
	var $description	= null;
	var $email		= null;
	var $website 		= null;
	var $approvals 		= null;
	var $created 		= null;
  	var $avatar		= null;
  	var $thumb		= null;
  	var $discusscount	= null;
  	var $wallcount		= null;
  	var $membercount	= null;
  	var $params		= null;
  	var $_pagination	= null;
	var $storage		= null;
  	
	/**
	 * Constructor
	 */	 	
	public function __construct( &$db )
	{
		parent::__construct( '#__community_groups', 'id', $db );
 	 	
		// Get cache object.
 	 	$oCache = CCache::inject($this);
 	 	
		// Remove groups cache on every delete & store
 	 	$oCache->addMethod(CCache::METHOD_DEL, CCache::ACTION_REMOVE, array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_GROUPS_CAT));
 	 	$oCache->addMethod(CCache::METHOD_STORE, CCache::ACTION_REMOVE, array(COMMUNITY_CACHE_TAG_GROUPS, COMMUNITY_CACHE_TAG_GROUPS_CAT));
	}

	public function getPagination()
	{
		return $this->_pagination;
	}
	
	public function updateMembers()
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT m.* FROM '
				. $db->nameQuote('#__community_groups_members') . ' AS m'
				. ' LEFT JOIN '
				. $db->nameQuote('#__users') . ' AS u ON u.id = m.memberid'
				. ' WHERE ' . $db->nameQuote('u.block') . ' = ' . $db->quote(0)
				. ' AND ' . $db->nameQuote('m.groupid') . ' = ' . $db->quote($this->id)
				. ' AND ' . $db->nameQuote('m.approved') . ' = ' . $db->quote(1);
		$db->setQuery();
		$row	= $db->loadResult();
	}
	
	/**
	 * Update all internal count without saving them
	 */	 	
	public function updateStats()
	{
		if( $this->id != 0 )
		{
			$db	=& JFactory::getDBO();
			
			// @rule: Update the members count each time stored is executed.
			$query	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__community_groups_members' ) . ' AS a '
					. 'JOIN '. $db->nameQuote( '#__users' ). ' AS b ON a.'.$db->nameQuote('memberid').'=b.'.$db->nameQuote('id')
					. 'AND b.'.$db->nameQuote('block').'=0 '
					. 'WHERE ' . $db->nameQuote('groupid') .'=' . $db->Quote( $this->id ) . ' '
					. 'AND ' . $db->nameQuote('approved'). '=' . $db->Quote( '1' ) . ' '
					. 'AND permissions!=' . $db->Quote(COMMUNITY_GROUP_BANNED);
			
			$db->setQuery( $query );
			$this->membercount	= $db->loadResult();

			// @rule: Update the discussion count each time stored is executed.
			$query	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__community_groups_discuss' ) . ' '
					. 'WHERE '. $db->nameQuote('groupid') .'=' . $db->Quote( $this->id );

			$db->setQuery( $query );
			$this->discusscount	= $db->loadResult();

			// @rule: Update the wall count each time stored is executed.
			$query	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__community_wall' ) . ' '
					. 'WHERE ' . $db->nameQuote('contentid'). '=' . $db->Quote( $this->id ) . ' '
					. 'AND '. $db->nameQuote('type') .'=' . $db->Quote( 'groups' );

			$db->setQuery( $query );
			$this->wallcount	= $db->loadResult();
		}
	}
	
	public function check()
	{
		// Santinise data
		$safeHtmlFilter		= CFactory::getInputFilter();
		$this->name		= $safeHtmlFilter->clean($this->name);
		$this->email 		= $safeHtmlFilter->clean($this->email);
		$this->website 		= $safeHtmlFilter->clean($this->website);

		// Allow html tags
		$config			= CFactory::getConfig();
		$safeHtmlFilter		= CFactory::getInputFilter( $config->get('allowhtml') );
		$this->description 	= $safeHtmlFilter->clean($this->description);
		
		return true;
	}
	
	/**
	 * Binds an array into this object's property
	 *
	 * @access	public
	 * @param	$data	mixed	An associative array or object
	 **/
	public function store()
	{
		if (!$this->check()) {
			return false;
		}
		
		return parent::store();
	}

	/**
	 * Return the category name for the current group
	 * 
	 * @return string	The category name
	 **/
	public function getCategoryName()
	{
		$category	=& JTable::getInstance( 'GroupCategory' , 'CTable' );
		$category->load( $this->categoryid );

		return $category->name;
	}

	/**
	 * Return the full URL path for the specific image
	 * 
	 * @param	string	$type	The type of avatar to look for 'thumb' or 'avatar'. Deprecated since 1.8 
	 * @return string	The avatar's URI
	 **/
	public function getAvatar( $type = 'thumb' )
	{
		if( $type == 'thumb' )
		{
			return $this->getThumbAvatar();
		}
		
		// Get the avatar path. Some maintance/cleaning work: We no longer store
		// the default avatar in db. If the default avatar is found, we reset it
		// to empty. In next release, we'll rewrite this portion accordingly.
		// We allow the default avatar to be template specific.
		if ($this->avatar == 'components/com_community/assets/group.jpg')
		{
			$this->avatar = '';
			$this->store();
		}

		// For group avatars that are stored in a remote location, we should return the proper path.
		if( $this->storage != 'file' && !empty($this->avatar) )
		{
			$storage = CStorage::getStorage($this->storage);
			return $storage->getURI( $this->avatar );
		}
		
		CFactory::load('helpers', 'url');
		$avatar	= CUrlHelper::avatarURI($this->avatar, 'group.png');
		
		return $avatar;
	}

	public function getThumbAvatar()
	{
		if ($this->thumb == 'components/com_community/assets/group_thumb.jpg')
		{
			$this->thumb = '';
			$this->store();
		}

		// For group avatars that are stored in a remote location, we should return the proper path.
		if( $this->storage != 'file' && !empty($this->thumb) )
		{
			$storage = CStorage::getStorage($this->storage);
			return $storage->getURI( $this->thumb );
		}
		
		CFactory::load('helpers', 'url');
		$thumb	= CUrlHelper::avatarURI($this->thumb, 'group_thumb.png');
		
		return $thumb;
	}
	
	/**
	 * Return the owner's name for the current group
	 * 
	 * @return string	The owner's name
	 **/	 	
	public function getOwnerName()
	{
		$user		= CFactory::getUser( $this->ownerid );
		return $user->getDisplayName();
	}

	public function getParams()
	{
		$params	= new CParameter( $this->params );
		
		return $params;
	}
	
	/**
	 * Method to determine whether the specific user is a member of a group
	 * 
	 * @param	string	User's id
	 * @return boolean True if user is registered and false otherwise
	 **/
	public function isMember( $userid )
	{
		$db		=& $this->getDBO();
		
		$query	= 'SELECT COUNT(*) FROM ' 
				. $db->nameQuote( '#__community_groups_members') . ' '
				. 'WHERE ' . $db->nameQuote( 'groupid' ) . '=' . $db->Quote( $this->id ) . ' '
				. 'AND ' . $db->nameQuote( 'memberid' ) . '=' . $db->Quote( $userid )
				. 'AND ' . $db->nameQuote( 'approved' ) . '=' . $db->Quote( '1' );
		$db->setQuery( $query );

		$status	= ( $db->loadResult() > 0 ) ? true : false;

		return $status;
	}

	public function isBanned( $userid )
	{
		$db	=&  $this->getDBO();

		$query	=   'SELECT COUNT(*) FROM '
			    . $db->nameQuote( '#__community_groups_members') . ' '
			    . 'WHERE ' . $db->nameQuote( 'groupid' ) . '=' . $db->Quote( $this->id ) . ' '
			    . 'AND ' . $db->nameQuote( 'memberid' ) . '=' . $db->Quote( $userid )
			    . 'AND ' . $db->nameQuote( 'permissions' ) . '=' . $db->Quote( COMMUNITY_GROUP_BANNED );

		$db->setQuery( $query );

		$status	= ( $db->loadResult() > 0 ) ? true : false;

		return $status;
	}

	public function isAdmin( $userid )
	{
		if($this->id ==0)
			return false;
		
		if($userid == 0)
			return false;
		
		// the creator is also the admin
		if($userid == $this->ownerid)
			return true;

		$db		=& $this->getDBO();
		
		$query	= 'SELECT COUNT(*) FROM ' 
				. $db->nameQuote( '#__community_groups_members') . ' '
				. 'WHERE ' . $db->nameQuote( 'groupid' ) . '=' . $db->Quote( $this->id ) . ' '
				. 'AND ' . $db->nameQuote( 'memberid' ) . '=' . $db->Quote( $userid )
				. 'AND ' . $db->nameQuote( 'approved' ) . '=' . $db->Quote( '1' )
				. 'AND ' . $db->nameQuote( 'permissions' ) . '=' . $db->Quote( COMMUNITY_GROUP_ADMIN );
		$db->setQuery( $query );

		$status	= ( $db->loadResult() > 0 ) ? true : false;

		return $status;
	}
	
	public function getLink( $xhtml = false )
	{
		$link	= CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $this->id , $xhtml );
		return $link;
	}
	
	public function getMembersCount()
	{
		return $this->membercount;
	}
	
	/**
	 * Determines if the current group is a private group.
	 **/	 	 	
	public function isPrivate()
	{
		return $this->approvals == COMMUNITY_PRIVATE_GROUP;
	}

	/**
	 * Determines if the current group is a public group.
	 **/	
	public function isPublic()
	{
		return $this->approvals == COMMUNITY_PUBLIC_GROUP;
	}
	
	/**
	 * Return true if the user is allow to modify the tag
	 */
	public function tagAllow($userid)
	{
		return $this->isAdmin($userid);
	}
	
	/**
	 * Return the title of the object
	 */
	public function tagGetTitle()
	{
		return $this->title;
	}
	
	/**
	 * Allows caller to bind parameters from the request
	 * @param	array 	$params		An array of values which keys should match with the parameter.
	 */
	public function bindRequestParams()
	{
		$params		= new CParameter( '' );
		
		$discussordering			= JRequest::getVar( 'discussordering' , DISCUSSION_ORDER_BYLASTACTIVITY , 'REQUEST' );
		$params->set('discussordering' , $discussordering );
		
		$photopermission			= JRequest::getVar( 'photopermission' , GROUP_PHOTO_PERMISSION_ADMINS , 'REQUEST' );
		$params->set('photopermission' , $photopermission );
		
		$videopermission			= JRequest::getVar( 'videopermission' , GROUP_PHOTO_PERMISSION_ADMINS , 'REQUEST' );
		$params->set('videopermission' , $videopermission );

		$eventpermission			= JRequest::getVar( 'eventpermission' , GROUP_EVENT_PERMISSION_ADMINS , 'REQUEST' );
		$params->set('eventpermission' , $eventpermission );
					
		$grouprecentphotos			= JRequest::getInt( 'grouprecentphotos' , GROUP_PHOTO_RECENT_LIMIT , 'REQUEST' );
		$params->set('grouprecentphotos' , $grouprecentphotos );
		
		$grouprecentvideos			= JRequest::getInt( 'grouprecentvideos' , GROUP_VIDEO_RECENT_LIMIT , 'REQUEST' );
		$params->set('grouprecentvideos' , $grouprecentvideos );			
		
		$grouprecentevent			= JRequest::getInt( 'grouprecentevents' , GROUP_EVENT_RECENT_LIMIT , 'REQUEST' );
		$params->set('grouprecentevents' , $grouprecentevent );

		$newmembernotification		= JRequest::getInt( 'newmembernotification' , '1' , 'REQUEST' );
		$params->set('newmembernotification' , $newmembernotification );
		
		$joinrequestnotification	= JRequest::getInt( 'joinrequestnotification' , '1' , 'REQUEST' );
		$params->set('joinrequestnotification' , $joinrequestnotification );
		
		$wallnotification			= JRequest::getInt( 'wallnotification' , '1' , 'REQUEST' );
		$params->set('wallnotification' , $wallnotification );
		
		$this->params	= $params->toString();
		
		return true;
	}

	/**
	 * Allows caller to update the owner name
	 */
	public function updateOwner( $oldOwner , $newOwner )
	{
		if( $oldOwner == $newOwner )
		{
			return true;
		}
		
		// Add member if member does not exist.
		if( !$this->isMember( $newOwner , $this->id ) )
		{
			$data 				= new stdClass();
			$data->groupid		= $this->id;
			$data->memberid		= $newOwner;
			$data->approved		= 1;
			$data->permissions	= 1;
			
			// Add user to group members table
			$this->addMember( $data );
			
			// Add the count.
			$this->updateStats( $group->id );
		}
		else
		{
			// If member already exists, update their permission
                        
			$member	=& JTable::getInstance( 'GroupMembers' , 'CTable' );
			$member->load( $group->id , $newOwner );
			$member->permissions	= '1';

			$member->store();
                
                         
                         
		}
	}

	/**
	 * 
	 */
	public function addMember( $data )
	{
		$db	=& $this->getDBO();
		
		// Test if user if already exists
		if( !$this->isMember($data->memberid, $data->groupid) )
		{
			$db->insertObject('#__community_groups_members' , $data);
			$this->updateStats();
		}
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $data;
	}

	public function deleteMember($gid,$memberid){
	    $db = JFactory::getDBO();

	    $sql = "DELETE FROM ". $db->nameQuote("#__community_groups_members")."
		    WHERE " .$db->nameQuote("groupid") ."=" .$db->quote($gid). "
		    AND " .$db->nameQuote("memberid"). "=" .$db->quote($memberid);

	    $db->setQuery($sql);
	    $db->Query();
		if($db->getErrorNum()){
			JError::raiseError( 500, $db->stderr());
		}

	    return true;
	}

}
