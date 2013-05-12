<?php

/**
* @version 1.0
* @package BasicTemplate
* @copyright (C) 2012 by Robin Jungermann
* @license Released under the terms of the GNU General Public License
**/

header("content-type: text/css");


$font = $_GET['font'];

//-----------------------------------------------------------------------------------------



echo (' 
@charset "utf-8";

/* BASIC / GLOBAL STYLES ----------------------------------------*/

* {
	font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
	font-size: 12px;
}

html, body {
	height: 100%;
	margin: 0;
	padding: 0;
}

p {
	line-height: 15px;
}

a {
	outline: none;
}

p img {
	outline: none;
	border: none;
	max-width: 100%;
	margin: 0 10px 10px 0;
	float: left;
}

a img {
	border: none;
	outline: none;
}

/* basic list styles ----------*/

ul {
	list-style: inside;
	list-style-image: url(../images/ul_basic.png);
	padding-left: 2px;
}
ul li {
	background: none;
}

ul li ul, ul li ul li ul, ul li ul li ul li ul {
	margin-left: 13px;
}



dl, dd {
	margin-left: 0;
	padding-left: 0;
}

/* CUSTOM FONTS ----------------------------------------*/

');

if($font == "arvo") {
	echo('
		@font-face {
			font-family: "ArvoBold";
			src: url("../fonts/arvo/Arvo-Regular-webfont.eot");
			src: url("../fonts/arvo/Arvo-Regular-webfont.eot?#iefix") format("embedded-opentype"),
				 url("../fonts/arvo/Arvo-Regular-webfont.woff") format("woff"),
				 url("../fonts/arvo/Arvo-Regular-webfont.ttf") format("truetype"),
				 url("../fonts/arvo/Arvo-Regular-webfont.svg#ArvoRegular") format("svg");
			font-weight: 600;
			font-style: normal;
		}
		
		@font-face {
			font-family: "ArvoRegular";
			src: url("../fonts/arvo/Arvo-Regular-webfont.eot");
			src: url("../fonts/arvo/Arvo-Regular-webfont.eot?#iefix") format("embedded-opentype"),
				 url("../fonts/arvo/Arvo-Regular-webfont.woff") format("woff"),
				 url("../fonts/arvo/Arvo-Regular-webfont.ttf") format("truetype"),
				 url("../fonts/arvo/Arvo-Regular-webfont.svg#ArvoRegular") format("svg");
			font-weight: normal;
			font-style: normal;
		}
		
	');	
}

if($font == "bebas") {
	echo('
		@font-face {
			font-family: "BebasRegular";
			src: url("../fonts/bebas/BEBAS___-webfont.eot");
			src: url("../fonts/bebas/BEBAS___-webfont.eot?#iefix") format("embedded-opentype"),
				 url("../fonts/bebas/BEBAS___-webfont.woff") format("woff"),
				 url("../fonts/bebas/BEBAS___-webfont.ttf") format("truetype"),
				 url("../fonts/bebas/BEBAS___-webfont.svg#BebasRegular") format("svg");
			font-weight: normal;
			font-style: normal;
		}
	');	
}

if($font == "charis") {
	echo('
		@font-face {
			font-family: "CharisSILBold";
			src: url("../fonts/charis/CharisSILB-webfont.eot");
			src: url("../fonts/charis/CharisSILB-webfont.eot?#iefix") format("embedded-opentype"),
				 url("../fonts/charis/CharisSILB-webfont.woff") format("woff"),
				 url("../fonts/charis/CharisSILB-webfont.ttf") format("truetype"),
				 url("../fonts/charis/CharisSILB-webfont.svg#CharisSILBold") format("svg");
			font-weight: normal;
			font-style: normal;
		}
	');	
}



echo('

h1, h1 a, h1 span, 
h2, h2 a, h2 span, 
h3, h3 a, h3 span,
h4, h4 a, h4 span,
h5, h5 a, h5 span,
blockquote,
.label_skitter p a,
.moduletable_ct_linkList a,
.content_vote input.button,
ul.pagenav li a,
table.category th, table.category th a,
.tip-title,
.ct_inlineLink,
a.readmore, p.readmore a, .ct_customLink,
.errorNumber, #errorboxheader,
ul.menu li a,
ul.menu li span,
input.button,
button, 
.ct_buttonYellow, 
.ct_buttonRed, 
.ct_buttonBlue,
.ct_buttonGreen,
.ct_buttonPink,
.ct_buttonBlack,
.ct_buttonWhite,
label, 
legend

{
');

	if($font == "arvo") {echo('font-family: "ArvoBold", "Trebuchet MS", Arial, Helvetica, sans-serif !important;');};
	if($font == "bebas") {echo('font-family: "BebasRegular", "Trebuchet MS", Arial, Helvetica, sans-serif !important; letter-spacing: 0.07em;');};
	if($font == "charis") {echo('font-family: "CharisSILBold", "Trebuchet MS", Arial, Helvetica, sans-serif !important;');};
echo('}');


if($font == "arvo") {echo('
	label, 
	legend
	{
		font-family: "ArvoRegular", "Trebuchet MS", Arial, Helvetica, sans-serif !important;	
	}

');};

echo('
/* ----------------------------------------*/

h1, h1 a, h1 span, 
h2, h2 a, h2 span, 
h3, h3 a, h3 span,
h4, h4 a, h4 span,
h5, h5 a, h5 span,
blockquote
{
	display: block;
	font-weight: normal !important;
	margin: 0;
	padding: 0;
	text-decoration: none;
	width: auto;
}

h1, 
h2,
h3,
h4,
h5 
{
	margin: 0 0 0 0;
}

h1, 
h1 a 
{
	font-size: 30px;
	line-height: 30px;
	text-shadow: 0px 2px 2px rgba(0, 0, 0, 0.30);
	margin-bottom: 18px;
	padding-bottom: 7px;
}

h2,
h2 a,
h2 span
{
	font-size: 25px !important;
	line-height: 28px;
	text-shadow: 0px 1px 2px rgba(0, 0, 0, 0.40);
	margin-bottom: 15px;
}

h3,
h3 a,
h3 span
{
	font-size: 20px;
	line-height: 24px;
	text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.40);
	margin-bottom: 7px;
}

h4, 
h4 a,
h4 span
{
	font-size: 16px;
	line-height: 20px;
	text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.40);
	margin-bottom: 5px;
}

h5 , 
h5 a, 
h5 span 
{
	font-size: 12px;
	line-height: 16px;
	text-shadow: 0px 1px 1px rgba(0, 0, 0, 0.40);
	margin-bottom: 5px;
}

h1 a, 
h2 a, 
h3 a, 
h4 a, 
h5 a 
{
	cursor: pointer;	
}

blockquote 
{
	font-size: 18px;
	font-style:italic;
	line-height: 19px;
}

.label_skitter p 
{
	padding: 10px 18px !important;
}

.label_skitter p a 
{
	font-weight: normal !important;
}

/* LISTS  ------------------------------- */

ul.ct_arrowList, ul.ct_starList, ul.ct_checkList {
	list-style: inside;
}

ul.ct_squareList 		{list-style-image: url(../images/ul_square.png);}
ul.ct_squareList.yellow {list-style-image: url(../images/ul_square_yellow.png);}
ul.ct_squareList.red 	{list-style-image: url(../images/ul_square_red.png);}
ul.ct_squareList.blue 	{list-style-image: url(../images/ul_square_blue.png);}
ul.ct_squareList.green	{list-style-image: url(../images/ul_square_green.png);}
ul.ct_squareList.pink	{list-style-image: url(../images/ul_square_pink.png);}
ul.ct_squareList.black	{list-style-image: url(../images/ul_square_black.png);}
ul.ct_squareList.white	{list-style-image: url(../images/ul_square_white.png);}

ul.ct_arrowList 		{list-style-image: url(../images/ul_arrow.png);}
ul.ct_arrowList.yellow 	{list-style-image: url(../images/ul_arrow_yellow.png);}
ul.ct_arrowList.red 	{list-style-image: url(../images/ul_arrow_red.png);}
ul.ct_arrowList.blue 	{list-style-image: url(../images/ul_arrow_blue.png);}
ul.ct_arrowList.green	{list-style-image: url(../images/ul_arrow_green.png);}
ul.ct_arrowList.pink	{list-style-image: url(../images/ul_arrow_pink.png);}
ul.ct_arrowList.black	{list-style-image: url(../images/ul_arrow_black.png);}
ul.ct_arrowList.white	{list-style-image: url(../images/ul_arrow_white.png);}

ul.ct_starList 			{list-style-image: url(../images/ul_star.png);}
ul.ct_starList.yellow 	{list-style-image: url(../images/ul_star_yellow.png);}
ul.ct_starList.red		{list-style-image: url(../images/ul_star_red.png);}
ul.ct_starList.blue		{list-style-image: url(../images/ul_star_blue.png);}
ul.ct_starList.green	{list-style-image: url(../images/ul_star_green.png);}
ul.ct_starList.pink		{list-style-image: url(../images/ul_star_pink.png);}
ul.ct_starList.black	{list-style-image: url(../images/ul_star_black.png);}
ul.ct_starList.white	{list-style-image: url(../images/ul_star_white.png);}

ul.ct_checkList 		{list-style-image: url(../images/ul_check.png);}
ul.ct_checkList.yellow	{list-style-image: url(../images/ul_check_yellow.png);}
ul.ct_checkList.red		{list-style-image: url(../images/ul_check_red.png);}
ul.ct_checkList.blue	{list-style-image: url(../images/ul_check_blue.png);}
ul.ct_checkList.green	{list-style-image: url(../images/ul_check_green.png);}
ul.ct_checkList.pink	{list-style-image: url(../images/ul_check_pink.png);}
ul.ct_checkList.black	{list-style-image: url(../images/ul_check_black.png);}
ul.ct_checkList.white	{list-style-image: url(../images/ul_check_white.png);}


ul.ct_arrowList li, ul.ct_starList li, ul.ct_checkList li  {
	background: none;
}

ul.ct_arrowList li ul,
ul.ct_starList li ul,
ul.ct_checkList li ul,
ul li ul li ul, ul li ul li ul li ul {
	margin-left: 13px;
}


/* PAGE NAVIGATION */

ul.pagenav, 
ul.pagenav li 
{
	list-style:none outside none;
	background-image: none;
	margin:0;
    padding:0;
}



/* -------------------------------------------------*/

#ct_bgImage, 
#ct_mainWrapper 
{
	height: 100%;
	width: 100%;
}

#ct_bgImage 
{
	position: fixed;
	z-index: 0;
}

#ct_mainWrapper 
{
	position: absolute;
	z-index: 10;
}

.moduletable, 
.ct_componentContent
{
	padding: 10px 10px 15px 10px
}

/* HEADER ELEMENTS -------------------------------------*/

#ct_headerWrapper_top,
#ct_headerWrapper_left,
#ct_headerWrapper_right
{
	z-index: 400;
	height: auto;
}

#ct_headerWrapper_top 
{
	position: relative;
	width: 100%;
}

#ct_headerWrapper_left,
#ct_headerWrapper_right
{
	position: relative;
	width: auto;
	max-width: 30%;
	min-width: 250px;
	top: 20px;
}

#ct_headerWrapper_left 
{
	left: 20px
}

#ct_headerWrapper_right 
{
	right: 20px;
}

#ct_headerContent
{
	height: auto;
	position: relative;
	margin: auto;
	background-position: top center;
	background-repeat: no-repeat;
}


#ct_headerMain 
{
	display: block;
}



#ct_headerWrapper_top #ct_headerMain 
{
	width: 100%;
}

#ct_headerWrapper_left #ct_headerMain 
#ct_headerWrapper_right #ct_headerMain
{
	width: auto;
}

/* Header Tools für Menu Position Top --------------------*/

#ct_headerMain_top #ct_headerTools 
{
	display: inline;
	min-height: 25px;
	margin: 15px 0 15px 0;
}

#ct_headerMain_top #ct_headerLogin, 
#ct_headerMain_top #ct_headerSearch 
{
	position: relative;
	width: auto;
	float: right;
	margin-left: 25px;	
}

#ct_headerMain_top #ct_headerSearch 
{ 
	margin-top: -3px;
}

/*-----------------*/

#ct_headerWrapper_left #ct_headerTools,
#ct_headerWrapper_right #ct_headerTools 
{
	display: block;
}

#ct_headerWrapper_left #ct_headerLogin, 
#ct_headerWrapper_right #ct_headerLogin, 
#ct_headerWrapper_left #ct_headerSearch,
#ct_headerWrapper_right #ct_headerSearch
{
	position: relative;
	width: auto;	
}

#ct_headerWrapper_left #ct_headerSearch,
#ct_headerWrapper_right #ct_headerSearch
{ 
	margin-bottom: 10px;
}

#ct_headerWrapper_left #ct_headerSearch .moduletable,
#ct_headerWrapper_right #ct_headerSearch .moduletable, 
#ct_headerWrapper_left #ct_headerLogin .moduletable,
#ct_headerWrapper_right #ct_headerLogin .moduletable
{
	padding: 7px;
}

#ct_headerWrapper_left #ct_headerLogin .moduletable,
#ct_headerWrapper_right #ct_headerLogin .moduletable
{
    min-height: 25px;
}

/****/

#ct_headerLogin h3, 
#ct_headerSearch h3 
{
	display: none;
}

#ct_headerWrapper_top #ct_logo 
{
 	float:left;
    height:auto;
    margin:0;
    position:relative;
}

#ct_headerWrapper_left #ct_logo,
#ct_headerWrapper_right #ct_logo
{
	margin: 0;
	width: auto;
	height: auto;
	position: relative;
	display: block;
	text-align: center;
}

#ct_headerMain_left #ct_logo,
#ct_headerMain_right #ct_logo 
{
	text-align: center;
}

#ct_logo img
{ 
 	border: none;
}



#ct_mainNavWrapper
{
	margin-top: 10px;
	margin-bottom: 10px;
	position: relative;
	z-index: 1500;
	width: auto;
	height: auto;
}


/* SLIDER -------------------------------------*/

#ct_sliderWrapper 
{
	position: relative;
	z-index: 300;
	height: auto;
	margin: auto;
}

#ct_sliderShadow
{
	width: auto;
	height: 40px;
	margin-left: auto;
	margin-righ: auto;
	background-image: url(../images/slider_shadow.png);
	background-position:  top center;
	background-repeat: no-repeat;
}


#ct_sliderContent
{
	height: 100%;
	margin: auto;
}

#ct_sliderContent .moduletable
{
	background-color: transparent !important;
	padding: 10px !important;
}

.box_skitter 
{
	margin-left: auto;
	margin-right: auto;
}


/* MODULE AREA WRAPPERS -------------------------------------*/

#ct_contentWrapper,
#ct_highlightsWrapper,
#ct_footerWrapper 
{
	position: relative;
	width: 100%;
	height: auto;
}

#ct_contentWrapper
{
	background-position: top center;
	background-repeat: no-repeat;
}

#ct_highlightsContent .ct_moduleWidth_3:first-child, 
#ct_highlightsContent .ct_moduleWidth_3:last-child,
#ct_footerContent .ct_moduleWidth_3:first-child,
#ct_footerContent .ct_moduleWidth_3:last-child,
#ct_footerContent .ct_moduleWidth_4:first-child,
#ct_footerContent .ct_moduleWidth_4:last-child
{
	margin:0 0 20px 0 !important;
}

#ct_highlightsContent .ct_moduleWidth_2:first-child,
#ct_highlightsContent .ct_moduleWidth_4:first-child,
#ct_footerContent .ct_moduleWidth_2:first-child
{
	margin:0 10px 20px 0 !important;
}
#ct_highlightsContent .ct_moduleWidth_2:last-child,
#ct_highlightsContent .ct_moduleWidth_4:last-child,
#ct_footerContent .ct_moduleWidth_2:last-child
{
	margin:0 0 20px 10px !important;
}

#ct_highlightsWrapper
{
	margin-bottom: 20px;
}

#ct_footerWrapper
{
	margin: 20px 0 60px 0;
}

/* MODULE AREAS CONTENTS -------------------------------------*/

#ct_highlightsContent,
#ct_mainContent,
#ct_footerContent
{
	position: relative;
	margin: auto;
}

#ct_highlightsContent 
{	
	padding: 10px 0 10px 0;
}

#ct_highlightsBorderBottom
{
	height: 10px;
	position: relative;
	bottom: -9px;
	margin: auto;
	background-image: url(../images/border-bottom_highlights.png);
	background-position:  top center;
	background-repeat: no-repeat;
}

#ct_mainContent
{
	padding: 10px 0 20px 0;
}

#ct_footerContent 
{
	display: block;
	height: auto;
	padding: 0 0 40px 0;
}

#ct_footerContent h1, 
#ct_footerContent h2, 
#ct_footerContent h3, 
#ct_footerContent h4, 
#ct_footerContent h5 
{
	text-shadow: none !important;
}


/* MODULE POSITION CONTAINERS -------------------------------------*/

.ct_clearFloat 
{
	clear: both;
}

.ct_module, 
.ct_left, 
.ct_right
{
	position: relative;
	display: inline;
	height: auto;
	margin: 0;
}

.ct_left, 
.ct_right
{
	z-index: 150;
}

.ct_module, 
.ct_left 
{
	float: left;
}

.ct_right 
{
	float: right;
}


/* MODULE BOX STYLES -------------------------------------*/

.moduletable_menu
{
	height: auto;
	-webkit-box-shadow: 0 3px 8px rgba(0, 0, 0, 0.75);
	-moz-box-shadow: 0 3px 8px rgba(0, 0, 0, 0.75); 
	box-shadow: 0 3px 8px rgba(0,0, 0, 0.75);
	-pie-box-shadow: 0 2px 0px rgba(0, 0, 0, 0.65);
}



#ct_headerWrapper_top #ct_headerSearch .moduletable, 
#ct_headerWrapper_top #ct_headerLogin .moduletable,
#ct_footerWrapper .moduletable
{
	-webkit-box-shadow: none;
	-moz-box-shadow: none; 
	box-shadow: none;
	-pie-box-shadow: none;
}

.ct_left .moduletable_ct_darkBox, 
.ct_left .moduletable_ct_lightBox, 
.ct_left .moduletable_ct_linkList
.ct_right .moduletable_ct_darkBox, 
.ct_right .moduletable_ct_lightBox, 
.ct_right .moduletable_ct_linkList 
{
	margin: 0 0 30px 0;
}

.ct_left .moduletable, 
.ct_right .moduletable 
{
	padding: 0 0 30px 0;
}

.moduletable_ct_darkBox, 
.moduletable_ct_lightBox 
{
	width: auto;
	padding: 12px 10px 20px 10px;
}

/* LINKLIST ------------------------------------------------------------------------*/

.moduletable_ct_linkList a 
{
	position: relative;
	display: block;
	border: none;
	font-weight: normal;
	font-size: 15px;
	padding: 5px 0 5px 0;
	text-decoration: none;
	padding: 5px 10px 5px 0;
	width: auto;
	background-image: url(../images/icon_link_arrow.png);
	background-color: transparent;
	background-position:  center right;
	background-repeat: no-repeat;
	float: left;
	clear: both;
	cursor: pointer;
}


/* VOTING / RATING ------------------------------------------------------------------------*/

.content_vote 
{
	margin: 5px 0;
}

.content_vote input[type="radio"] 
{
	margin: 0 2px 0 3px;
}

.content_vote input.button 
{
	display: inline;
	text-align: center;
	text-decoration: none;
	height: 23px;
	width: auto;
    cursor:pointer;
    font-size:12px;
    font-weight:normal;
	color:#FFFFFF;
	text-shadow:0 -1px 0 rgba(0, 0, 0, 0.4);
	padding: 2px 7px 7px 7px;
	margin-left: 10px;
}


/* ACTIONS PANEL (PRINT & E-MAIL) ------------------------------------------------------------------------*/

.actions 
{
	margin-top: 6px;
	padding: 0;
	list-style: inside none;
	float: right;
	width: auto;
}

.actions li
{
	float: left;
}

.print-icon, 
.email-icon, 
.edit-icon, 
.print-icon a, 
.email-icon a, 
.edit-icon a  
{
	height: 16px;
	width: 16px;
	background-repeat: no-repeat;
}

.print-icon 
{
	margin-right: 10px;
}

.edit-icon 
{
	margin-left: 10px;
}

.print-icon a img,
.email-icon a img,
.edit-icon a img
{
	opacity: 0;
        -moz-opacity: 0;
        -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
        filter:alpha(opacity=0);	
}


/* LOGIN -----------------------------------------*/

#login-form p 
{
	margin-top: 5px;
}

.ct_loginHelpers 
{
	display: inline-block;
	padding-left: 0;
	list-style: inside;
	list-style-image: url(../images/ul_arrow_2.png);
}

.ct_loginHelpers li 
{
	width: 100%;
	float: left;
}

.ct_loginHelpers li a 
{
	font-weight: bold;
	font-size: 12px;
	text-decoration: none;
}

#form-login-remember 
{
	display: block;
}

#form-login-remember label,
#form-login-remember input
{
	float: left;
}

#form-login-remember label
{
	line-height: 19px;
}

#form-login-remember input 
{
	clear: right;
}

#login-form .button
{
	float: left;
	clear: left;
	margin-top: 10px;
	display: inline-block;
}

#login-form.compact .button 
{
	float: none;
	clear: none;
	margin-top: 0;
}

.login-fields {
	margin-bottom: 10px;
}

.login .button {
	margin-top: 5px !important;
}

/* COMPACT VERSION */

#login-form.compact, 
#login-form.compact fieldset 
{
	width: auto;
}

#login-form.compact p {
	padding: 0 !important;
	margin-top: 0 !important;
	margin-bottom: 0 !important;
	display: block;
}

#login-form.compact #form-login-username label, 
#login-form.compact #form-login-password label,  
#login-form.compact #form-login-remember, 
#login-form.compact .ct_loginHelpers
{
	display: none;
}

#login-form.compact input[type="text"], 
#login-form.compact input[type="password"]
{
	background-repeat:no-repeat;
    height:25px;
    padding-left:30px;
	margin-bottom: 7px;
}

#login-form.compact .button, 
#ct_headerLogin input.button
{
	margin: 0;
	display: block;
	margin:0 !important;
	margin-top: 1px !important;
	text-indent: -9999px;
	overflow: hidden;
	width: 33px;
	padding: 0;
	background-position: right;
	background-repeat: no-repeat;
}

#ct_headerLogin .login-greeting, 
#ct_headerLogin .logout-button 
{
	float:left;
}

.logout-button .button 
{
	margin: 0 !important;
	float: none !important;
}

.login-greeting 
{
	height: 25px;
	font-size: 14px;
	padding-top: 5px;
	margin-right:15px;
}


/* COMPACT VERSION FOR HEADER */

#ct_headerWrapper_top #login-form.compact, 
#ct_headerWrapper_top #login-form.compact fieldset
{
	width: auto;
}

#ct_headerWrapper_top #login-form.compact p {
	padding: 0 !important;
	margin-top: 0 !important;
	margin-bottom: 0 !important;
	float: left;
	display: inline-block;
}

#ct_headerWrapper_top #login-form.compact #form-login-username label, 
#ct_headerWrapper_top #login-form.compact #form-login-password label, 
#ct_headerWrapper_top #login-form.compact #form-login-remember, 
#ct_headerWrapper_top #login-form.compact .ct_loginHelpers 
{
	display: none;
}

#ct_headerWrapper_top #login-form.compact input[type="text"], 
#ct_headerWrapper_top #login-form.compact input[type="password"]
{
	width: 120px;
	height: 25px;
	font-size: 11px;
	margin-right:5px;
}

#ct_headerWrapper_top #login-form.compact .button, 
#ct_headerWrapper_top #ct_headerLogin input.button 
{
	margin: 0;
	float: left;
	display: inline-block;
	margin:0 !important;
	margin-top: 1px !important;
	text-indent: -9999px;
	overflow: hidden;
	width: 33px;
	padding: 0;
	background-position: right;
	background-repeat: no-repeat;
}

#ct_headerWrapper_top #ct_headerLogin .login-greeting, 
#ct_headerWrapper_top #ct_headerLogin .logout-button 
{
	float:left;
}

#ct_headerWrapper_top #login-form.compact .button 
{
	float: none;
	clear: none;
	margin-top: 0;
}

/*-----------------*/



/* RESET / REMIND / REGISTRATION */

.reset, 
.remind, 
.registration 
{
	max-width: 460px;
	white-space: normal;
}


/* HEADER SEARCH ------------------------------------------------------------------------*/

#ct_headerSearch .search label, 
#ct_headerSearch .finder label
{
	visibility: hidden;
	width: 0;
	height: 0;
	padding: 0;
}

#ct_headerSearch .search input, 
#ct_headerSearch .finder input 
{
	height: 25px;
	padding-left: 30px;
	background-repeat: no-repeat;
}

#ct_headerSearch .finder input {
	margin-top: 3px;	
}

#ct_headerWrapper_top #ct_headerSearch .moduletable,
#ct_headerWrapper_top #ct_headerLogin .moduletable {
	background-image: none !important;
}

/* PAGENAV ------------------------------------------------------------------------*/ 

ul.pagenav {
	width: 100%;
	display: block;
	margin: 25px auto;
	clear: left;
}

ul.pagenav li a {
	display: block;
	margin-top: 20px;
	text-align: center;
	text-decoration: none;
	height: 20px;
	width: auto;
	margin-right: 7px;	
    border:medium none;
    cursor:pointer;
    font-size:12px;
    font-weight:normal;
	color:#FFFFFF;
	
	text-shadow:0 -1px 0 rgba(0, 0, 0, 0.4);
	
	-webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
	-moz-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
	box-shadow:0 1px 3px rgba(0, 0, 0, 0.3);
	
	-pie-box-shadow: 0 2px 0px rgba(0, 0, 0, 0.15);
}

ul.pagenav li a:hover {
	color: #fff;
	text-shadow: 0px 0px 5px #fff;
}

ul.pagenav li.pagenav-pre, ul.pagenav li.pagenav-next {

}

ul.pagenav li.pagenav-prev a {
	float: left;
	padding: 3px 10px 0 7px;
}

ul.pagenav li.pagenav-next a {
	float: left;
	padding: 3px 7px 0 10px;
}


/* GLOBAL TABLE STYLES  ------------------------------------------------------------------------*/

table.category th, table.category td {
	padding: 5px 5px 5px 7px;
}

table.category {
	width: 100%;
}

table.category th {
	padding: 5px 10px 5px 0;
}

table.category th, table.category th a {
	text-align: left;
	font-weight: bold;
	font-size: 16px;
	text-decoration: none;
}

table.category th a img {
	margin-left: 5px;
}

table.category th, table.category td {
	padding: 5px;
}


/* SYSTEM MESSAGES -------------------------------------------- */
#system-message dd { 
	text-indent: 45px;
}

#system-message dd.message ul,
#system-message dd.error ul,
#system-message dd.warning ul,
#system-message dd.notice ul,
.bfErrorMessage {
	background-position: left top !important;
	border: 2px solid #ff3600 !important;
	padding-left: 0 !important;
}

#system-message dd.error ul {
	background-image:url("../images/bg_system_alert.png") !important;
}
#system-message dd.warning ul, .bfErrorMessage {
	background-image:url("../images/bg_system_note.png") !important;
}
#system-message dd.message ul, #system-message dd.notice ul {
	background-image:url("../images/bg_system_info.png") !important;
}

#system-message dd.message ul li,
#system-message dd.error ul li,
#system-message dd.warning ul li,
#system-message dd.notice ul li,
.bfErrorMessage {
	color: #ff3600 !important;
	font-size: 14px !important;
	font-weight: bold;
}


/* TOOLTIP ----------------------------------------------------- */

.tip-wrap {
	z-index: 1001 !important;
}

.tip {
	max-width: 300px;
	padding: 5px 10px 10px 10px;
	background-repeat: no-repeat;
}

.tip-title {
	display: inline-block;
	font-weight: normal !important;
	margin: 0 0 5px 23px;
	padding: 0;
	text-decoration: none;
	width: auto;
	font-size: 17px;
	line-height: 17px;
}

.tip-text {
	font-size: 12px;
}


/* LATEST NEWS ---------------------------------------------------------- */

ul.latestnews {
	list-style: inside;
	list-style-image: url(../images/ul_arrow_2.png);
	padding-left: 2px;
}
ul.latestnews li {
	padding: 4px 0 4px 0;
	background: none;
}

ul.latestnews a {
	font-size: 14px;
	font-weight: bold;
	text-decoration: none;
}


/* BLOG-FEATURED ------------------------------------------------------ */

.blog-featured h1 {
	margin-bottom: 20px;
}

.blog-featured h2 {
	margin-bottom: 10px;
}


/* BREADCRUMBS ------------------------------------------------------ */

.ct_breadcrumbs.moduletable {
	padding: 5px 10px;
}

.ct_breadcrumbs span, .ct_breadcrumbs a {
	text-decoration: none;
	font-weight: bold;
}

.ct_breadcrumbsSeparator {
	display: inline-block;
	height: 8px;
	width: 9px;
	background: url(../images/ul_arrow.png);
	background-repeat: no-repeat;
}


/* LINKS ---------------------------------------------------*/

.ct_inlineLink {
	position: relative;
	display: inline-block;
	font-size: 12px;
	text-decoration: none;
	margin: 0 5px;
	padding: 0 9px 2px 0;
	width: auto;
	height: auto;
	background-color: transparent;
	background-position:  center right;
	background-repeat: no-repeat;
}

a.readmore, p.readmore a, .ct_customLink {
	position: relative;
	display: block;
	height: 16px;
	font-size: 13px;
	line-height: 18px;
	background: url(../images/bg_btn_readmore.png);
	background-repeat: no-repeat;
	padding-left: 20px;
	text-decoration: none;
	margin-bottom: 20px;
}

/* ERROR PAGES ---------------------------------------------------*/

#ct_errorWrapper {
	display: block;
	width: 800px;
	margin: auto;
	margin-top: 15px;
	margin-bottom: 50px;
}

.errorNumber, #errorboxheader {
	text-shadow: 1px 3px 3px rgba(0,0,0, 0.5);
	filter: dropshadow(color=#000000, offx=1, offy=3);
	text-align: center;
	display: block;
}

.errorNumber {
	font-size: 300px;
	line-height: 280px;
	margin-top: 50px;
}

#errorboxheader {
	font-size: 26px;
	white-space: nowrap;
}

#errorboxbody {
	margin-top: 50px;
	text-align: center;
}


/* SPAN-STYLES TO HIGHLIGHT SPECIAL CONTENT---------------------------------------------------*/

.ct_tip, 
.ct_alert, 
.ct_info, 
.ct_video,
.ct_contact,
.ct_checklist,
.ct_calendar,
.ct_settings,
.ct_cart,
.ct_delivery,
.ct_sound,
.ct_map {
	display: block;
	width: 100%;
	min-height: 31px;
	background: no-repeat;
	padding: 14px 0 0 0;
	text-indent: 50px;
	margin-bottom: -1px;
}

.ct_tip 		{background-image: url(../images/bg_tip.png);}
.ct_alert 		{background-image: url(../images/bg_alert.png);}
.ct_info 		{background-image: url(../images/bg_info.png);}
.ct_video 		{background-image: url(../images/bg_video.png);}
.ct_contact		{background-image: url(../images/bg_contact.png);}
.ct_checklist	{background-image: url(../images/bg_checklist.png);}
.ct_calendar 	{background-image: url(../images/bg_calendar.png);}
.ct_settings 	{background-image: url(../images/bg_settings.png);}
.ct_cart 		{background-image: url(../images/bg_cart.png);}
.ct_delivery 	{background-image: url(../images/bg_delivery.png);}
.ct_sound 		{background-image: url(../images/bg_sound.png);}
.ct_map 		{background-image: url(../images/bg_map.png);}

#mailto-window { padding: 10px 20px; }

');?>