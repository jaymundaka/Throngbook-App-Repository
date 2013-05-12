<?php
/**
* @version Geommunity geocoder.php v1.3
* @author Adrien ROUSSEL
* http://geommunity.nordmograph.com
* @package joomla! community builder
* @subpackage Geommunity Geocoder
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) {
	die( 'Direct Access to this location is not allowed.' );
}
// register geocoder function
$_PLUGINS->registerFunction('onBeforeNewUser', 'geocodeAddress', 'geommunitygeocoder');
$_PLUGINS->registerFunction('onBeforeUserUpdate', 'geocodeAddress', 'geommunitygeocoder');
//$_PLUGINS->registerFunction('onBeforeUserRegistration', 'geocodeAddress', 'geommunitygeocoder');
$_PLUGINS->registerFunction('onAfterUserRegistration', 'geocodeAddress', 'geommunitygeocoder');
class geocoder {
	var $address = null;
	var $city = null;
	var $state = null;
	var $country = null;
	var $zipcode = null;
	var $lat = null;
	var $lng = null;
	function geocoder($geoData, $address='address', $city='city', $state='state', $country='country', $zipcode='zipcode', $lat='lat', $lng='lng', $yahoo_appid) {
		$this->address = $geoData->$address;
		$this->city = $geoData->$city;
		$this->state = $geoData->$state;
		$this->country = $geoData->$country;
		$this->zipcode = $geoData->$zipcode;
		//$this->latitude = $geoData->$lat;
		//$this->longitude = $geoData->$lng;
		$this->yahoo_appid = $yahoo_appid;
		return true;
	}
	function geocodeAddress() {
		$query = trim($this->address).','.trim($this->city).','.trim($this->state).','.trim($this->zipcode).','.trim($this->country);
		$query = preg_replace('/^,|,$/', '', preg_replace('/(,)*/', '$1', $query));
		//if (_ISO == 'charset=utf-8')
		//$query = utf8_decode($query);
		$query = urlencode($query);
		$result = $this->googleGeocode($query);
		$lat = $result['lat'];
		$lng = $result['lng'];
		if ($lat == false || $lng == false || $lat=="0" || $lng=="0") {
			$result = $this->yahooGeocode($query, $this->yahoo_appid);
			$lat = $result['lat'];
			$lng = $result['lng'];
		}
		$this->latitude = $lat ? $lat : $this->latitude;
		$this->longitude = $lng ? $lng : $this->longitude;
		return true;
	}
	function googleGeocode($query) {		
		$qurl = 'http://maps.googleapis.com/maps/api/geocode/xml?address='. $query.'&sensor=false';
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $qurl);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$xmlContent = trim(curl_exec($c));
		curl_close($c);
		$xmlObject = simplexml_load_string($xmlContent);
		$result['lat'] = $xmlObject->result->geometry->location->lat;
		$result['lng'] = $xmlObject->result->geometry->location->lng;
		return $result;
	}
	function yahooGeocode($query,$appid) {
		$geourl = "http://where.yahooapis.com/geocode?location=".$query."&appid=".$appid;
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $geourl);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		$xmlContent = trim(curl_exec($c));
		curl_close($c);
		$xmlObject = simplexml_load_string($xmlContent);
		$result['lat'] = $xmlObject->Result->latitude;
		$result['lng'] = $xmlObject->Result->longitude;
		return $result;
	}
}

class geommunitygeocoder extends cbTabHandler {
	function geocodeAddress($user,$cbUser) {	
		global $_CB_database;
		$db = $_CB_database;
		$params = $this->params;
		$mode = $params->get('mode',0);
		$yahoo_appid = $params->get('yahoo_appid');
		$place = new geocoder($cbUser, $params->get('geoAddress','cb_address'), $params->get('geoCity','cb_city'), $params->get('geoState','cb_state'), $params->get('geoCountry','cb_country'), $params->get('geoZipcode','cb_zipcode'), $lat='geoLat', $lng='geoLng', $yahoo_appid);
		$place->geocodeAddress();
		if ( $mode==1 && ($cbUser->$lat == '' || $cbUser->$lng == '') ) { // on profile update and a missing coordinate
			$cbUser->$lat = $place->latitude ? $place->latitude : $cbUser->$lat;
			$cbUser->$lng = $place->longitude ? $place->longitude : $cbUser->$lng;			
			$query ="UPDATE #__comprofiler SET geoLat='".$cbUser->$lat."',geoLng='".$cbUser->$lng."' WHERE id='".$user->id."'";
			$db->setQuery($query);
           $db->query();
		}
		elseif( $mode==0 ){ // every profile update
			$cbUser->$lat = $place->latitude ? $place->latitude : $cbUser->$lat;
			$cbUser->$lng = $place->longitude ? $place->longitude : $cbUser->$lng;
			$query ="UPDATE #__comprofiler SET geoLat='".$cbUser->$lat."',geoLng='".$cbUser->$lng."' WHERE id='".$user->id."'";
			$db->setQuery($query);
            $db->query();
				
		}	
	}
}
?>