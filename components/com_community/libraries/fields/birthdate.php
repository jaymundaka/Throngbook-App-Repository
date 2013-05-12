<?php
/**
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.utilities.date');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );
require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'fields' . DS.'date.php');

class CFieldsBirthdate extends CFieldsDate
{
	public function getFieldData( $field )
	{
		$value = $field['value'];
		
		if( empty( $value ) )
			return $value;
		
		$params	= new CParameter($field['params']);
		$format = $params->get('display');
		
		if(! class_exists('CFactory'))
		{
			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );
		}
		
		$ret = '';
		
		if ($format == 'age')
		{
			// PHP version > 5.2
			$datetime	= new DateTime( $value );
			$now		= new DateTime( 'now' );
			
			// PHP version > 5.3
			if (method_exists($datetime, 'diff'))
			{
				$interval	= $datetime->diff($now);
				$ret		= $interval->format('%Y');
			} else {
				$mth		= $now->format( 'm' ) - $datetime->format( 'm');
				$day		= $now->format( 'd' ) - $datetime->format( 'd');
				$ret		= $now->format( 'Y' ) - $datetime->format( 'Y');
				
				if($mth >= 0){
					if($day < 0){
						$ret--;
					}
				}else{
					$ret--;
				}
			}
		}
		else
		{
			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'models' . DS . 'profile.php' );
			$model	= CFactory::getModel( 'profile' );				
			$ret = $model->formatDate($value);
			
			//overwrite Profile date format in Configuration
			$format = $params->get('date_format');
			if ($format)
			{
				$date = new JDate($value);
				$ret = $date->toFormat($format);
			}
		}
		
		return $ret;
	}
}