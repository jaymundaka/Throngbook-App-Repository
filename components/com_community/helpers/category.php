<?php
/**
 * @category	Helper
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

class CCategoryHelper
{
	static public function getCategories($rows)
	{
				
		
		// Reset array key
		foreach( $rows as $key=>$row)
		{
			$row				= (array)$row;
			$keyId				= $row['id'];
			$tmpRows[$keyId]	= $row;
		}  

		foreach( $tmpRows as $key=>$row )
		{	                      
			$row['nodeText']	= CCategoryHelper::_getCat( $tmpRows, $row['id'] );

			$row['nodeId']		= explode( ',',CCategoryHelper::_getCatId( $tmpRows, $row['id'] ) );
			$sort1[$key]		= $row['nodeId'][0];
			$sort2[$key]		= $row['parent'];
			
			$categories[]		= $row;
		}
		//array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, $categories);   
		
		return	$categories;

	} 
		
	static private function _getCat($rows,$id) 
	{   
	    if($rows[$id]['parent'] > 0 && $rows[$id]['parent'] != $rows[$id]['id']) {
	        return CCategoryHelper::_getCat($rows, $rows[$id]['parent']) . ' &rsaquo; ' . JText::_( $rows[$id]['name'] );
	    }
	    else {
			return JText::_( $rows[$id]['name'] );
	    }
	}
	 		
	static private function _getCatId($rows,$id) 
	{   
	    if($rows[$id]['parent'] > 0 && $rows[$id]['parent'] != $rows[$id]['id']) {
	        return CCategoryHelper::_getCatId($rows, $rows[$id]['parent']) . ',' . $rows[$id]['id'];
	    }
	    else {
			return $rows[$id]['id']; 
	    }
	}

	static public function getSelectList( $app, $options, $catid=null, $required=false, $update=false )
	{
		$attr	=   '';

		switch ($app)
		{
			case 'groups' : $name = 'categoryid'; break;
			case 'videos' : $name = 'category_id'; break;
			default : $name = 'catid';
		}

		if( $required )
		{
			$attr	.= 'class="inputbox required" ';
		}

		if( $update )
		{
			$attr	.= 'onchange="updateCategoryId()" ';
		}
                
                // Obtain a list of columns
                foreach ($options as $key => $row) {
                    $nodeText[$key]  = $row['nodeText'];
                }
                array_multisort($nodeText, SORT_ASC, $options);


		if( C_JOOMLA_15 )
		{
			return JHTML::_('select.genericlist', $options, $name, $attr, 'id', 'nodeText', $catid );
		}
		else
		{
			return JHTML::_('select.genericlist', $options, $name, array('list.attr' =>$attr, 'option.key'=>'id', 'option.text'=>'nodeText', 'list.select'=>$catid, 'option.text.toHtml'=>false));
		}
	}
}