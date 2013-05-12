<?php
/**
 * @category	Model
 * @package		JomSocial
 * @subpackage	Activities 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT .DS.'components'.DS.'com_community'.DS.'models'.DS.'models.php');
CFactory::load( 'tables' , 'activity' );

/**
 *
 */ 
class CommunityModelActivities extends JCCModel
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Return an object with a single activity item
	 */	 	
	public function getActivity($activityId)
	{ 		
		$act	=& JTable::getInstance( 'Activity' , 'CTable' );
		$act->load($activityId);	
		return $act;
	}
	
	/**
	 * Retrieves the activity content for specific activity
	 * @deprecated since 2.2	 
	 * @return string
	 **/	 
	public function getActivityContent( $activityId )
	{
		$act = $this->getActivity($activityId);
		return $act->content;
	}
	
	/**
	 * Retrieves the activity stream for specific activity
	 * @deprecated since 2.2
	 **/	 
	public function getActivityStream( $activityId )
	{
		return $this->getActivity($activityId);
	}	
	
	/**
	 * Add new data to the stream	
	 * @deprecated since 2.2
	 */	 	
	public function add($actor, $target, $title, $content, $appname = '', $cid=0, $params='', $points = 1, $access = 0){
		jimport('joomla.utilities.date');
		
		$table =& JTable::getInstance( 'Activity' , 'CTable' );
		$table->actor		= $actor;
		$table->target 		= $target;
		$table->title		= $title;
		$table->content		= $content;
		$table->app			= $appname;
		$table->cid			= $cid;
		$table->points		= $points;
		$table->access		= $access;
		$table->location	= '';
		$table->params		= $params;
		
		return $table->store();
	}
	
	
	/**
	 * For photo upload, we should delete all aggregated photo upload activity,
	 * instead of just 1 photo uplaod activity	 
	 */	 	
	public function hide($userId , $activityId )
	{
		$db		=& $this->getDBO();
		
		// 1st we compare if the activity stream author match the userId. If yes,
		// archive the record. if not, insert into hide table.
		$activity	= $this->getActivityStream($activityId);
		
		if(! empty($activity))
		{
			$query	= 'SELECT ' . $db->nameQuote('id') .' FROM ' . $db->nameQuote('#__community_activities');
			$query	.= ' WHERE ' . $db->nameQuote('app') .' = ' . $db->Quote($activity->app);
			$query	.= ' AND ' . $db->nameQuote('cid') .' = ' . $db->Quote($activity->cid);
			$query	.= ' AND ' . $db->nameQuote('title') .' = ' . $db->Quote($activity->title);
			$query	.= ' AND DATEDIFF( created, ' . $db->Quote($activity->created) . ' )=0';
			
			$db->setQuery($query);
			$db->query();
			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
			
			$rows	= $db->loadResultArray();
			
			if(!empty($rows))
			{
				foreach($rows as $key=>$value)
				{
					$obj				= new stdClass();
					$obj->user_id		= $userId;
					$obj->activity_id	= $value;
					$db->insertObject('#__community_activities_hide' , $obj);
					if($db->getErrorNum())
					{
						JError::raiseError( 500, $db->stderr());
					}
				}
			}
		}
		
		return true;
	}


	/**
	 * Return rows of activities
	 */	 	
	public function getActivities($userid='', $friends='', $afterDate = null, $maxEntries=20 , $respectPrivacy = true , $exclusions = null , $displayArchived = false ){
		$db	 = &$this->getDBO();
		$my  = CFactory::getuser();
		
		$cache = CFactory::getFastCache();
 		$cacheid = serialize(func_get_args());
 		if($data = $cache->get( serialize(func_get_args()) ) )
 		{
 			return $data;
 		}

		$todayDate	= new JDate();

		// Oversampling, to cater for aggregated activities
		$maxEntries = ($maxEntries < 0) ? 0 : $maxEntries;
		$maxEntries = $maxEntries*8;

		$orWhere = array();
		$andWhere = array();
		$onActor = '';
		//default the 1st condition here so that if the date is null, it wont give sql error.
		if( !$displayArchived )
		{
			$andWhere[] = $db->nameQuote('archived')."=0";
			//$andWhere[] = "`archived`=0";
		}
		
		if(!empty($userid)){
			$orWhere[] = '(a.' . $db->nameQuote('actor') .'=' . $db->Quote($userid) .')';
			$onActor = ' AND ((a.' . $db->nameQuote('actor') .'='. $db->Quote($userid) .') OR (a.' . $db->nameQuote('target') .'='. $db->Quote($userid).'))';
		}

		// 
		if(!empty($friends)) {
			$orWhere[] = '(a.' . $db->nameQuote('actor') .' IN ('.implode(',',$friends). '))';
			//actor are friends, clear the on Actor condition
			$onActor = '';
		}
		
		if(!empty($userid))
			$orWhere[] = '(a.' . $db->nameQuote('target') .'=' . $db->Quote($userid).')';
		
		if(!empty($afterDate))
			$andWhere[] = '(a.' . $db->nameQuote('created') .' between '.$db->Quote($afterDate->toMySQL()).' and '.$db->Quote($todayDate->toMySQL()).')' ;
		
		if( !is_null( $exclusions) )
		{
			
			$exclusionQuery	= '(a.' . $db->nameQuote('id') .' NOT IN (';

			for($i=0; $i < count( $exclusions);$i++)
			{
				$exclusion	= $exclusions[ $i ];
				$exclusionQuery	.= $db->Quote( $exclusion );
				
				if( $i != (count( $exclusions ) - 1) )
				{
					$exclusionQuery	.= ',';
				}
			}
			$exclusionQuery .= ') )';
			$andWhere[]	= $exclusionQuery;
		}
		
		if( $respectPrivacy )
		{
			// Add friends limits, but admin should be able to see all
			// @todo: should use global admin code check instead
			if($my->id == 0){
				// for guest, it is enough to just test access <= 0
				//$andWhere[] = "(a.`access` <= 10)";
				$andWhere[] = "(a.". $db->nameQuote('access')." <= 10)";
				
			}elseif( ! COwnerHelper::isCommunityAdmin($my->id) ){
				$orWherePrivacy = array();
				$orWherePrivacy[] = '((a.' . $db->nameQuote('access') .' = 0) ' . $onActor .')';
				$orWherePrivacy[] = '((a.' . $db->nameQuote('access') .' = 10) ' . $onActor .')';
				$orWherePrivacy[] = '((a.' . $db->nameQuote('access') .' = 20) AND ( '.$db->Quote($my->id) .' != 0) ' . $onActor .')';
				if($my->id != 0)
				{
					$orWherePrivacy[] = '((a.' . $db->nameQuote('access') .' = ' . $db->Quote(40).') AND (a.' . $db->nameQuote('actor') .' = ' . $db->Quote($my->id).') ' . $onActor .')';
					$orWherePrivacy[] = '((a.' . $db->nameQuote('access') .' = ' . $db->Quote(30).') AND ((a.' . $db->nameQuote('actor') .'IN (SELECT c.' . $db->nameQuote('connect_to')
							.' FROM ' . $db->nameQuote('#__community_connection') .' as c'
							.' WHERE c.' . $db->nameQuote('connect_from') .' = ' . $db->Quote($my->id)
							.' AND c.' . $db->nameQuote('status') .' = ' . $db->Quote(1) .' ) ) OR (a.' . $db->nameQuote('actor') .' = ' . $db->Quote($my->id).') )' . $onActor .' )';
				}
				$OrPrivacy = implode(' OR ', $orWherePrivacy);
				$andWhere[] = "(".$OrPrivacy.")";
			}
		}

		if(!empty($userid))
		{
			//get the list of acitivity id in archieve table 1st.
			$subQuery	= 'SELECT b.' . $db->nameQuote('activity_id') .' FROM ' . $db->nameQuote('#__community_activities_hide') .' as b WHERE b.' . $db->nameQuote('user_id') .' = '. $db->Quote($userid);
			$db->setQuery($subQuery);
			$subResult	= $db->loadResultArray();
			$subString	= implode(',', $subResult);
		
			if( ! empty($subString))
				$andWhere[] = 'a.' . $db->nameQuote('id') .' NOT IN ('.$subString.')';
	    }			

		// If current user is blocked by a user he should not see the activity of the user
		// who block him. (of course, if the user data is public, he can see it anyway!)
		/*
		if($my->id != 0){
			$andWhere[] = "a.`actor` NOT IN (SELECT `userid` FROM #__community_blocklist WHERE `blocked_userid`='{$my->id}')";
		}
		*/

		$whereOr = implode(' OR ', $orWhere);
		$whereAnd = implode(' AND ', $andWhere);

		
		// Actors can also be your friends
		// We load 100 activities to cater for aggregated content
		$date	= CTimeHelper::getDate(); //we need to compare where both date with offset so that the day diff correctly.
		
			
		/* ORIGINAL QUERY HERE
 		$sql = 'SELECT b.' . $db->nameQuote('id') .' as _comment_last_id, '
			.' b.' . $db->nameQuote('date') .' as _comment_date, count( b.' . $db->nameQuote('comment') .' ) as _comment_count, '
			.' b.' . $db->nameQuote('comment') .' as _comment_last , b.' . $db->nameQuote('post_by') .' as _comment_last_by, c.' . $db->nameQuote('like') .' as _likes, a.*, '
			.' TO_DAYS('.$db->Quote($date->toMySQL(true)).') -  TO_DAYS( DATE_ADD(a.' . $db->nameQuote('created').', INTERVAL '.$date->getOffset().' HOUR ) ) as _daydiff'
			.' FROM ' . $db->nameQuote('#__community_activities') .' as a '
			.' LEFT JOIN 
			 	( SELECT ' . $db->nameQuote('id') .', ' . $db->nameQuote('date') .', ' . $db->nameQuote('comment') .', ' . $db->nameQuote('contentid') .', ' . $db->nameQuote('type') .', ' . $db->nameQuote('post_by')
					.' FROM ' . $db->nameQuote('#__community_wall') 
					.' ORDER BY ' . $db->nameQuote('id') .' DESC	) as b '
			.' ON a.' . $db->nameQuote('comment_id') .' = b.' . $db->nameQuote('contentid') 
			.' AND a.' . $db->nameQuote('comment_type') .' = b.' . $db->nameQuote('type') 
			.' LEFT JOIN ' . $db->nameQuote('#__community_likes') .' AS c ON a.' . $db->nameQuote('like_id') .' = c.' . $db->nameQuote('uid')
			.' AND a.' . $db->nameQuote('like_type') .' = c.' . $db->nameQuote('element')
			.' WHERE '
			.' ( '. $whereOr .' ) AND '
			. $whereAnd 
			.' GROUP BY a.' . $db->nameQuote('id')
			.' ORDER BY a.' . $db->nameQuote('created') .' DESC LIMIT ' . $maxEntries;				  
	
		// Remove the bracket if it is not needed
		$sql = CString::str_ireplace('WHERE  (  ) AND', ' WHERE ', $sql);*/
		
// 		$db->setQuery( $sql );

// 		$result = $db->loadObjectList();
// 		if($db->getErrorNum()) {
// 			JError::raiseError( 500, $db->stderr());
// 		}
		

		// Azrul Code start
		// 1. Get all the ids of the activities
		$sql = 'SELECT a.*, '
			.' TO_DAYS('.$db->Quote($date->toMySQL(true)).') -  TO_DAYS( DATE_ADD(a.' . $db->nameQuote('created').', INTERVAL '.$date->getOffset(true).' HOUR ) ) as _daydiff'
			.' FROM ' . $db->nameQuote('#__community_activities') .' as a '
			.' WHERE '
			.' ( '. $whereOr .' ) AND '
			. $whereAnd 
			.' GROUP BY a.' . $db->nameQuote('id')
			.' ORDER BY a.' . $db->nameQuote('created') .' DESC LIMIT ' . $maxEntries;				  
	
		// Remove the bracket if it is not needed
		$sql = CString::str_ireplace('WHERE  (  ) AND', ' WHERE ', $sql);
		
		$db->setQuery( $sql );

		$result = $db->loadObjectList();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		// 2. Get the ids of the comments and likes we will query
		$comments = array();
		$likes = array();
		if(!empty($result))
		{
			foreach($result as $row)
			{
				if(!empty($row->comment_type))
					$comments[$row->comment_type][] = $row->comment_id;
					
				if(!empty($row->like_type))
					$likes[$row->like_type][] = $row->like_id;
			}
		}
		
		// 3. Query the comments
		$commentsResult = array();
		if(!empty($result))
		{
			$cond = array();
			foreach( $comments as $lk => $lv )
			{
				// Make every uid unique
				$lv = array_unique($lv);
				if( !empty($lv))
				{
					$cond[] = ' ( ' 
						.' a.' . $db->nameQuote('type') . '=' . $db->Quote($lk) 
						.' AND '
						.' a.' . $db->nameQuote('contentid') . ' IN (' . implode( ',' , $lv ) . ') '
						.' ) ';
				}
			}
				
			if(!empty($cond)){
			
			$sql = 'SELECT a.* '
				.' FROM ' . $db->nameQuote('#__community_wall') .' as a '
				.' WHERE '
				. implode( ' OR ' , $cond ) 
				.' ORDER BY '.$db->nameQuote('id') . ' DESC ';
			
			
			$db->setQuery( $sql );
			$resultComments = $db->loadObjectList();
			
			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
			
			foreach($resultComments as $comment)
			{
				if(!isset($commentsResult[$comment->type . '-' . $comment->contentid]))
				{
					$commentsResult[$comment->type . '-' . $comment->contentid]->_comment_count = 0;
					$commentsResult[$comment->type . '-' . $comment->contentid] = $comment;
				}
				
				$commentsResult[$comment->type . '-' . $comment->contentid]->_comment_count++;
			}
			}
		}
		
		// 4. Query the likes
		$likesResult = array();
		if(!empty($result))
		{
			$cond = array();
			foreach( $likes as $lk => $lv )
			{
				// Make every uid unique
				$lv = array_unique($lv);
				
				if( !empty($lv))
				{
					$cond[] = ' ( ' 
						.' a.' . $db->nameQuote('element') . '=' . $db->Quote($lk) 
						.' AND '
						.' a.' . $db->nameQuote('uid') . ' IN (' . implode( ',' , $lv ) . ') '
						.' ) ';
				}
			}

			if(!empty($cond)){
			
			$sql = 'SELECT a.* '
				.' FROM ' . $db->nameQuote('#__community_likes') .' as a '
				.' WHERE '
				. implode( ' OR ' , $cond ) ;
				 			
			$db->setQuery( $sql );
			$resultLikes = $db->loadObjectList();
			 
			if($db->getErrorNum()) {
				JError::raiseError( 500, $db->stderr());
			}
			
			foreach($resultLikes as $like)
			{
				$likesResult[$like->element . '-' . $like->uid] = $like->like;
			}

			}
		}
		
		
		// 4. Merge data
		$activities = array();
		if(!empty($result))
		{
			foreach($result as $row)
			{
				// Merge Like data
				if(array_key_exists($row->like_type . '-' . $row->like_id, $likesResult) )
				{
					$row->_likes = $likesResult[$row->like_type . '-' . $row->like_id];
				}
				else 
				{
					$row->_likes = '';
				}
				
				// Merge comment data
				if(array_key_exists($row->comment_type . '-' . $row->comment_id, $commentsResult) )
				{
					$data = $commentsResult[$row->comment_type . '-' . $row->comment_id];
					$row->_comment_last_id = $data->id;
					$row->_comment_last_by = $data->post_by;
					$row->_comment_date	   = $data->date;	
					$row->_comment_count   = $data->_comment_count;			
					$row->_comment_last    = $data->comment;			
				} 
				else 
				{
					$row->_comment_last_id = '';
					$row->_comment_last_by = '';
					$row->_comment_date	   = '';
					$row->_comment_count   = 0;
					$row->_comment_last    = '';
				}
				
				// Create table object
				$act	=& JTable::getInstance( 'Activity' , 'CTable' );
				$act->bind($row);
				$activities[] = $act;
			}
		}
		
		// This is probably not necessary
		unset($likesResult);
		unset($commentsResult);

		$cache->store($activities, $cacheid,array('activities'));
		return $activities;
	}
	
	/**
	 * Return all activities by the given apps
	 */	 	
	public function getAppActivities($appname, $identifier = null , $limit = '100' , $respectPrivacy = true , $exclusions = null , $displayArchived = false ){
		
		$db	 = &$this->getDBO();
		
		// Double the number of limit to allow for aggregator
		$limit = ($limit < 0) ? 0 : $limit;
		$limit = $limit*2;

		$displayArchived	= $displayArchived ? 1 : 0;

		$appsWhere = $db->nameQuote('archived') .'=' . $db->Quote( $displayArchived ) . ' AND ' . $db->nameQuote('app').'='.$db->Quote($appname);
				
		if($identifier != null)
			$appsWhere .= ' AND ' . $db->nameQuote('cid') .'=' . $db->Quote($identifier);
		
		if( !is_null( $exclusions) )
		{
			$appsWhere	.= ' AND (a.' . $db->nameQuote('id') .' NOT IN ('. implode( ',' , $exclusions) . ') )';
		}
		// Actors can also be your friends
		$date	= CTimeHelper::getDate(); //we need to compare where both date with offset so that the day diff correctly.

		$sql = 'SELECT a.* , (DAY( ' . $db->Quote($date->toMySQL(true)).' ) - DAY( DATE_ADD(a.' . $db->nameQuote('created') .',INTERVAL '.$date->getOffset().' HOUR ) )) as ' . $db->Quote('_daydiff') 
				.' FROM ' . $db->nameQuote('#__community_activities') .' as a '
				.' WHERE ' . $appsWhere
				.' ORDER BY ' . $db->nameQuote('created') .' DESC '
				.' LIMIT ' . $limit ;
		$db->setQuery( $sql );
		$result = $db->loadObjectList();
		
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		// @todo: write a plugin that return the html part of the whole system
		$activities = array();
		if(!empty($result)){
			foreach($result as $row){
				$act	=& JTable::getInstance( 'Activity' , 'CTable' );
				$act->bind($row);
				$activities[] = $act;
			}
		}
		return $activities;
	}
	
	/**
	 * Remove any recently changed activities
	 */	 	
	public function removeRecent($actor, $title, $app, $timeDiff){
	}
	
	/*
	 * Remove One Photo Activity
	 * As it's tricky to remove the activity since there's no photo id in the
	 * activity data. Here we get all the activities of 5 seconds within the
	 * activity creation time, then we try to match the photo id in the activity 
	 * params, and also the thumbnail in the activity content field. When all 
	 * fails, we fallback to removeOneActivity()	 
	 */
	public function removeOnePhotoActivity( $app, $uniqueId, $datetime, $photoId, $thumbnail )
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT * FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . '=' . $db->Quote( $app ) . ' '
				. 'AND ' . $db->nameQuote( 'cid' ) . '=' . $db->Quote( $uniqueId ) . ' '
				. 'AND ( ' . $db->nameQuote( 'created' ) . ' BETWEEN ' . $db->Quote( $datetime ) . ' '
				. 'AND ( ADDTIME(' . $db->Quote($datetime) . ', ' . $db->Quote('00:00:05') . ' ) ) ) '
				;
		$db->setQuery($query);
		$result	= $db->loadObjectList();
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		$activityId = null;
		$handler = new CParameter(null);
		
		// the activity data contains photoid and the photo thumbnail
		// which can be useful for us to find the correct activity id
		foreach ($result as $activity)
		{
			$handler->loadINI($activity->params);
			if ($handler->getValue('photoid')==$photoId)
			{
				$activityId = $activity->id;
				break;
			}
			if ( JString::strpos($activity->content, $thumbnail)!== false )
			{
				$activityId = $activity->id;
				break;
			}
		}
		
		if (is_null($activityId))
		{
			return $this->removeOneActivity($app, $uniqueId);
		}
		
		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $activityId ) . ' '
				. 'LIMIT 1 ' ;
		$db->setQuery( $query );
		$status	= $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $status;
	}
	
	public function removeOneActivity( $app , $uniqueId )
	{
		$db		=& $this->getDBO();
		
		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . '=' . $db->Quote( $app ) . ' '
				. 'AND ' . $db->nameQuote( 'cid' ) . '=' . $db->Quote( $uniqueId ) . ' ' 
				. 'LIMIT 1 ' ;

		$db->setQuery( $query );
		$status	= $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $status;
	}
		//Remove Discussion via params
	function removeDiscussion($app,$uniqueId,$paramName,$paramValue){

		$db	=&	$this->getDBO();

		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . '=' . $db->Quote( $app ) . ' '
				. 'AND ' . $db->nameQuote( 'cid' ) . '=' . $db->Quote( $uniqueId ) . ' '
				. 'AND ' . $db->nameQuote( 'params' ) . ' LIKE '.$db->Quote('%'.$paramName .'='.$paramValue.'%') ;
		$db->setQuery( $query );
		$status	= $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $status;
	}

	public function removeActivity( $app , $uniqueId )
	{
		$db		=& $this->getDBO();
		
		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . '=' . $db->Quote( $app ) . ' '
				. 'AND ' . $db->nameQuote( 'cid' ) . '=' . $db->Quote( $uniqueId ) ;

		$db->setQuery( $query );
		$status	= $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $status;
	} 
	
	public function removeGroupActivity($ids)
	{
	    $db		=& $this->getDBO();
	    $app = '"groups","groups.bulletin","groups.discussion","groups.wall"';
	    $query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . 'IN ('.$app.') '
				. 'AND ' . $db->nameQuote( 'cid' ) . 'IN ('.$ids.')';
	      
	    $db->setQuery( $query );
	    $status	= $db->query();

	    if($db->getErrorNum())
	    {
		JError::raiseError( 500, $db->stderr());
	    }
	    return $status;
	}
	
	/**
	 *  Deprecated since 2.2
	 *	Use CTableActivity instead.
	 */
	public function deleteActivity( $app , $uniqueId )
	{
		$activity	= JTable::getInstance( 'Activity' , 'CTable' );
		$activity->load( $uniqueId );

		return $activity->delete( $app );
	}
	
	/**
	 * Return the actor id by a given activity id
	 */	 	
	public function getActivityOwner($uniqueId){
		$db	 = &$this->getDBO();
		
		
		$sql = 'SELECT ' . $db->nameQuote('actor')
				.' FROM ' . $db->nameQuote('#__community_activities') 
				.' WHERE ' . $db->nameQuote('id') .'=' . $db->Quote($uniqueId);
		
		$db->setQuery( $sql );
		$result = $db->loadResult();
		
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		// @todo: write a plugin that return the html part of the whole system
		return $result;
	}
	
	/**
	 * Return the number of total activity by a given user 
	 */	 	
	public function getActivityCount($userid) {
		$db	 = &$this->getDBO();
		
		
		$sql = 'SELECT SUM(' . $db->nameQuote('points')
				.') FROM ' . $db->nameQuote('#__community_activities') 
				.' WHERE ' . $db->nameQuote('actor') .'=' . $db->Quote($userid);
		
		$db->setQuery( $sql );
		$result = $db->loadResult();
		
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		// @todo: write a plugin that return the html part of the whole system
		return $result;
	}
	
	/**
	 * Retrieves total number of activities throughout the site.
	 * 
	 * @return	int	$total	Total number of activities.	 	 
	 **/	 	
	public function getTotalActivities(){
		$db		= JFactory::getDBO();
		$query	= 'SELECT COUNT(1) FROM ' . $db->nameQuote('#__community_activities');
		$db->setQuery( $query );
		$total	= $db->loadResult();
		
		return $total;
	}
	/**
	 * Update acitivy stream access
	 *
	 * @param <type> $access
	 * @param <type> $previousAccess
	 * @param <type> $actorId
	 * @param <type> $app
	 * @param <type> $cid
	 * @return <type>
	 *
	 */
	public function updatePermission($access, $previousAccess , $actorId, $app = '' , $cid = '')
	{
		$db	 = &$this->getDBO();
		
		$query	= 'UPDATE ' . $db->nameQuote('#__community_activities') .' SET ' . $db->nameQuote('access') .' = ' . $db->Quote($access);
		$query	.= ' WHERE ' . $db->nameQuote('actor') .' = ' . $db->Quote($actorId);

		if( $previousAccess != null && $previousAccess > $access )
		{
			$query	.= ' AND ' . $db->nameQuote('access') .' <' . $db->Quote( $access );
		}

		if( !empty( $app ) )
		{
			$query	.= ' AND ' . $db->nameQuote('app') .' = ' . $db->Quote($app);
		}
		
		if(! empty($cid))
		{
			$query	.= ' AND ' . $db->nameQuote('cid') .' = ' . $db->Quote($cid);
		}

		$db->setQuery( $query );
		$db->query();
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		return true;
	}
	
	public function updatePermissionByCid($access, $previousAccess = null, $cid, $app)
	{
		// if (is_array($cid)) {}
		
		$db	 = &$this->getDBO();
		
		$query	= 'UPDATE ' . $db->nameQuote('#__community_activities') .' SET ' . $db->nameQuote('access') .' = ' . $db->Quote($access);
		$query	.= ' WHERE ' . $db->nameQuote('cid') .' IN (' . $db->Quote($cid) . ')';
		$query	.= ' AND ' . $db->nameQuote('app') .' = ' . $db->Quote($app);

		if( $previousAccess != null && $previousAccess > $access )
		{
			$query	.= ' AND ' . $db->nameQuote('access') .' <' . $db->Quote( $access );
		}

		$db->setQuery( $query );
		$db->query();
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		return true;
	}
	
	/**
	 * Generic activity update code
	 */	 	
	public function update($condition, $update)
	{
		$db	 = &$this->getDBO();
		
		$where = array();
		foreach($condition as $key => $val)
		{
			$where[] = $db->nameQuote($key) .'=' . $db->Quote($val);
		}
		$where = implode(' AND ', $where);
		
		$set = array();
		foreach($update as $key => $val)
		{
			$set[] = ' '. $db->nameQuote($key) .'=' . $db->Quote($val);
		}
		$set = implode(', ', $set);
		
		$query	= 'UPDATE ' . $db->nameQuote('#__community_activities') .' SET '. $set . ' WHERE '. $where;
		
		$db->setQuery( $query );
		$db->query();
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		return true;
	}
}
