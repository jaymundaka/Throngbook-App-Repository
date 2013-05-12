<?php
/**
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class CFieldsTextarea
{
	public function getFieldHTML( $field , $required )
	{
		$params	= new CParameter($field->params);
		$readonly	= $params->get('readonly') ? ' readonly=""' : '';
		$disabled	= $params->get('disabled') ? ' disabled=""' : '';
		
		$config	= CFactory::getConfig();
		$js	= 'assets/validate-1.5';
		$js .= ( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js';
		CAssets::attach($js, 'js');
		
		// If maximum is not set, we define it to a default
		$field->max	= empty( $field->max ) ? 200 : $field->max;
	 
		$class	= ($field->required == 1) ? ' required' : '';
		$class	.= !empty( $field->tips ) ? ' jomTips tipRight' : '';
		CFactory::load( 'helpers' , 'string' );
		$html	= '<textarea id="field' . $field->id . '" name="field' . $field->id . '" class="inputbox textarea' . $class . '" title="' . JText::_( $field->name ) . '::' . CStringHelper::escape( JText::_( $field->tips ) ) . '"'.$readonly.$disabled.'>' . $field->value . '</textarea>';
		$html   .= '<span id="errfield'.$field->id.'msg" style="display:none;">&nbsp;</span>';
		$html	.= '<script type="text/javascript">cvalidate.setMaxLength("#field' . $field->id . '", "' . $field->max . '");</script>';
		
		return $html;
	}
	
	public function isValid( $value , $required )
	{
		if( $required && empty($value))
		{
			return false;
		}		
		return true;
	}
}
