<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Xclass for < 6.0
$version = TYPO3_version;
$firstNumber = (int)$version{0};
if ($firstNumber < 6) {
	$GLOBALS['TYPO3_CONF_VARS']['BE']['XCLASS']['ext/cms/layout/db_layout.php'] =
		t3lib_extMgm::extPath($_EXTKEY) . 'Xclass/db_layout.php';
}
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawFooterHook'][$_EXTKEY] =
	t3lib_extMgm::extPath($_EXTKEY) . 'Hooks/PageLayoutController.php:Tx_Autoswitchtolistview_Hooks_PageLayoutController->render';


?>
