<?php

/**
* @version 1.1
* @package cleanlogic
* @copyright (C) 2011 by Robin Jungermann
* @license Released under the terms of the GNU General Public License
**/

header("content-type: text/css");

$color = $_GET['color']; // accent color from template settings
$content_color = $_GET['content_color']; // base color from template settings
$bg_style = $_GET['bg_style']; // background style from template settings
$templateUrl = $_GET['templateurl'];

//break up the color in its RGB components for later use
$r_accent = hexdec(substr($color,0,2));
$g_accent = hexdec(substr($color,2,2));
$b_accent = hexdec(substr($color,4,2));
$accent_brightness = ceil(0.299 * $r_accent  + 0.587 * $g_accent + 0.114 * $b_accent);

$r_base = hexdec(substr($content_color,0,2));
$g_base = hexdec(substr($content_color,2,2));
$b_base = hexdec(substr($content_color,4,2));
$base_brightness = ceil(0.299 * $r_base  + 0.587 * $g_base + 0.114 * $b_base);

echo("/* R = ".$r_base."/ G = ".$g_base."/ B= ".$b_base."/ BRIGHTNESS = ".$base_brightness." */");

echo ("/* ABERRATION =  ".((($r_base + $g_base + $b_base) / 3)-$r_base)." */" );



$darkerColor = ""; // darker version of the accent color
$lighterColor = ""; // lighter version of the accent color

include("hsv_color.php");

function ct_colorShade($hex,$factor = 30){
	$new_hex = ''; 
     
    $base['R'] = hexdec($hex{0}.$hex{1}); 
    $base['G'] = hexdec($hex{2}.$hex{3}); 
    $base['B'] = hexdec($hex{4}.$hex{5}); 
     
    foreach ($base as $k => $v) 
        { 
        $amount = 255 - $v; 
        $amount = $amount / 100; 
        $amount = round($amount * $factor); 
        $new_decimal = $v + $amount; 
     
        $new_hex_component = dechex($new_decimal); 
        if(strlen($new_hex_component) < 2) 
            { $new_hex_component = "0".$new_hex_component; } 
        $new_hex .= $new_hex_component; 
        } 
         
    return $new_hex;  
};






if(!preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $parts))
  die("Not a value color");
  
if(!preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $partsDarker))
  die("Not a value color");


for($i = 1; $i <= 3; $i++) {
	$parts[$i] = hexdec($parts[$i]);
	$parts[$i] = round($parts[$i]) * 1; // 0% lighter than the main color
	$lighterColor .= str_pad(dechex($parts[$i]), 2, '0', STR_PAD_LEFT);
}

for($i = 1; $i <= 3; $i++) {
	$partsDarker[$i] = hexdec($partsDarker[$i]);
	$partsDarker[$i] = round($partsDarker[$i]) * 0.75; // 25% darker than the main color
	$darkerColor .= str_pad(dechex($partsDarker[$i]), 2, '0', STR_PAD_LEFT);
}


echo("

@charset 'utf-8';
/* CSS Document */

/* SET BACKGROUND STYLE ----------------------------------------------------------------------------- */

body {
	background-image: url(../images/bg_main_".$bg_style.".jpg);
	background-position: center;
}


/* SET ACCENT COLOR STYLES ----------------------------------------------------------------------------- */

a:hover,
h1, h1 a, h1 span, 
h2, h2 a, h2 span, 
h3, h3 a, h3 span,
h4, h4 a, h4 span,
h5, h5 a, h5 span,
#ct_loginHelpers li:hover a,
ul.latestnews li:hover a,
.ct_breadcrumbs a:hover,
a.readmore:hover, p.readmore a:hover,
.categories-list span.item-title a:hover,
.category td a:hover,
.category th a:hover,
.registration legend,
.search-results .result-title:hover,
.search-results .result-title:hover a,
ul.circleList li, 
ul.circleList li ul li,
#ct_footerContent h1, 
#ct_footerContent h2, 
#ct_footerContent h3, 
#ct_footerContent h4, 
#ct_footerContent h5,
.tip-title
{
	color: #".$color.";
}

input.button, button
{
    background: url(../images/bg_btn.png);
	background-color: #".$lighterColor.";
	background: url(../images/bg_btn.png), -moz-linear-gradient(top,  #".$lighterColor." 0%, #".$darkerColor." 100%);
	background: url(../images/bg_btn.png),  -webkit-gradient(linear, left top, left bottom, color-stop(0%,#".$lighterColor."), color-stop(100%,#".$darkerColor."));
	background: url(../images/bg_btn.png),  -webkit-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: url(../images/bg_btn.png),  -o-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: url(../images/bg_btn.png),  -ms-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: url(../images/bg_btn.png),  linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	
	-pie-background: url(".$templateUrl."/images/bg_btn.png) no-repeat right, linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
}

.ct_buttonYellow 
{
	color: #777 !important;
	background-color: #ffe400;
	background: -moz-linear-gradient(top,  #ffe400 0%, #af9417 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffe400), color-stop(100%,#af9417));
	background: -webkit-linear-gradient(top,  #ffe400 0%,#af9417 100%);
	background: -o-linear-gradient(top,  #ffe400 0%,#af9417 100%);
	background:  -ms-linear-gradient(top,  #ffe400 0%,#af9417 100%);
	background:  linear-gradient(top,  #ffe400 0%,#af9417 100%);
	
	-pie-background: linear-gradient(top, #ffe400 0%, #af9417 100%);
} 

.ct_buttonRed 
{
	background-color: #ed0000;
	background: -moz-linear-gradient(top,  #ed0000 0%, #9e1815 100%);
	background:  -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ed0000), color-stop(100%,#9e1815));
	background:  -webkit-linear-gradient(top,  #ed0000 0%,#9e1815 100%);
	background:  -o-linear-gradient(top,  #ed0000 0%,#9e1815 100%);
	background:  -ms-linear-gradient(top,  #ed0000 0%,#9e1815 100%);
	background:  linear-gradient(top,  #ed0000 0%,#9e1815 100%);
	
	-pie-background: linear-gradient(top, #ed0000 0%, #9e1815 100%);
} 

.ct_buttonBlue 
{
	background-color: #0072ff;
	background: -moz-linear-gradient(top,  #0072ff 0%, #29487a 100%);
	background:  -webkit-gradient(linear, left top, left bottom, color-stop(0%,#0072ff), color-stop(100%,#29487a));
	background:  -webkit-linear-gradient(top,  #0072ff 0%,#29487a 100%);
	background:  -o-linear-gradient(top,  #0072ff 0%,#29487a 100%);
	background:  -ms-linear-gradient(top,  #0072ff 0%,#29487a 100%);
	background:  linear-gradient(top,  #0072ff 0%,#29487a 100%);
	
	-pie-background: linear-gradient(top, #0072ff 0%, #29487a 100%);
} 

.ct_buttonGreen 
{
	background-color: #58d000;
	background: -moz-linear-gradient(top,  #58d000 0%, #477d1d 100%);
	background:  -webkit-gradient(linear, left top, left bottom, color-stop(0%,#58d000), color-stop(100%,#477d1d));
	background:  -webkit-linear-gradient(top,  #58d000 0%,#477d1d 100%);
	background:  -o-linear-gradient(top,  #58d000 0%,#477d1d 100%);
	background:  -ms-linear-gradient(top,  #58d000 0%,#477d1d 100%);
	background:  linear-gradient(top,  #58d000 0%,#477d1d 100%);
	
	-pie-background: linear-gradient(top, #58d000 0%, #477d1d 100%);
} 

.ct_buttonPink 
{
	background-color: #ff00ea;
	background: -moz-linear-gradient(top,  #ff00ea 0%, #af0577 100%);
	background:  -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ff00ea), color-stop(100%,#af0577));
	background:  -webkit-linear-gradient(top,  #ff00ea 0%,#af0577 100%);
	background:  -o-linear-gradient(top,  #ff00ea 0%,#af0577 100%);
	background:  -ms-linear-gradient(top,  #ff00ea 0%,#af0577 100%);
	background:  linear-gradient(top,  #ff00ea 0%,#af0577 100%);
	
	-pie-background: linear-gradient(top, #ff00ea 0%, #af0577 100%);
} 

.ct_buttonBlack 
{
	background-color: #000;
	background: -moz-linear-gradient(top,  #474747 0%, #000 100%);
	background:  -webkit-gradient(linear, left top, left bottom, color-stop(0%,#474747), color-stop(100%,#000));
	background:  -webkit-linear-gradient(top,  #474747 0%,#000 100%);
	background:  -o-linear-gradient(top,  #474747 0%,#000 100%);
	background:  -ms-linear-gradient(top,  #474747 0%,#000 100%);
	background:  linear-gradient(top,  #474747 0%,#000 100%);
	
	-pie-background: linear-gradient(top, #474747 0%, #000 100%);
} 

.ct_buttonWhite 
{
	color: #777 !important;
	background-color: #fff;
	background: -moz-linear-gradient(top,  #fff 0%, #bababa 100%);
	background:  -webkit-gradient(linear, left top, left bottom, color-stop(0%,#fff), color-stop(100%,#bababa));
	background:  -webkit-linear-gradient(top,  #fff 0%,#bababa 100%);
	background:  -o-linear-gradient(top,  #fff 0%,#bababa 100%);
	background:  -ms-linear-gradient(top,  #fff 0%,#bababa 100%);
	background:  linear-gradient(top,  #fff 0%,#bababa 100%);
	
	-pie-background: linear-gradient(top, #fff 0%, #bababa 100%);
} 

input[type='text']:hover, input[type='password']:hover, input[type='email']:hover, input[type='text']:focus, input[type='password']:focus, input[type='email']:focus,
select:focus, textarea:focus {
	-moz-box-shadow: 0px 0px 3px 0px #".$color.", 0px 0px 3px 0px #".$color.", 0px 0px 3px 0px #".$color.";
	-webkit-box-shadow: 0px 0px 3px 0px #".$color.", 0px 0px 3px 0px #".$color.", 0px 0px 3px 0px #".$color.";
	box-shadow: 0px 0px 3px 0px #".$color.", 0px 0px 3px 0px #".$color.", 0px 0px 3px 0px #".$color.";
}

ul.pagenav li a,
.ct_PaginationStart,
.ct_PaginationPrevious,
.ct_PaginationNext,
.ct_PaginationEnd,
.ct_PaginationPageActive a,

ul.menu li:hover a,
ul.menu li:hover .separator,
ul.menu li.active a,
ul.menu li.active .separator,

ul.menu li:hover ul li:hover a,
ul.menu li:hover ul li:hover .separator,
ul.menu li:hover ul li.active a,
ul.menu li:hover ul li.active .separator,

ul.menu li:hover ul li:hover ul li:hover a,
ul.menu li:hover ul li:hover ul li:hover .separator,
ul.menu li:hover ul li:hover ul li.active a,
ul.menu li:hover ul li:hover ul li.active .separator,

ul.menu li:hover ul li:hover ul li:hover ul li:hover a,
ul.menu li:hover ul li:hover ul li:hover ul li:hover .separator,
ul.menu li:hover ul li:hover ul li:hover ul li.active a,
ul.menu li:hover ul li:hover ul li:hover ul li.active .separator

{
    background: #".$lighterColor.";
	background: -moz-linear-gradient(top,  #".$lighterColor." 0%, #".$darkerColor." 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#".$lighterColor."), color-stop(100%,#".$darkerColor."));
	background: -webkit-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: -o-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: -ms-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#".$lighterColor."', endColorstr='#".$darkerColor."',GradientType=0 );
}

.ct_menu_vertical ul.menu li:hover a,
.ct_menu_vertical ul.menu li:hover .separator,
.ct_menu_vertical ul.menu li.active a,
.ct_menu_vertical ul.menu li.active .separator,

.ct_menu_vertical ul.menu li:hover ul li:hover a,
.ct_menu_vertical ul.menu li:hover ul li:hover .separator,
.ct_menu_vertical ul.menu li:hover ul li.active a,
.ct_menu_vertical ul.menu li:hover ul li.active .separator,

.ct_menu_vertical ul.menu li:hover ul li:hover ul li:hover a,
.ct_menu_vertical ul.menu li:hover ul li:hover ul li:hover .separator,
.ct_menu_vertical ul.menu li:hover ul li:hover ul li.active a,
.ct_menu_vertical ul.menu li:hover ul li:hover ul li.active .separator,

.ct_menu_vertical ul.menu li:hover ul li:hover ul li:hover ul li:hover a,
.ct_menu_vertical ul.menu li:hover ul li:hover ul li:hover ul li:hover .separator,
.ct_menu_vertical ul.menu li:hover ul li:hover ul li:hover ul li.active a,
.ct_menu_vertical ul.menu li:hover ul li:hover ul li:hover ul li.active .separator,

ul.menu li:hover ul li:hover a,
ul.menu li:hover ul li:hover .separator,
ul.menu li:hover ul li.active a,
ul.menu li:hover ul li.active .separator,

ul.menu li:hover ul li:hover ul li:hover a,
ul.menu li:hover ul li:hover ul li:hover .separator,
ul.menu li:hover ul li:hover ul li.active a,
ul.menu li:hover ul li:hover ul li.active .separator,

ul.menu li:hover ul li:hover ul li:hover ul li:hover a,
ul.menu li:hover ul li:hover ul li:hover ul li:hover .separator,
ul.menu li:hover ul li:hover ul li:hover ul li.active a,
ul.menu li:hover ul li:hover ul li:hover ul li.active .separator
{
	border-top:1px solid rgba(255, 255, 255, 0.5);
	border-bottom:1px solid rgba(0, 0, 0, 0.5);
}

#login-form.compact .button, #ct_headerLogin input.button {
	background: #".$lighterColor.";
	background: url(../images/bg_btn_login.png), -moz-linear-gradient(top,  #".$lighterColor." 0%, #".$darkerColor." 100%);
	background: url(../images/bg_btn_login.png), -webkit-gradient(linear, left top, left bottom, color-stop(0%,#".$lighterColor."), color-stop(100%,#".$darkerColor."));
	background: url(../images/bg_btn_login.png), -webkit-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: url(../images/bg_btn_login.png), -o-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: url(../images/bg_btn_login.png), -ms-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: url(../images/bg_btn_login.png), linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	
	-pie-background: url(".$templateUrl."/images/bg_btn_login.png) no-repeat, linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
}

.content_vote input.button {
	background: #".$lighterColor.";
	background: -moz-linear-gradient(top,  #".$lighterColor." 0%, #".$darkerColor." 100%);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#".$lighterColor."), color-stop(100%,#".$darkerColor."));
	background: -webkit-linear-gradient(top, #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: -o-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: -ms-linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%);
	background: linear-gradient(top,  #".$lighterColor." 0%,#".$darkerColor." 100%); 
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#".$lighterColor."', endColorstr='#".$darkerColor."',GradientType=0 );
	
    border-radius:3px 3px 3px 3px;
    box-shadow:0 1px 3px rgba(0, 0, 0, 0.3);
}


/* SET BASE COLOR STYLES -------------------------------------------------------------------------- */

");
	if($r_base == $g_base && $g_base == $b_base) {
		$colorType = "grey";		
	}
	
	if( ((($r_base + $g_base + $b_base) / 3)-$r_base) < ($g_base -2) || ((($r_base + $g_base + $b_base) / 3)-$r_base) > ($g_base +2) ) {
		$colorType = "grey";		
	}
	
	else {
		$colorType = "color";		
	}
	

	if($base_brightness > 200 && $content_color != "ffffff") {
		
		$menuTextShadow = "0px -1px 0px rgba(0, 0, 0, 0.4), 0px 1px 0px rgba(255, 255, 255, 0.4)";
		
		if($colorType == "grey") {
			$satVal = 0;
                        $satVal_1 = 0;
			$satVal_2 = 0; 
		}
                
                
                
		if ($colorType == "color") {
			if ($r_base > 240  || $g_base > 240  || $b_base > 240) {
				$satVal_1 = 0;
				$satVal_2 = 0; 
			}
			else {
				$satVal_1 = 0.5;
				$satVal_2 = 0.8;
			}		
		}

		$menuGradientLight = ct_hsvShade($content_color, 0.05, $satVal_1);
		$menuGradientDark = ct_hsvShade($content_color, -0.15, $satVal_1);
		$menuFont = ct_hsvShade($content_color, -0.3, $satVal_1);
		
		$darkboxGradientLight = ct_hsvShade($content_color, -0.03, $satVal_1);
		$darkboxGradientDark = ct_hsvShade($content_color, -0.15, $satVal_1);
		
		$lightboxGradientLight = ct_hsvShade($content_color, 0.15, $satVal_1);
		$lightboxGradientDark = ct_hsvShade($content_color, 0.025, $satVal_1);
		
		$inputBG = ct_hsvShade($content_color, 0.3, 0);
		
		$tabelHeaderBG = ct_hsvShade($content_color, -0.1, $satVal_2);
		$tabelRowEvenBG = ct_hsvShade($content_color, -0.05, $satVal_2);
		$tabelRowOddBG= ct_hsvShade($content_color, -0.025, $satVal_2);
		
	}
	
	if($base_brightness > 138 && $base_brightness < 200 ) {
		
		$menuTextShadow = "0px -1px 0px rgba(0, 0, 0, 0.2), 0px 1px 0px rgba(255, 255, 255, 0.2)";
		
		if($colorType == "grey") {
			$satVal_1 = 0;
			$satVal_2 = 0; 
			$satVal_3 = 0; 
		}
		if ($colorType == "color") {
			if ($r_base > 240  || $g_base > 240  || $b_base > 240) {
				$satVal_1 = 0;
				$satVal_2 = 0; 
				$satVal_3 = 0; 
			}
			else {
				$satVal_1 = 0;
				$satVal_2 = 0.1;
				$satVal_3 = 0.5;
			}		
		}
		
		$menuGradientLight = ct_hsvShade($content_color, 0.15, $satVal_1);
		$menuGradientDark = ct_hsvShade($content_color, -0.10, $satVal_1);
		$menuFont = ct_hsvShade($content_color, -0.25, 0);
		
		$darkboxGradientLight = ct_hsvShade($content_color, -0.025, $satVal_2);
		$darkboxGradientDark = ct_hsvShade($content_color, -0.125, $satVal_2);
		
		$lightboxGradientLight = ct_hsvShade($content_color, 0.20, $satVal_2);
		$lightboxGradientDark = ct_hsvShade($content_color, 0.020, $satVal_2);
		
		$inputBG = ct_hsvShade($content_color, 0.3, -0.3);
		
		$tabelHeaderBG = ct_hsvShade($content_color, -0.1, $satVal_3);
		$tabelRowEvenBG = ct_hsvShade($content_color, -0.05, $satVal_3);
		$tabelRowOddBG= ct_hsvShade($content_color, -0.025, $satVal_3);
	}
	
	if($base_brightness < 138) {
		
		$menuTextShadow = "0px -1px 0px rgba(0, 0, 0, 0.5)";
		
		if($colorType == "grey") {
			$satVal_1 = 0;
			$satVal_2 = 0;
			$satVal_3 = 0; 
		}
		if ($colorType == "color") {
			if ($r_base > 240  || $g_base > 240  || $b_base > 240) {
				$satVal_1 = 0;
				$satVal_2 = 0; 
				$satVal_3 = 0; 
			}
			else {
				$satVal_1 = 0;
				$satVal_2 = -0.3;
				$satVal_3 = 0;
			}		
		}
		
		$menuGradientLight = ct_hsvShade($content_color, 0.15, $satVal_1);
		$menuGradientDark = ct_hsvShade($content_color, -0.03, $satVal_1);
		$menuFont = ct_hsvShade($content_color, 0.41, $satVal_2);
		
		$darkboxGradientLight = ct_hsvShade($content_color, -0.02, $satVal_1);
		$darkboxGradientDark = ct_hsvShade($content_color, -0.06, $satVal_1);
		
		$lightboxGradientLight = ct_hsvShade($content_color, 0.1, $satVal_1);
		$lightboxGradientDark = ct_hsvShade($content_color, 0.03, $satVal_1);
		
		$inputBG = ct_hsvShade($content_color, 0.12, 0);
		
		$tabelHeaderBG = ct_hsvShade($content_color, 0.1, $satVal_3);
		$tabelRowEvenBG = ct_hsvShade($content_color, 0.05, $satVal_3);
		$tabelRowOddBG= ct_hsvShade($content_color, 0.025, $satVal_3);
	}
		
	if($content_color == "ffffff") {
		
		$menuTextShadow = "0px -1px 0px rgba(0, 0, 0, 0.4)";
		
		$menuGradientLight = "#f0f0f0";
		$menuGradientDark = "#dedede";
		$menuFont = "#8f8f8f";
		
		$darkboxGradientLight = '#f0f0f0';
		$darkboxGradientDark = '#dedede';
		
                $lightboxGradientLight = '#fefefe';
                $lightboxGradientDark  = '#efefef';
		
		$inputBG = "#ffffff";
		
		$tabelHeaderBG = "#dadada";
		$tabelRowEvenBG = "#ececec";
		$tabelRowOddBG= "#e6e6e6";
	}
		
	echo("
	
		ul.menu, 
		ul.menu ul,
		.moduletable_menu,
                .ct_popup_bg,
		#ct_headerWrapper_left #ct_headerSearch .moduletable,
		#ct_headerWrapper_right #ct_headerSearch .moduletable,
		#ct_headerWrapper_left #ct_headerLogin .moduletable,
		#ct_headerWrapper_right #ct_headerLogin .moduletable,
		#ct_sliderWrapper .moduletable,
		#ct_highlightsWrapper,
		#ct_mainContent,
                #ct_footerWrapper
		{
			background-image: url(../images/bg_content_dark_alphablending.jpg);
			
			-webkit-box-shadow: 0 3px 8px rgba(0, 0, 0, 0.75);
			-moz-box-shadow: 0 3px 8px rgba(0, 0, 0, 0.75); 
			box-shadow: 0 3px 8px rgba(0,0, 0, 0.75);
			-pie-box-shadow: 0 2px 0px rgba(0, 0, 0, 0.65);
		}
		
		.ct_left .moduletable_menu, 
		.ct_left ul.menu 
		{
			padding-left: 5px;
			
			-webkit-box-shadow: none;
			-moz-box-shadow: none; 
			box-shadow: none;
			-pie-box-shadow: none;
		}
		
		.ct_PaginationPageActive span {
			background: ".$menuGradientLight.";
			background: -moz-linear-gradient(top, ".$menuGradientLight." 0%, ".$menuGradientDark." 100%);
			background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, ".$menuGradientLight."), color-stop(100%, ".$menuGradientDark."));
			background: -webkit-linear-gradient(top, ".$menuGradientLight." 0%, ".$menuGradientDark." 100%);
			background: -o-linear-gradient(top, ".$menuGradientLight." 0%, ".$menuGradientDark." 100%);
			background: -ms-linear-gradient(top, ".$menuGradientLight." 0%, ".$menuGradientDark." 100%);
			background: linear-gradient(top, ".$menuGradientLight." 0%, ".$menuGradientDark." 100%);
		
			-pie-background: linear-gradient(top, ".$menuGradientLight." 0%, ".$menuGradientDark." 100%);
		}
		
		
		ul.menu  a, 
		ul.menu .separator, 
		
		ul.menu li:hover ul li a,
		ul.menu li:hover ul li .separator,
		ul.menu li.active ul li a,
		ul.menu li.active ul li .separator,
		
		ul.menu li:hover ul li:hover ul li a,
		ul.menu li:hover ul li:hover ul li .separator,
		ul.menu li:hover ul li.active ul li a,
		ul.menu li:hover ul li.active ul li .separator,
		
		ul.menu li:hover ul li:hover ul li:hover ul li a,
		ul.menu li:hover ul li:hover ul li:hover ul li .separator,
		ul.menu li:hover ul li:hover ul li.active ul li a,
		ul.menu li:hover ul li:hover ul li.active ul li .separator,
		
		ul.menu li:hover ul li:hover ul li:hover ul li:hover ul li a,
		ul.menu li:hover ul li:hover ul li:hover ul li:hover ul li .separator,
		ul.menu li:hover ul li:hover ul li:hover ul li.active ul li a,
		ul.menu li:hover ul li:hover ul li:hover ul li.active ul li .separator
		{
			color: ".$menuFont." !important;
			text-shadow: ".$menuTextShadow." !important;
		}

		.tip {
			background: ".$darkboxGradientLight.";
			background: url(../images/bg_tooltip.png), -moz-linear-gradient(top,  ".$darkboxGradientLight." 0%, ".$darkboxGradientDark." 100%);
			background: url(../images/bg_tooltip.png), -webkit-gradient(linear, left top, left bottom, color-stop(0%,".$darkboxGradientLight."), color-stop(100%,".$darkboxGradientDark."));
			background: url(../images/bg_tooltip.png), -webkit-linear-gradient(top,  ".$darkboxGradientLight." 0%,".$darkboxGradientDark." 100%);
			background: url(../images/bg_tooltip.png), -o-linear-gradient(top,  ".$darkboxGradientLight." 0%,".$darkboxGradientDark." 100%);
			background: url(../images/bg_tooltip.png), -ms-linear-gradient(top,  ".$darkboxGradientLight." 0%,".$darkboxGradientDark." 100%);
			background: url(../images/bg_tooltip.png), linear-gradient(top,  ".$darkboxGradientLight." 0%,".$darkboxGradientDark." 100%);
			
			-pie-background: url(".$templateUrl."/images/bg_tooltip.png) no-repeat, linear-gradient(top,  ".$darkboxGradientLight." 0%,".$darkboxGradientDark." 100%);
		}
		
		input[type='text'], input[type='password'], input[type='email'], select, textarea {
			color: #ffffff;
			background-color: ".$inputBG.";
		}
		
		span.highlight {
			background-color: ".ct_hsvShade($content_color, 0.25, 0)." !important;
		}
		
		
		table.category th {
			background-color: ".$tabelHeaderBG." 
		}
		
		table.category  tr.cat-list-row0 td {
			background-color: ".$tabelRowOddBG."
		}
		
		table.category  tr.cat-list-row1 td {
			background-color: ".$tabelRowEvenBG."
		}
");



/* CHOOSE THEME IN RELATION TO BASE COLOR -------------------------------------------------------------------------- */

if($base_brightness > 138 ) {
    //bright base color
	$themeColor = "dark";
	$fontColor = "454545";
	
	$fontColorBase = "#".$fontColor;
	$fontColorBright1 = ct_hsvShade($fontColor, 0.2, 0);
	$fontColorBright2 = ct_hsvShade($fontColor, 0.4, 0);
	$fontColorDark1 = ct_hsvShade($fontColor, -0.1, 0);
	$fontColorDark2 = ct_hsvShade($fontColor, -0.05, 0);
	
}else{
    //dark base color
	$themeColor = "light";
	$fontColor = "f2f2f2";
	
	$fontColorBase = "#".$fontColor;
	$fontColorBright1 = ct_hsvShade($fontColor, -0.2, 0);
	$fontColorBright2 = ct_hsvShade($fontColor, -0.3, 0);
	$fontColorDark1 = ct_hsvShade($fontColor, 0.05, 0);
	$fontColorDark2 = ct_hsvShade($fontColor, 0.1, 0);		
}



	

echo ('

* {
	color: '.$fontColorBase.';
}

.title a span {
	color: #'.$color.' !important;
}

label, legend {
	color: '.$fontColorBase.';
}

.moduletable_ct_linkList a {
	color: '.$fontColorBright1.';
}

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
	color: '.$fontColorBase.';
	border-top: 1px dotted '.$fontColorBright1.';
	border-bottom: 1px dotted '.$fontColorBright1.';
}

#ct_loginHelpers li a { 
	color: '.$fontColorDark2.';
}

#ct_headerSearch .search input, #ct_headerSearch .finder input {
	background-image: url(../images/bg_inputfield_search_'.$themeColor.'.png);
}

.ct_inlineLink {
	background-image: url(../images/icon_link_arrow_small_'.$themeColor.'.png);
}
.ct_inlineLink:hover {
	background-image: url(../images/icon_link_arrow_small_hover.png);
}


#ct_headerSearch .search input,
input[type="text"], 
input[type="password"],
input[type="email"], 
input[type="text"], 
input[type="password"], 
input[type="email"] {
	color: '.$fontColorBase.';
}

#ct_headerSearch .search input:focus,
input[type="text"]:hover, 
input[type="password"]:hover,
input[type="email"]:hover, 
input[type="text"]:focus, 
input[type="password"]:focus, 
input[type="email"]:focus,
select:focus, textarea:focus {
	color: '.$fontColorDark2.';
}

table.category th, 
table.category th a,
.categories-list span.item-title a,
.category .item-title a {
	color: '.$fontColorBase.';
}

#system-message dd.message ul,
#system-message dd.error ul,
#system-message dd.warning ul,
#system-message dd.notice ul,
.bfErrorMessage {
	background-color: #fff !important; 
}

.tip {
	-webkit-box-shadow: 2px 4px 5px 0px rgba(0, 0, 0, 0.5);
	-moz-box-shadow: 2px 4px 5px 0px rgba(0, 0, 0, 0.5);
	box-shadow: 2px 4px 5px 0px rgba(0, 0, 0, 0.5);
}

.tip-title {
	color: #'.$color.';
}

.tip-text {
	color: '.$fontColorBase.';
}

ul.latestnews li, ul.latestnews li:first-child {
	border-bottom: 1px dotted '.$fontColorBright1.';
}

.panel {
	border-top: 1px dotted '.$fontColorBright1.';
}

h1, h1 a { 
	border-bottom: 1px solid '.$fontColorBright2.';
}

ul.latestnews a {
	color: '.$fontColorBase.'; ;
}

.blog-featured .article-info dd, .blog-featured .article-info dd a {
	color: '.$fontColorBright1.';
}

.ct_breadcrumbs span, .ct_breadcrumbs a {
	color: '.$fontColorBright1.';
}

.showHere {
	color: '.$fontColorDark1.' !important;
}

a.readmore, p.readmore a {
	color: '.$fontColorDark1.';
}

.article-info dd, .article-info dd a {
	color: '.$fontColorBright1.';
}

.contact-address,
.contact-emailto,
.contact-telephone,
.contact-fax,
.contact-mobile,
.contact-webpage,
.contact-vcard {
	background-image: url(../images/icons_contactdetails_'.$themeColor.'.png);
}

.print-icon {
	background-image: url(../images/bg_icon_print_'.$themeColor.'.png);
}

.email-icon {
	background-image: url(../images/bg_icon_mail_'.$themeColor.'.png);
}

.edit-icon {
	background-image: url(../images/bg_icon_edit_'.$themeColor.'.png);
}

.cdajaxvote .ui-stars-star a {
	background-image: url(../images/voting-star_empty_'.$themeColor.'.png) !important;
}

.icon_searchresult_com_content {
	background-image: url(../images/icon_searchresult_com_content_'.$themeColor.'.png);
}
.icon_searchresult_com_category {
	background-image: url(../images/icon_searchresult_com_category_'.$themeColor.'.png);
}
.icon_searchresult_com_contact {
	background-image: url(../images/icon_searchresult_com_contact_'.$themeColor.'.png);
}

.cdajaxvote .ui-stars-cancel a {
	background-image: url(../images/voting-cancel_'.$themeColor.'.png);
}


ul.pagenav {
	border-top: 1px dotted '.$fontColorBright1.';
}

.phrases-box label, .only-box label, .display-limit {
	color: '.$fontColorBase.' !important;
}

blockquote {
	color: '.$fontColorBright1.';
}

');

?>
