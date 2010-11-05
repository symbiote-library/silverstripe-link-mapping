<?php
/**
 * @package silverstripe-linkmapping
 */

Director::addRules(2, array(
	'$URLSegment//$Action/$ID/$OtherID' => 'LinkMappingFrontController'
));