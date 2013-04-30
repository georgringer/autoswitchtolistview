<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Georg Ringer <typo3@ringerge.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Hook to redirect to list view if page module is selected and list module is available
 *
 * @package TYPO3
 * @subpackage tx_autoswitchtolistview
 */
class Tx_Autoswitchtolistview_Hooks_PageLayoutController {

	/**
	 * @param array $configuration
	 * @param $parentObject
	 * @return string
	 */
	public function render(array $configuration, $parentObject) {
		$out = '';

		// If doktype is type sysfolder
		if ($parentObject->pageinfo['doktype'] == t3lib_pageSelect::DOKTYPE_SYSFOLDER) {
			$moduleLoader = t3lib_div::makeInstance('t3lib_loadModules');
			$moduleLoader->load($GLOBALS['TBE_MODULES']);
			$modules = $moduleLoader->modules;

			// If access to the list module
			if (is_array($modules['web']['sub']['list'])) {

				$tsconfig = t3lib_BEfunc::getPagesTSconfig($parentObject->id);

				if (!(isset($tsconfig['autoswitchtolistview.']) && isset($tsconfig['autoswitchtolistview.']['disable']) && $tsconfig['autoswitchtolistview.']['disable'] == 1)) {
					$out .= '<script type="text/javascript">top.goToModule(\'web_list\',1);</script>';
				}
			}
		}
		return $out;
	}

}

?>