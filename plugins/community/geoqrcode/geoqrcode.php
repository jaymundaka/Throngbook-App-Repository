<?php
/**
 * @category	Plugins
 * @package		JomSocial
 * @copyright (C) 2010 Nordmograph.com - All rights reserved!
 * @license		GNU/GPL, see GPL.txt
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');
if(!class_exists('plgCommunityGeoQrcode')){
	class plgCommunityGeoQrcode extends CApplications{
		var $name		= 'GeoQRcode';
		var $_name		= 'geoqrcode';
		var $_user		= null;
	    function plgCommunityGeoQrcode(& $subject, $config){
			$this->_my		= CFactory::getUser();
			$this->db 		=& JFactory::getDBO();
			parent::__construct($subject, $config);
	    } 	 	 	 	 		
		function onProfileDisplay(){	
			JPlugin::loadLanguage( 'plg_geoqrcode', JPATH_ADMINISTRATOR );
			$mainframe =& JFactory::getApplication();
			// Attach CSS
			$document	=& JFactory::getDocument();
			$css		= JURI::base() . 'plugins/community/geoqrcode/style.css';
			$document->addStyleSheet($css);
			$user     = CFactory::getRequestUser();
			$userid	= $user->id;	
			$row = $this->getCoords($userid);		
			$caching = $this->params->get('cache', 1);
			$side = $this->params->get('side', 200);		
			if($caching)
				$caching = $mainframe->getCfg('caching');
			$cache =& JFactory::getCache('plgCommunityGeoQrcode');
			$cache->setCaching($caching);
			$callback = array('plgCommunityGeoQrcode', '_getGeoQrcodeHTML');		
			$content = $cache->call($callback, $userid, $row,$side);
			return $content;
		}
		
		function _getGeoQrcodeHTML($userid, $row, $side){		
			ob_start();				
			if(!empty($row)){
				?>
				<div id="application-geoqrcode">
                <?php 
				echo '<img src="http://chart.apis.google.com/chart?cht=qr&chs='.$side.'x'.$side.'&chl=geo%3A'.$row[0].'%2C'.$row[1].'" alt="Geo QR Code" width="'.$side.'px" height="'.$side.'px" title="Geo QR Code::'.JText::_('PLG_GEOQRCODE SCAN THIS').'" class="jomTips"/>';
				
				////// You might want to remove this link, no problem :)
				////// But before, make sure you visit the Geommunity Page at:
				//////  http://www.nordmograph.com/en/geommunity.html
				////// Geommunity is the best user mapping application for Jomsocial!
				echo '<div style=" font-size:10px;color:#cccccc;">Powered by <a style="color:#cccccc;" href="http://www.nordmograph.com/en/geommunity.html" target="_blank" >Geommunity</a></div>';
				//////  Thank you!
				//////
				?>
                </div>
				<?php
			}
			else{
				?>
				<div><?php echo JText::_('PLG_GEOQRCODE NO DATA')?></div>
				<?php
			}	
			?>
			<div style='clear:both;'></div>
            <?php
			$contents  = ob_get_contents();
			@ob_end_clean();
			$html = $contents;
			return $html;
		}
		
		function getCoords($userid){		
			$sql  = "SELECT  latitude,longitude
						FROM ".$this->db->nameQuote('#__community_users')." 
						WHERE ".$this->db->nameQuote('userid')." = ".$this->db->quote($userid)." 
						AND	latitude <255 AND longitude <255";		
			$query = $this->db->setQuery($sql);
			$row  = $this->db->loadRow();
			if($this->db->getErrorNum()) {
				JError::raiseError( 500, $this->db->stderr());
			}
			return $row;
		}
	}	
}
