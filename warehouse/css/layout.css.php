<?php

/**
* @version 1.0
* @package BasicTemplate
* @copyright (C) 2011 by Robin Jungermann
* @license Released under the terms of the GNU General Public License
**/

header("content-type: text/css");



$sitewidth = intval($_GET['sitewidth']);
$sitewidth_unit = "px";

$module_distance = 0;
if($sitewidth_unit == "px") {
	$module_distance=20;
};
if($sitewidth_unit == "%") {
	$module_distance=0;
};


//-----------------------------------------------------------------------------------------


echo ('
@charset "utf-8";

#ct_leftWrapper,
#ct_rightWrapper {
	height: 100%;
	position: absolute;
}

#ct_leftWrapper {
	width: 300px;
	float: left;
}

#ct_rightWrapper {
	display: inline-block;
	min-width: 70%;
	margin-left: 300px;
	float: right;
}

#ct_siteWrapper {
	display: block;
	width: '.$sitewidth.$sitewidth_unit.';
	margin: auto;
	margin-top: 20px;
	margin-bottom: 50px;
}

#ct_headerWrapper_top #ct_headerContent, #ct_sliderWrapper, #ct_sliderShadow {
	width: '.($sitewidth).$sitewidth_unit.';
}

#ct_highlightsContent, #ct_mainContent, #ct_footerContent, #ct_highlightsBorderBottom {
	width: '.$sitewidth.$sitewidth_unit.';
}

.ct_moduleWidth_1 {
	width: '.($sitewidth).$sitewidth_unit.';
}

.ct_moduleWidth_2 {
	width: '.floor(($sitewidth-$module_distance)/2).$sitewidth_unit.';
}

.ct_moduleWidth_3 {
	width: '.floor( ($sitewidth -($module_distance*2))/3).$sitewidth_unit.';
	margin: 0 20px 20px 20px !important;
}

.ct_moduleWidth_4 {
	width: '.floor( ($sitewidth -($module_distance*3))/4).$sitewidth_unit.';
	margin: 0 10px 20px 10px !important;
}

.ct_left, .ct_right {
	width: '.floor( (($sitewidth -($module_distance*3))/4)).$sitewidth_unit.';
}

.ct_left {
	margin: 0 20px 20px 0 !important;	
}

.ct_right {
	margin: 0 0 20px 20px !important;	
}


.ct_componentContent {
	position: relative;
	display: inline;
	float: left;
	height: auto;
	margin: 0 0 30px 0;
	font-size: 14px !important;
}

.ct_componentWidth_2 {
	width: '.floor((($sitewidth-($sitewidth/2))-$module_distance)-10).$sitewidth_unit.';
}

.ct_componentWidth_3 {
	width: '.floor((($sitewidth-($sitewidth/4))-$module_distance)-5).$sitewidth_unit.';
}

.ct_componentWidth_4 {
	width: '.($sitewidth - $module_distance).$sitewidth_unit.';
}

');?>