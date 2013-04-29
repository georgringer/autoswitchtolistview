<?php

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