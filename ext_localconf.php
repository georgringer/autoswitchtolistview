<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

 $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawFooterHook'][$_EXTKEY] =
	 t3lib_extMgm::extPath($_EXTKEY) . 'Hooks/PageLayoutController.php:Tx_Autoswitchtolistview_Hooks_PageLayoutController->render';

?>
