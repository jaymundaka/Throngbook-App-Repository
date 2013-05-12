<?php
/**
 * @copyright (C) 2010 by Nordmograph.com - All rights reserved!
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
require_once( JPATH_BASE .DS.'components' .DS.'com_community' .DS.'libraries' .DS.'core.php');
class plgCommunitygeommunitylocator extends CApplications{
	var $name 		= "Geommunity Map Locator profile application";
	var $_name		= 'geommunitylocator';
	var $_path		= '';
	var $_user		= '';
	var $_my		= '';
	var $code		= null;
	function plgCommunitygeommunitylocator(& $subject, $config) {
		$this->_user	= CFactory::getRequestUser();
		$this->_my		=& CFactory::getUser();
		$this->db 		= JFactory::getDBO();
  		parent::__construct($subject, $config);
   }
	function onProfileDisplay(){
		JPlugin::loadLanguage( 'plg_geommunitylocator', JPATH_ADMINISTRATOR );
		$config			=& CFactory::getConfig();
		
		$myJconfig 		=& JFactory::getConfig();
		$juri 			= JURI::base();
		$doc			=& JFactory::getDocument();
		$db 						= &JFactory::getDBO();
		$this->loadUserParams();
		$html ='';
		ob_start();
		if(version_compare(JVERSION,'1.6.0','<')){
			//Code for Joomla! 1.5
			$appfiles_path = 'plugins/community/geommunitylocator/';
		}else{
			//Code for Joomla >= 1.6.0
			$appfiles_path = 'plugins/community/geommunitylocator/geommunitylocator/';
		}
		$readonly			= $this->params->get( 'readonly',0 );
		$latfield			= $this->params->get( 'latfield','FIELD_GEOLAT' );
		$lngfield			= $this->params->get( 'lngfield','FIELD_GEOLNG' );
		$type 				= $this->params->get('type','G_NORMAL_MAP');
		$width 				= $this->params->get('width','100%');
		$height 			= $this->params->get('height','400px');
		$zoom 				= $this->params->get('zoom','5');
		$scrollwheel 		= $this->params->get('scrollwheel',1);
		if($scrollwheel)
			$scrollwheel		= 'true';
		else
			$scrollwheel		= 'false';
		$stylez 			= $this->params->get('stylez');
		$geodesic 			= $this->params->get('geodesic',0);
		$modulelink 		= $this->params->get('modulelink','index.php');
		$shoutbox 			= $this->params->get('shoutbox',0);
		$userpoints 		= $this->params->get('userpoints',0);
		$showfriends 		= $this->params->get('showfriends',1);
		$friendslimit		= $this->params->get('friendslimit',100);
		$fnaming 			= $this->params->get('fnaming','username');
		$linecolor 			= $this->params->get('linecolor','333333');
		$privacyaware 		= $this->params->get('privacyaware',0);
		$showevents 		= $this->params->get('showevents',1);
		$eventslimit		= $this->params->get('eventslimit',10);
		$showadsense 		= $this->params->get('showadsense',0); // not yet supported
		$adformat			= $this->params->get('adformat','HALF_BANNER'); // not supported
		$adposition			= $this->params->get('adposition','BOTTOM'); // not supported
		$publisherid		= $this->params->get('publisherid','ca-pub-9324708320571579'); // not supported
		$my_id 				= $this->_my->id;
		$userid				= $this->_user->id;
		$user_username 		= $this->_user->username;
		if($fnaming=='name')
			$user_username =$this->_user->name;
		$user_username 		= ucfirst($user_username);
		$user_username 		= addslashes($user_username);
		$user_username 		= str_replace(CHR(10)," ",$user_username); 
		$user_username 		= str_replace(CHR(13)," ",$user_username);
		$showstreetview 	= $this->params->get( 'showstreetview',1 );
		$showqrcode 		= $this->params->get( 'showqrcode',1 );
		$activitymap 		= $this->params->get( 'activitymap',1 );
		$activitymap_width 		= $this->params->get( 'activitymap_width','390' );
		$activitymap_height 		= $this->params->get( 'activitymap_height','100' );
		$activitymarker_color 	= $this->params->get( 'activitymarker_color','blue' );
		
		$showroute			= $this->params->get( 'showroute',1 );
		$enable_modeoftravel = $this->params->get( 'enable_modeoftravel',1 );
		$default_modeoftravel = $this->params->get( 'default_modeoftravel','DRIVING' );
		$unitsystem = $this->params->get('unitsystem','METRIC');
		
		$mymarker = '';
		$friendsmarkers = '';
		$friendspolylines ='';
		$eventsmarkers = '';
		$eventspolylines ='';
		if($my_id == $userid && !$readonly)
			$html .='<form name="Form" method="post" action="'.CRoute::_('index.php?option=com_community&view=profile').'#geommunity_plugin">';
		function getEvents($userid, $eventslimit){
			$db =& JFactory::getDBO();
			$sql = "SELECT e.id,e.catid, e.title,e.location,e.creator,e.startdate, e.thumb  , e.latitude,e.longitude ,
					ec.name 
					FROM #__community_events AS e 
					LEFT JOIN #__community_events_members AS em ON em.eventid = e.id 
					LEFT JOIN #__community_events_category AS ec ON ec.id = e.catid 
					WHERE (em.memberid = ".$userid." OR e.creator = ".$userid." ) 
					AND e.published ='1' 
					AND e.latitude <255 
					AND e.longitude <255 
					AND e.enddate > NOW() ";
			if($eventslimit != 0){
				$sql .=" LIMIT ".$eventslimit;
			}								
			$db->setQuery($sql);
			return $db->loadObjectList();
		}
		function getFriends($userid, $friendslimit){
			$db =& JFactory::getDBO();
			$sql = "SELECT ".$db->nameQuote("connect_from")."  
					FROM ".$db->nameQuote("#__community_connection")."
					WHERE".$db->nameQuote("connect_to")." = ".$userid." AND
							".$db->nameQuote("status")." = ".$db->Quote("1");
			if($friendslimit != 0){
				$sql .=" LIMIT ".$friendslimit;
			}							
			$db->setQuery($sql);
			return $db->loadObjectList();
		}
		$query = "SELECT latitude, longitude 
		FROM #__community_users 
		WHERE userid='".$userid."' 
		AND latitude < 83 
		AND latitude > -83
		AND longitude > -180 
		AND longitude < 180 ";			
		$this->db->setQuery($query);
     	$coords = $this->db->loadRow();
      	$user_geoLat = $coords[0];
     	$user_geoLng = $coords[1];
		$fake = 0;	
		if (isset($_POST['geoLat'])  && $my_id == $userid && $my_id != 0 && !$readonly) {
			if ( is_numeric($_POST["geoLat"]) && is_numeric($_POST["geoLng"])) {
				$newlat = $_POST["geoLat"];
				$newlng = $_POST["geoLng"];

				if($userpoints ==1){
					include_once( JPATH_ROOT . DS . 'components' . DS . 'com_community'.DS .'libraries' . DS . 'userpoints.php');
					CuserPoints::assignPoint('geommunitylocator.locate');
				}
				if($userpoints ==2){
					$api_AUP = JPATH_SITE.DS.'components'.DS.'com_alphauserpoints'.DS.'helper.php';
					if ( file_exists($api_AUP)){
						require_once ($api_AUP);
						AlphaUserPointsHelper::newpoints( 'plgaup_geommunity_locate' ,'', date('Y-m-d'),date('Y-m-d') );
					}
				}
				$query ="UPDATE #__community_users SET latitude='".$newlat."',longitude='".$newlng."' WHERE userid='".$my_id."'";
				$this->db->setQuery($query);
				if (!$this->db->query()) die($this->db->stderr(true));
				if(isset($_POST['shout'])){
					$url = CRoute::_('index.php?option=com_community&view=profile&userid='.$my_id).'#geommunity_plugin';
					if($shoutbox>0){
						$entryby = JText::_('PLG_GEOLOC_GEOMMUNITY');
						$thetime = time();
						$msg = $user_username." ".JText::_('PLG_GEOLOC_MESSAGE')." <a href='".$url."'>".JText::_('PLG_GEOLOC_GEOMMUNITY')." ".JText::_('PLG_GEOLOC_LOCATOR')."</a>";
					
						$sbtable ='shoutbox';
						if ($shoutbox ==2)
							$sbtable ='liveshoutbox';							
						$query = 'INSERT INTO #__'.$sbtable.' (time,name,text,url)'
							.' VALUES ("'.$thetime.'","'.$entryby .'","'.$msg.'","'.$url.'")';
						$this->db->setQuery($query);
   						if (!$this->db->query()) die($this->db->stderr(true));
					}		
					//activity stream 
					CFactory::load('libraries', 'activities');          
    				$act 			= new stdClass();
    				$act->cmd    	= 'wall.write';
    				$act->actor    	= $my_id;
   					$act->target    = 0; // no target
   					$act->title    	= "{actor} ".JText::_('PLG_GEOLOC_MESSAGE')." <a href='".$url."'>".JText::_('PLG_GEOLOC_GEOMMUNITY')." ".JText::_('PLG_GEOLOC_LOCATOR')."</a>" ;
    				$act->content   = '';
					if($activitymap){
						$zoom1= 5;
						$zoom2=10 ;
						$zoom3= 15;
						$map_type= 'terrain'; // or 'satellite' or 'hybrid' or 'terrain'
						
						$imgurl= 'http://maps.google.com/maps/api/staticmap?center='.$newlat.','.$newlng.'&zoom='.$zoom1.'&size='.$activitymap_width.'x'.$activitymap_height.'&maptype='.$map_type.'&markers=color:'.$activitymarker_color.'|size:tiny|'.$newlat.','.$newlng.'&sensor=false';
						$imgurl_over= 'http://maps.google.com/maps/api/staticmap?center='.$newlat.','.$newlng.'&zoom='.$zoom2.'&size='.$activitymap_width.'x'.$activitymap_height.'&maptype='.$map_type.'&markers=color:'.$activitymarker_color.'|size:tiny|'.$newlat.','.$newlng.'&sensor=false';
						
						$act->content	= '<a  onmouseover="document.activitymap'.$my_id.'.src = \''.$imgurl_over.'\';" onmouseout="document.activitymap'.$my_id.'.src = \''.$imgurl.'\';"   href="'.$url.'"><img name="activitymap'.$my_id.'" src="'.$imgurl.'" alt="Map" title="Geommunity::'.$user_username.' '.JText::_('PLG_GEOLOC_MESSAGE').' '.JText::_('PLG_GEOLOC_GEOMMUNITY').' '.JText::_('PLG_GEOLOC_LOCATOR').'"  class="jomTips" width="'.$activitymap_width.'" height="'.$activitymap_height.'">
						</a>';
					}
    				$act->app    	= 'geommunitylocator';
    				$act->cid    	= 0;
					$act->comment_id	= CActivities::COMMENT_SELF;
					$act->comment_type	= 'profile.location';
					$act->like_id		= CActivities::LIKE_SELF;		
					$act->like_type		= 'profile.location';
    				CActivityStream::add($act);
				}
			}	
		}
		if (($user_geoLng!="" && $user_geoLat!="" && is_numeric($user_geoLat) && is_numeric($user_geoLng))||(isset($_POST['geoLat'])) ){
      		$lat = $user_geoLat;
			$lng = $user_geoLng;
			if ( isset($_POST['geoLat']) ){
				$lat = $newlat;
				$lng = $newlng;
			}	
			
			$mymarker = "var me = new google.maps.LatLng($lat,$lng);
			var marker = new google.maps.Marker({						
				position: me,
				map: map,
				clickable: true,
				draggable: true,					
				title:'".$user_username."',
			});
			var infobulle = new google.maps.InfoWindow({
				content:'".JText::_('PLG_GEOLOC_CLICKAPLACE')." <img src=\'".$juri.$appfiles_path."guess.gif\' alt=\'Guess\'  width=\'16\' height=\'16\'>',
			});
			var infobulle_moved = new google.maps.InfoWindow({
				content:'".JText::_('PLG_GEOLOC_PRESSBUTTON')." <div><input type=\'checkbox\' name=\'shout\' id=\'shout\' title=\'".JText::_('PLG_GEOLOC_ANNOUNCE')."\' checked/> ".JText::_('PLG_GEOLOC_ANNOUNCE')."</div><div><span class=\'readon\'><input type=\'submit\' name=\'geobutton\' id=\'geobutton\' class=\'button\' value=\'".JText::_('PLG_GEOLOC_UPDATEBUTTON')."\' /></span></div>'
			});
			google.maps.event.addListener(marker,'click',function(){
				if(moves == 0){
					infobulle.open(map, marker);
				}
				else{
					infobulle_moved.open(map, marker);
				}
			});
			infobulle.open(map,marker);
			google.maps.event.addListener(marker, 'mousedown', function() {
				infobulle.close();	
				infobulle_moved.close();	
			});
			google.maps.event.addListener(marker, 'dragstart', function() {
				deleteOverlays();	
			});											  
			google.maps.event.addListener(marker, 'dragend', function() {
				var PointTmp = marker.getPosition();
			  	marker.setPosition(PointTmp);
				document.Form.geoLat.value = PointTmp.lat();
    			document.Form.geoLng.value = PointTmp.lng();
				deleteOverlays();				
				infobulle_moved.open(map,marker);
				if ( friendspositionsArray) {								
					for (i=0; i < friendspositionsArray.length; i++) {
						var mepolyline	= new google.maps.LatLng(PointTmp.lat(), PointTmp.lng());
						var friendpolyline	= friendspositionsArray[i];
						var polyline = new google.maps.Polyline({
  							path: [mepolyline,friendpolyline],
							geodesic: ".$geodesic.",
							map:map,
							strokeColor: '".$linecolor."',
							strokeWeight: 1,
							strokeOpacity:1.0
						});
						markersArray.push( polyline );
					}
				}
			});";
			if ($my_id == $userid && $lat=='' && $lng=='' && !$readonly){
				$mymarker ='';
				$lat = '0';
      			$lng = '0';
				$zoom = '2';
			}
			if($showevents){
				$events = getEvents($userid, $eventslimit);
				$eventsmarkers .=" var eventicon= new google.maps.MarkerImage('".$juri.$appfiles_path."event.png',
				new google.maps.Size(12,20),
				new google.maps.Point(0,0),
				new google.maps.Point(6,20));
				var eventshadow= new google.maps.MarkerImage('".$juri.$appfiles_path."small-shadow.png',
				new google.maps.Size(22,20),
				new google.maps.Point(0,0),
				new google.maps.Point(7,20)); ";
				foreach ($events as $event){
					$ev_id=$event->id;
					if($event->latitude!='' && $event->longitude!=''){
						$ev_thumb = $juri.$event->thumb;
						
						if($config->get('storages3bucket')!='' && $config->get('user_avatar_storage')=='s3' )
							$ev_thumb = 'http://'.$config->get('storages3bucket').'.s3.amazonaws.com/'.$event->thumb;	
								
						if(!$event->thumb)
							$ev_thumb = $juri.'components/com_community/assets/event_thumb.png';
							
							
						$eventurl = CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id);
						$eventsmarkers .="
						var event".$event->id." = new google.maps.LatLng(".$event->latitude.",".$event->longitude.");
						var fmarker".$event->id."= new google.maps.Marker({
							position: event".$event->id.",
							map:map,
							clickable: true,
							title:'".addslashes($event->title)." @ ".addslashes($event->location)."',
							icon: eventicon,
							shadow: eventshadow
							});";
						if ($my_id == $userid && !$readonly)
							$eventsmarkers .="friendspositionsArray.push( event".$event->id." );";
						$eventsmarkers .="var finfobulle".$event->id." = new google.maps.InfoWindow({
							content:'<div><div style=\"float:left;width:60px;\"><a href=\"".$eventurl."\"> <img width=\"50\" src=\"".$ev_thumb."\" alt=\"".addslashes($event->title)."\" style=\"border: 1px solid rgb(102, 102, 102); padding: 2px;\"/></a>";
						if ($my_id == $userid && $showroute)
							$eventsmarkers .="<div><a href=\"#geommunity_plugin\"><img src=\"".$juri.$appfiles_path."route.png\" alt=\"\" width=\"34\" height=\"16\" title=\"".JText::_('PLG_GEOLOC_GETDIR')."\" onclick=\"javascript:calcFriendRoute(".$event->latitude.",".$event->longitude.");\" /></a></div>";
							
						$eventsmarkers .="</div><div><a href=\"".$eventurl."\"><b>".addslashes($event->title)."</b></a><br />".addslashes($event->location)."<br />".addslashes($event->startdate)."</div></div>'
						});
						new google.maps.event.addListener(fmarker".$event->id.",'click',function(){
							finfobulle".$event->id.".open(map, fmarker".$event->id.");
						});";
						$eventspolylines .="	
						var mepolyline	= new google.maps.LatLng($lat, $lng);	
						var friend".$event->id."polyline	= new google.maps.LatLng(".$event->latitude.", ".$event->longitude.");
						var polyline".$event->id." = new google.maps.Polyline({
							path: [mepolyline,friend".$event->id."polyline],
							geodesic: ".$geodesic.",
							map:map,
							strokeColor: '".$linecolor."',
							strokeWeight: 1,
							strokeOpacity:1.0
						});";
						if ($my_id == $userid && !$readonly)
								$eventspolylines .=" markersArray.push( polyline".$event->id." );";	
					}	
				}
			}
			if($showfriends){
				$friends = getFriends($userid, $friendslimit);
				$friendsmarkers .=" var friendicon= new google.maps.MarkerImage('".$juri.$appfiles_path."friend.png',
				new google.maps.Size(12,20),
				new google.maps.Point(0,0),
				new google.maps.Point(6,20));
				var friendshadow= new google.maps.MarkerImage('".$juri.$appfiles_path."small-shadow.png',
				new google.maps.Size(22,20),
				new google.maps.Point(0,0),
				new google.maps.Point(7,20)); ";	
				foreach ($friends as $friend){
					$fid=$friend->connect_from;
					////////////////////////////////  Privacy check for Jomsocial
					if($privacyaware ){
						$db =& JFactory::getDBO();
						$my_id = $this->_my->id;
						$friendprivacyok =0;
						$q = "SELECT privacy FROM #__community_apps WHERE apps='geommunitylocator' AND userid='".$fid."'";
						$db->setQuery($q);
						$friendmarkerprivacy = $db->loadResult();
						if($friendmarkerprivacy ==0) //everybody
							$friendprivacyok=1;
						elseif ($friendmarkerprivacy ==10){ //me and my friends: is visitor one of the marker's friends
								$q="SELECT connection_id FROM #__community_connection WHERE connect_from ='".$my_id."' AND  connect_to ='".$fid."' AND status='1' ";
								$db->setQuery($q);
								$visitorisfriend = $db->loadResult();
								if($visitorisfriend) $friendprivacyok=1;
						}
						elseif($markerprivacy ==20){ // only me
							if($fid == $my_id)
								$friendprivacyok=1;
						}
					}
					else
						$friendprivacyok=1;
					/////////////////////////////////////////////////
					if($friendprivacyok){
						$query = "SELECT j.latitude, j.longitude, j.friendcount,j.thumb, u.".$this->db->nameQuote($fnaming)." FROM #__community_users AS j LEFT JOIN #__users AS u ON u.id = j.userid WHERE j.userid='".$fid."' AND j.latitude<255 AND j.longitude<255";			
						$this->db->setQuery($query);
						$coordz = $this->db->loadRow();
						$friend_geoLat = $coordz[0];
						$friend_geoLng = $coordz[1];
						if($coordz[0]!='' && $coordz[1]!=''){
							$naming = addslashes(ucfirst($coordz[4]));
							$thumb = $juri.$coordz[3];
							
								
							if($config->get('storages3bucket')!='' && $config->get('user_avatar_storage')=='s3' )
								$thumb = 'http://'.$config->get('storages3bucket').'.s3.amazonaws.com/'.$coordz[3];	
							//http://mgfforum.s3.amazonaws.com/images/avatar/thumb_1c6eabb9289f582be94b3240.jpg
							if( !$coordz[3] )
								$thumb = $juri.'components/com_community/assets/user_thumb.png';
								
								
								$friendurl = CRoute::_('index.php?option=com_community&view=profile&userid='.$fid);
							$friendsmarkers .="
							var friend".$fid." = new google.maps.LatLng(".$friend_geoLat.",".$friend_geoLng.");
							var fmarker".$fid."= new google.maps.Marker({
								position: friend".$fid.",
								map:map,
								clickable: true,
								title:'".$naming."',
								icon: friendicon,
								shadow: friendshadow
							});";
							if ($my_id == $userid && !$readonly)
								$friendsmarkers .="friendspositionsArray.push( friend".$fid." );";
							$friendsmarkers .="var finfobulle".$fid." = new google.maps.InfoWindow({
								content:'<div><div style=\"float:left;width:60px;\"><a href=\"".$friendurl."\"> <img width=\"50\" src=\"".$thumb."\" alt=\"".$naming."\" style=\"border: 1px solid rgb(102, 102, 102); padding: 2px;\"/></a></div><div><a href=\"".$friendurl."\"><b>".$naming."</b></a><br />".JText::_('PLG_GEOLOC_FRIENDS').": ".$coordz[2]."</div>";
							if ($my_id == $userid && $showroute)
								$friendsmarkers .="<div><a href=\"#geommunity_plugin\"><img src=\"".$juri.$appfiles_path."route.png\" alt=\"\" width=\"34\" height=\"16\" title=\"".JText::_('PLG_GEOLOC_GETDIR')."\" onclick=\"javascript:calcFriendRoute(".$friend_geoLat.",".$friend_geoLng.");\" /></a></div>";
								
								$friendsmarkers .="</div>'
							});
							new google.maps.event.addListener(fmarker".$fid.",'click',function(){
								finfobulle".$fid.".open(map, fmarker".$fid.");
							});";
							$friendspolylines .="var mepolyline	= new google.maps.LatLng($lat, $lng);	
							var friend".$fid."polyline	= new google.maps.LatLng($friend_geoLat, $friend_geoLng);
							var polyline".$fid." = new google.maps.Polyline({
								path: [mepolyline,friend".$fid."polyline],
								geodesic: ".$geodesic.",
								map:map,
								strokeColor: '".$linecolor."',
								strokeWeight: 1,
								strokeOpacity:1.0
							});";
							if ($my_id == $userid && !$readonly)
								$friendspolylines .=" markersArray.push( polyline".$fid." );";
							}
						}
					}
				}
    		}
			else {
				$lat = '0';
				$lng = '0';
				$zoom = '2';
			}

			$visi_lat=0;
			$visi_lng=0;

	
			$mapscript ="function add_Event(obj_, evType_, fn_){ 
      			if (obj_.addEventListener)
					obj_.addEventListener(evType_, fn_, false); 
	  			else
					obj_.attachEvent('on'+evType_, fn_);  
    		}
			
			
			
			
		
			
			
			
			
			
			
			
			
			function calcRoute(visi_lat,visi_lng,modeoftravel) {
				document.getElementById('routelat').value = visi_lat;
						document.getElementById('routelng').value = visi_lng;
				var start;
				if(!modeoftravel )
				modeoftravel = document.getElementById('mode').value;
		
				if(".$my_id."!=0 && visi_lat!=0 && visi_lat!=255 && visi_lng!=0 && visi_lng!=255){
					start	=new google.maps.LatLng(visi_lat,visi_lng);
					var end		=new google.maps.LatLng(".$lat.",".$lng.");
							var request = {
							  origin: start,
							  destination: end,
							  unitSystem: google.maps.DirectionsUnitSystem.".$unitsystem.",
							 travelMode: google.maps.DirectionsTravelMode[modeoftravel]
						};
						
						directionsService.route(request, function(response, status) {
					  if (status == google.maps.DirectionsStatus.OK) {
						document.getElementById('directionspanel').style.display='block';
						directionsDisplay.setDirections(response);
					  }
					  else
					  	alert('".JText::_('PLG_GEOLOC_NOROUTEDATA')."');
					});
				}
				else{
					alert('".JText::_('PLG_GEOLOC_APPROXROUTE')."');
					
					
					if( navigator.geolocation   ) {
						navigator.geolocation.getCurrentPosition(function(position) {
							start = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
							var end		=new google.maps.LatLng(".$lat.",".$lng.");
							var request = {
							  origin: start,
							  destination: end,
							 unitSystem: google.maps.DirectionsUnitSystem.".$unitsystem.",
							 travelMode: google.maps.DirectionsTravelMode[modeoftravel]
						};
						
						directionsService.route(request, function(response, status) {
          if (status == google.maps.DirectionsStatus.OK) {
			document.getElementById('directionspanel').style.display='block';
            directionsDisplay.setDirections(response);
          }
		  else
					  	alert('".JText::_('PLG_GEOLOC_NOROUTEDATA')."');
        });

						});
						
					}
					
					else{
						alert('".JText::_('PLG_GEOLOC_NOWAY')."');
					}			
				}

      }";
	  
	  
	  
	  if($readonly){
	  
	  $mapscript .="function calcFriendRoute(friend_lat,friend_lng,modeoftravel) {
						document.getElementById('routelat').value = friend_lat;
						document.getElementById('routelng').value = friend_lng;
						var start;
						if(!modeoftravel )
							modeoftravel = document.getElementById('mode').value;
					
						if(".$my_id."!=0 && friend_lat!=0 &&friend_lat!=255 && friend_lng!=0 && friend_lng!=255){
							var end	=new google.maps.LatLng(friend_lat,friend_lng);
							start		=new google.maps.LatLng(".$lat.",".$lng.");
										var request = {
										  origin: start,
										  destination: end,
										  unitSystem: google.maps.DirectionsUnitSystem.".$unitsystem.",
										 travelMode: google.maps.DirectionsTravelMode[modeoftravel]
									};
									
									directionsService.route(request, function(response, status) {
								  if (status == google.maps.DirectionsStatus.OK) {
									document.getElementById('directionspanel').style.display='block';
									directionsDisplay.setDirections(response);
								  }
								  else
					  				alert('".JText::_('PLG_GEOLOC_NOROUTEDATA')."');
							});
						}
				  	}";
					
	}
	  
	  

	  
	 $mapscript .="var directionsDisplay;
      var directionsService = new google.maps.DirectionsService();
			
			
			
			
			
			
			
			
			
			function initializemap(){
				directionsDisplay = new google.maps.DirectionsRenderer();
				var latlng = new google.maps.LatLng(".$lat.",".$lng.");
				var myOptions = {
					zoom: ".$zoom.",
					center: latlng,
					mapTypeId: google.maps.MapTypeId.".$type.",
					scrollwheel: ".$scrollwheel.",
					navigationControl: true,
					scaleControl: true,
					mapTypeControl: true,
					streetViewControl: ".$showstreetview."
				}
				var map = new google.maps.Map(document.getElementById('map'), myOptions);
				directionsDisplay.setMap(map);
				directionsDisplay.setPanel(document.getElementById(\"directionspanel\"));
				var me = new google.maps.LatLng($lat,$lng);
				var marker = new google.maps.Marker({						
					position: me,
					map: map,
					clickable: true,					
					title:'".$user_username."'
				});
				var infobulle = new google.maps.InfoWindow({
				content:'".$user_username." ";
				
				
				if($showqrcode){
					$mapscript .="<div style=\'text-align:center;width:250px;height:220px\'><img src=\'http://chart.apis.google.com/chart?cht=qr&chs=200x200&chl=geo%3A".$lat."%2C".$lng."\' width=\'200\' height=\'200\' alt=\'Geo QRCode\' title=\'".addslashes(JText::_('PLG_GEOLOC_QRTITLE'))."\'/></div>";
				}
				$mapscript .=
				"'});
				google.maps.event.addListener(marker,'click',function(){
					infobulle.open(map, marker);
				});
				infobulle.open(map,marker);";
				if($stylez!=''){
					$mapscript .="var stylez = ".$stylez.";
					var styledMapOptions = {
						name: \"1\"
					}
					var GeommunityLocatorMapType = new google.maps.StyledMapType( stylez, styledMapOptions);
					map.mapTypes.set(\"1\", GeommunityLocatorMapType);
					map.setMapTypeId(\"1\");";	
				}
				$mapscript .= $friendsmarkers;
				$mapscript .= $friendspolylines;
				$mapscript .= $eventsmarkers;
				$mapscript .= $eventspolylines;
				if($showadsense && $userid != $my_id){
					$doc->addScript( "http://maps.google.com/maps/api/js?libraries=adsense&sensor=false");
					$mapscript .="var adUnitDiv = document.createElement('adunitdiv');
					var adUnitOptions = {
						format: google.maps.adsense.AdFormat.".$adformat.",
						position: google.maps.ControlPosition.".$adposition.",
						map: map,
						visible: true,
						publisherId: '".$publisherid."'
					}
					adUnit = new google.maps.adsense.AdUnit(adUnitDiv, adUnitOptions);";	
				}
				$mapscript .="}
				function initgmap() {
      				//if (arguments.callee.done) GUnload();
      				arguments.callee.done = true;
      				initializemap();
    			};
    			add_Event(window, 'load', initgmap);";
				if ($my_id == $userid && !$readonly){
					$mapscript ="var markersArray = [];
					var friendspositionsArray = [];
					var moves = 0;
					function add_Event(obj_, evType_, fn_){ 
						if (obj_.addEventListener)
							obj_.addEventListener(evType_, fn_, false); 
						else
							obj_.attachEvent('on'+evType_, fn_);  
					}
					function deleteOverlays() {
						if (markersArray) {
							for (i=0; i < markersArray.length; i++) {
								markersArray[i].setMap(null);
							}
							markersArray.length = 0;
						}
					}
					
					function calcFriendRoute(friend_lat,friend_lng,modeoftravel) {
						document.getElementById('routelat').value = friend_lat;
						document.getElementById('routelng').value = friend_lng;
						var start;
						if(!modeoftravel )
							modeoftravel = document.getElementById('mode').value;
					
						if(".$my_id."!=0 && friend_lat!=0 &&friend_lat!=255 && friend_lng!=0 && friend_lng!=255){
							var end	=new google.maps.LatLng(friend_lat,friend_lng);
							start		=new google.maps.LatLng(document.getElementById('geoLat').value,document.getElementById('geoLng').value);
										var request = {
										  origin: start,
										  destination: end,
										  unitSystem: google.maps.DirectionsUnitSystem.".$unitsystem.",
										 travelMode: google.maps.DirectionsTravelMode[modeoftravel]
									};
									
									directionsService.route(request, function(response, status) {
								  if (status == google.maps.DirectionsStatus.OK) {
									document.getElementById('directionspanel').style.display='block';
									directionsDisplay.setDirections(response);
								  }
								  else
					  				alert('".JText::_('PLG_GEOLOC_NOROUTEDATA')."');
							});
						}
				  	}
					
					
					
					 var directionsDisplay;
      var directionsService = new google.maps.DirectionsService();
	  
					function initializemap(){
						directionsDisplay = new google.maps.DirectionsRenderer();
						var latlng = new google.maps.LatLng(".$lat.",".$lng.");
						var myOptions = {
							zoom: ".$zoom.",
							center: latlng,
							mapTypeId: google.maps.MapTypeId.".$type.",
							scrollwheel: ".$scrollwheel.",
							navigationControl: true,
							scaleControl: true,
							mapTypeControl: true,
							streetViewControl: ".$showstreetview."
						}
						var map = new google.maps.Map(document.getElementById('map'), myOptions);
						directionsDisplay.setMap(map);
				directionsDisplay.setPanel(document.getElementById(\"directionspanel\"));";
						
						if($my_id ==$userid && ($lat==0 || $lng==0 || $lat=='' || $lng=='')){
							
							$mapscript .="var me = new google.maps.LatLng(255,255);  //off the map
						var marker = new google.maps.Marker({						
						position: me,
						map: map,
						clickable: true,					
						draggable:true,
						title:'".$user_username."'
						});
						var infobulle = new google.maps.InfoWindow({
							content:'',
						});
						var infobulle_moved = new google.maps.InfoWindow({
							content:'".JText::_('PLG_GEOLOC_PRESSBUTTON')." <div><input type=\'checkbox\' name=\'shout\' id=\'shout\' title=\'".JText::_('PLG_GEOLOC_ANNOUNCE')."\' checked/> ".JText::_('PLG_GEOLOC_ANNOUNCE')."</div><div><span class=\'readon\'><input type=\'submit\' name=\'geobutton\' id=\'geobutton\' class=\'button\' value=\'".JText::_('PLG_GEOLOC_UPDATEBUTTON')."\' /></span></div>'
						});
						
						google.maps.event.addListener(marker, 'dragstart', function() {
				deleteOverlays();	
			});											  
			google.maps.event.addListener(marker, 'dragend', function() {
				var PointTmp = marker.getPosition();
			  	marker.setPosition(PointTmp);
				document.Form.geoLat.value = PointTmp.lat();
    			document.Form.geoLng.value = PointTmp.lng();
				deleteOverlays();				
				infobulle_moved.open(map,marker);
				if ( friendspositionsArray) {								
					for (i=0; i < friendspositionsArray.length; i++) {
						var mepolyline	= new google.maps.LatLng(PointTmp.lat(), PointTmp.lng());
						var friendpolyline	= friendspositionsArray[i];
						var polyline = new google.maps.Polyline({
  							path: [mepolyline,friendpolyline],
							geodesic: ".$geodesic.",
							map:map,
							strokeColor: '".$linecolor."',
							strokeWeight: 1,
							strokeOpacity:1.0
						});
						markersArray.push( polyline );
					}
				}
			});
						
						";
						}
			
				
						
						
						$mapscript .="google.maps.event.addListener(map, 'click', function(event) {
							var PointTmp2 = event.latLng;
							marker.setPosition(PointTmp2);
							document.Form.geoLat.value = event.latLng.lat();
							document.Form.geoLng.value = event.latLng.lng();
							deleteOverlays();
							infobulle.close();
							infobulle_moved.open(map,marker);
							moves ++;
							if ( friendspositionsArray) {								
								for (i=0; i < friendspositionsArray.length; i++) {
									var mepolyline	= new google.maps.LatLng(PointTmp2.lat(), PointTmp2.lng());
									var friendpolyline	= friendspositionsArray[i];
									var polyline = new google.maps.Polyline({
										path: [mepolyline,friendpolyline],
										geodesic: ".$geodesic.",
										map:map,
										strokeColor: '".$linecolor."',
										strokeWeight: 1,
										strokeOpacity:1.0
									});
									markersArray.push( polyline );
								}
							}									
						});";
						if($stylez!=''){
							$mapscript .="var stylez = ".$stylez.";
							var styledMapOptions = {
								name: '1'
							}
							var GeommunityLocatorMapType = new google.maps.StyledMapType( stylez, styledMapOptions);
							map.mapTypes.set('1', GeommunityLocatorMapType);
							map.setMapTypeId('1');";	
						}
						$mapscript .= $mymarker;
						$mapscript .= $friendsmarkers;
						$mapscript .= $friendspolylines;
						$mapscript .= $eventsmarkers;
						$mapscript .= $eventspolylines;
						if($showadsense){
							$doc->addScript( "http://maps.google.com/maps/api/js?libraries=adsense&sensor=true");
							$mapscript .="var adUnitDiv = document.createElement('adunitdiv');
								var adUnitOptions = {
										format: google.maps.adsense.AdFormat.".$adformat.",
										position: google.maps.ControlPosition.".$adposition.",
										map: map,
										visible: true,
										publisherId: '".$publisherid."'
								 }
								 adUnit = new google.maps.adsense.AdUnit(adUnitDiv, adUnitOptions);";	
						}
						$mapscript .= "
					}
					function initgmap() {
						//if (arguments.callee.done) GUnload();
						arguments.callee.done = true;
						initializemap();
					};
    				add_Event(window, 'load', initgmap);";
				}
				$html .='<a name="geommunity_plugin" id="geommunity_plugin"></a>';
				if($lat !=0 && $lng!=0 && $lat!='' && $lng!=''  || $my_id ==$userid){
					$html .='<table cellspacing="0" cellpadding="1" style="width:'.$width.';text-align:left;">';
					$html .='<tr class="sectiontableentry2"><td><div style="width:30%;float:left;"><img src="http://maps.google.com/intl/en_us/mapfiles/marker.png" alt="Geommunity" width="10" height="17"/> '.$user_username.'</div>';
					if($showfriends)
						$html .= '<div style="width:30%;float:left;"><img src="'.$juri.$appfiles_path.'friend.png" alt="'.JText::_('PLG_GEOLOC_FRIENDS').'" width="6" height="10"> '.JText::_('PLG_GEOLOC_FRIENDS').'</div>';
					if($showevents)
						$html .= '<div style="width:30%;float:left;"><img src="'.$juri.$appfiles_path.'event.png" alt="'.JText::_('PLG_GEOLOC_EVENTS').'" width="6" height="10"> '.JText::_('PLG_GEOLOC_EVENTS').'</div>';
					if ($modulelink !=''){
						$html .='<div style="width:16px;float:right;text-align:right">';
						$html .='<a href="'.$modulelink.'?markerid='.$userid.'#geommunity_module" ><img src="'.$juri.$appfiles_path.'favicon.png" alt="Geommunity" width="16" height="16" title="'.JText::_('PLG_GEOLOC_GLOBALMAP').'" ></a>';
						$html .='</div>';
					}
					if($my_id ==$userid && ($lat==0 || $lng==0 || $lat=='' || $lng=='') && $readonly!='1')
						$html .='<div style="clear:both;">'.JText::_('PLG_GEOLOC_CLICKPLACE').'</div>';
						
					$html .='</td></tr>';
					$html .='</table>';
					if($my_id == $userid && !$readonly)
						$doc->addScript( "http://maps.google.com/maps/api/js?sensor=true");
					else
						$doc->addScript( "http://maps.google.com/maps/api/js?sensor=false");
					$doc->addScriptDeclaration($mapscript);
					$html .= "<div id=\"map\" style=\"color:#000000;height:".$height.";width:".$width.";border:1px solid #ccc\">";
					$html .= "<div style=\"text-align:center;\"><br /><br /><br /><a href=\"http://http://www.nordmograph.com/en/geommunity.html\"><img src=\"".$juri.$appfiles_path."loader.gif\" alt=\"Geommunity\" title=\"Geommunity\" width=\"42\" height=\"42\" /></a></div>";
					$html .= "</div>";
					$html .='<table style="width:'.$width.'" cellspacing="0" cellpadding="1">';
					$html .='<tr class="sectiontableentry2">';
					$html .= '<td>';
					$html .= '<a href="javascript:initgmap();"><img src="'.$juri.$appfiles_path.'reset.gif" alt="'.JText::_('PLG_GEOLOC_RESETMAP').'"  title="'.JText::_('PLG_GEOLOC_RESETMAP').'" width="16" height="16"/></a>';	
					$html .= '</td>';
					
					
					
					
					if($my_id == $userid && !$readonly){
						
			 				$browserjs ="function updategmap(){
				 			navigator.geolocation.getCurrentPosition(function(position) {							   
								document.getElementById('geoLat').value = position.coords.latitude;
								document.getElementById('geoLng').value = position.coords.longitude;
								var guessed = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
								var myOptions = {
							zoom: 13,
							center: guessed,
							mapTypeId: google.maps.MapTypeId.".$type.",
							scrollwheel: ".$scrollwheel.",
							navigationControl: true,
							scaleControl: true,
							mapTypeControl: true,
							streetViewControl: ".$showstreetview."
						}
						var map = new google.maps.Map(document.getElementById('map'), myOptions);";
						if($stylez!=''){
							$browserjs .="var stylez = ".$stylez.";
							var styledMapOptions = {
								name: '1'
							}
							var GeommunityLocatorMapType = new google.maps.StyledMapType( stylez, styledMapOptions);
							map.mapTypes.set('1', GeommunityLocatorMapType);
							map.setMapTypeId('1');";	
						}
						$browserjs .=" deleteOverlays();
						var marker = new google.maps.Marker({						
								position: guessed,
								map: map,
								clickable: true,
								draggable: true,					
								title:'".$user_username."'
						});
						var infobulle_moved = new google.maps.InfoWindow({
							content:'".JText::_('PLG_GEOLOC_PRESSBUTTON')." <div><input type=\'checkbox\' name=\'shout\' id=\'shout\' title=\'".JText::_('PLG_GEOLOC_ANNOUNCE')."\' checked/> ".JText::_('PLG_GEOLOC_ANNOUNCE')."</div><div><span class=\'readon\'><input type=\'submit\' name=\'geobutton\' id=\'geobutton\' class=\'button\' value=\'".JText::_('PLG_GEOLOC_UPDATEBUTTON')."\' /></span></div>'
						});
						google.maps.event.addListener(marker,'click',function(event){
							infobulle_moved.open(map, marker);
						});
						infobulle_moved.open(map,marker);
						google.maps.event.addListener(marker, 'mousedown', function() {
							infobulle_moved.close();											   
						});
						google.maps.event.addListener(marker, 'mouseup', function() {
								infobulle_moved.open(map,marker);											   
						});
						$friendsmarkers;
						$eventsmarkers;
						google.maps.event.addListener(marker, 'dragstart', function() {
							deleteOverlays();
						});										  
						google.maps.event.addListener(marker, 'dragend', function() {
							var PointTmp = marker.getPosition();
							marker.setPosition(PointTmp);
							document.Form.geoLat.value = PointTmp.lat();
							document.Form.geoLng.value = PointTmp.lng();						
							if ( friendspositionsArray) {								
								for (i=0; i < friendspositionsArray.length; i++) {
									var mepolyline	= new google.maps.LatLng(PointTmp.lat(), PointTmp.lng());
									var friendpolyline	= friendspositionsArray[i];
									var polyline = new google.maps.Polyline({
										path: [mepolyline,friendpolyline],
										geodesic: ".$geodesic.",
										map:map,
										strokeColor: '".$linecolor."',
										strokeWeight: 1,
										strokeOpacity:1.0
									});
									markersArray.push( polyline );
								}
							}
						 });
						google.maps.event.addListener(map, 'click', function(event) {
							var PointTmp2 = event.latLng;
							marker.setPosition(PointTmp2);
							document.Form.geoLat.value = event.latLng.lat();
							document.Form.geoLng.value = event.latLng.lng();
							deleteOverlays();							
							if ( friendspositionsArray) {								
								for (i=0; i < friendspositionsArray.length; i++) {
									var mepolyline	= new google.maps.LatLng(PointTmp2.lat(), PointTmp2.lng());
									var friendpolyline	= friendspositionsArray[i];
									var polyline = new google.maps.Polyline({
										path: [mepolyline,friendpolyline],
										geodesic: ".$geodesic.",
										map:map,
										strokeColor: '".$linecolor."',
										strokeWeight: 1,
										strokeOpacity:1.0
									});
									markersArray.push( polyline );
								}
							}									
						});
						if ( friendspositionsArray) {								
							for (i=0; i < friendspositionsArray.length; i++) {
								var friendpolyline	= friendspositionsArray[i];
								var polyline = new google.maps.Polyline({
									path: [guessed,friendpolyline],
									geodesic: ".$geodesic.",
									map:map,
									strokeColor: '".$linecolor."',
									strokeWeight: 1,
									strokeOpacity:1.0
								});
								markersArray.push( polyline );
							}
						}
			 		})
			 	}";
				$doc->addScriptDeclaration($browserjs);
				$html .= '<td>';
				$html .= '<a href="javascript:updategmap();"><img src="'.$juri.$appfiles_path.'guess.gif" alt="'.JText::_('PLG_GEOLOC_GUESS').'" title="'.JText::_('PLG_GEOLOC_GUESS').'"  width="16" height="16"></a>';	
				$html .= '</td>';
				$html .= '<td>';
				$html .= '<div style="text-align:right;"><b>'.JText::_('PLG_GEOLOC_LAT').'</b> ';
				$html .= '<input type="text" maxlength="150" size="10" value="'.$lat.'" id="geoLat" name="geoLat" title="'.JText::_('PLG_GEOLOC_LAT').'" readonly/>';
				$html .= '<b>'.JText::_('PLG_GEOLOC_LNG').'</b> ';
				$html .= '<input type="text" maxlength="150" size="10" value="'.$lng.'" id="geoLng" name="geoLng" title="'.JText::_('PLG_GEOLOC_LNG').'" readonly/>';	
				$html .= '</div></td>';
				$html .='</form>';
			}
			
			
			if($showroute && $my_id != $userid){
				$q ="SELECT latitude,longitude FROM #__community_users WHERE userid='".$my_id."' ";
				$db->setQuery($q);
				$visitor_coords = $db->loadRow();
				$visi_lat=$visitor_coords[0];
				$visi_lng=$visitor_coords[1];
				if($my_id==0){
					$visi_lat=0;
					$visi_lng=0;
				}
					$html .='<td><div style="float:right"><a href="#geommunity_plugin"><img src="'.$juri.$appfiles_path.'route.png" alt="" width="34" height="16" title="'.JText::_('PLG_GEOLOC_GETDIR').'" onclick="javascript:calcRoute('.$visi_lat.','.$visi_lng.');" /></a></div></td>';
			}
			
			
			
			$html .='</tr>';
			$html .='</table>';
			
			
			
			
			
			if( $showroute){
			
				$html .='<div id="directionspanel" style="display:none"><hr />';
				$html .='<input type="hidden" id="routelat" name="routelat" /><input type="hidden" id="routelng" name="routelng" />';
				if($enable_modeoftravel){
					$html .='<div id="modeoftravel" style="float:left">';
					if($userid!=$my_id)
						$html .='<select id="mode" onchange="calcRoute('.$visi_lat.','.$visi_lng.',this.value);">';
					elseif($my_id==$userid)
						$html .='<select id="mode" onchange="calcFriendRoute(document.getElementById(\'routelat\').value,document.getElementById(\'routelng\').value,this.value);">';
				  $html .='<option value="DRIVING" ';
				  if($default_modeoftravel=='DRIVING') $html .= 'selected';
				  $html .='>'.JText::_('PLG_GEOLOC_DRIVING').'</option>';
				  $html .='<option value="WALKING" ';
				  if($default_modeoftravel=='WALKING') $html .= 'selected';
				  $html .='>'.JText::_('PLG_GEOLOC_WALKING').'</option>';
				  $html .='<option value="BICYCLING" ';
				  if($default_modeoftravel=='BICYCLING') $html .= 'selected';
				  $html .='>'.JText::_('PLG_GEOLOC_BICYCLING').'</option>';
				$html .='</select></div>';
				}
				else
					$html .='<div id="modeoftravel"><input type="hidden" id="mode" name="mode" value="'.$default_modeoftravel.'" /></div>';
				$html .='<div id="closepanel_top" style="float:right;cursor:pointer"><img src="'.$juri.$appfiles_path.'close.png" alt="X" width="16" height="16" class="hasTipas" title="'.JText::_('PLG_GEOLOC_CLOSEDIRPANEL').'" onclick="document.getElementById(\'directionspanel\').style.display=\'none\';" /></div><div style="clear:both"></div>';
				
				$html .='<div id="closepanel_bot" style="float:right;bottom:2px;right:5px;position:absolute;cursor:pointer"><img src="'.$juri.$appfiles_path.'close.png" alt="X" width="16" height="16" class="hasTipas" title="'.JText::_('PLG_GEOLOC_CLOSEDIRPANEL').'" onclick="document.getElementById(\'directionspanel\').style.display=\'none\';" /></div><div style="clear:both"></div>';
				$html .='</div>';
			
			
			
			}
			
			
			
			
			
			
			
			
			
			
			
		}
		else{
			$html .='<div><img src="'.$juri.$appfiles_path.'favicon.png" alt="Geommunity" width="16" height="16"/> <b>'.$user_username.'</b> '.JText::_('PLG_GEOLOC_HASNOTYET').'</div>';
		}
		echo $html;
		$contents	= ob_get_contents();
		ob_end_clean();		
		return $contents;		    		
	}
}
?>