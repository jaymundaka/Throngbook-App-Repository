<?php

$model		= CFactory::getModel( 'user' );
$members		= $model->getPopularMember( 10 );
$html    = '';

//Get Template Page
$tmpl   =	new CTemplate();
$html   =	$tmpl	->set( 'members'    , $members )
		->fetch( 'activity.members.popular' );
// OR, we can just display it here directly
echo $html;