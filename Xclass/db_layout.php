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
 * Xclass of PageLayoutController to add the additional hook
 *
 * @package TYPO3
 * @subpackage tx_autoswitchtolistview
 */
class ux_SC_db_layout extends SC_db_layout {

	/**
	 * Rendering all other listings than QuickEdit
	 *
	 * @return	void
	 */
	function renderListContent() {
			// Initialize list object (see "class.db_layout.inc"):
		/** @var $dblist tx_cms_layout */
		$dblist = t3lib_div::makeInstance('tx_cms_layout');
		$dblist->backPath = $GLOBALS['BACK_PATH'];
		$dblist->thumbs = $this->imagemode;
		$dblist->no_noWrap = 1;
		$dblist->descrTable = $this->descrTable;

		$this->pointer = t3lib_utility_Math::forceIntegerInRange($this->pointer,0,100000);
		$dblist->script = 'db_layout.php';
		$dblist->showIcon = 0;
		$dblist->setLMargin=0;
		$dblist->doEdit = $this->EDIT_CONTENT;
		$dblist->ext_CALC_PERMS = $this->CALC_PERMS;

		$dblist->agePrefixes = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears');
		$dblist->id = $this->id;
		$dblist->nextThree = t3lib_utility_Math::forceIntegerInRange($this->modTSconfig['properties']['editFieldsAtATime'],0,10);
		$dblist->option_showBigButtons = ($this->modTSconfig['properties']['disableBigButtons'] === '0');
		$dblist->option_newWizard = $this->modTSconfig['properties']['disableNewContentElementWizard'] ? 0 : 1;
		$dblist->defLangBinding = $this->modTSconfig['properties']['defLangBinding'] ? 1 : 0;
		if (!$dblist->nextThree)	$dblist->nextThree = 1;

		$dblist->externalTables = $this->externalTables;

			// Create menu for selecting a table to jump to (this is, if more than just pages/tt_content elements are found on the page!)
		$h_menu = $dblist->getTableMenu($this->id);

			// Initialize other variables:
		$h_func='';
		$tableOutput=array();
		$tableJSOutput=array();
		$CMcounter = 0;

			// Traverse the list of table names which has records on this page (that array is populated by the $dblist object during the function getTableMenu()):
		foreach ($dblist->activeTables as $table => $value) {

				// Load full table definitions:
			t3lib_div::loadTCA($table);

			if (!isset($dblist->externalTables[$table]))	{
				$q_count = $this->getNumberOfHiddenElements();
				$h_func_b = t3lib_BEfunc::getFuncCheck(
					$this->id,
					'SET[tt_content_showHidden]',
					$this->MOD_SETTINGS['tt_content_showHidden'],
					'db_layout.php',
					'',
					'id="checkTt_content_showHidden"'
				) . '<label for="checkTt_content_showHidden">' .
				(!$q_count ? $GLOBALS['TBE_TEMPLATE']->dfw($GLOBALS['LANG']->getLL('hiddenCE')) : $GLOBALS['LANG']->getLL('hiddenCE') . ' (' . $q_count . ')') . '</label>';

				$dblist->tt_contentConfig['showCommands'] = 1;	// Boolean: Display up/down arrows and edit icons for tt_content records
				$dblist->tt_contentConfig['showInfo'] = 1;		// Boolean: Display info-marks or not
				$dblist->tt_contentConfig['single'] = 0; 		// Boolean: If set, the content of column(s) $this->tt_contentConfig['showSingleCol'] is shown in the total width of the page

				if ($this->MOD_SETTINGS['function'] == 4) {
						// grid view
					$dblist->tt_contentConfig['showAsGrid'] = 1;
				}

					// Setting up the tt_content columns to show:
				if (is_array($GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'])) {
					$colList = array();
					$tcaItems = t3lib_div::callUserFunction( 'EXT:cms/classes/class.tx_cms_backendlayout.php:tx_cms_BackendLayout->getColPosListItemsParsed' , $this->id, $this );
					foreach($tcaItems as $temp)	{
						$colList[] = $temp[1];
					}
				} else {	// ... should be impossible that colPos has no array. But this is the fallback should it make any sense:
					$colList = array('1','0','2','3');
				}
				if (strcmp($this->colPosList,''))	{
					$colList = array_intersect(t3lib_div::intExplode(',',$this->colPosList),$colList);
				}

					// If only one column found, display the single-column view.
				if (count($colList) === 1 && !$this->MOD_SETTINGS['function'] === 4) {
					$dblist->tt_contentConfig['single'] = 1;	// Boolean: If set, the content of column(s) $this->tt_contentConfig['showSingleCol'] is shown in the total width of the page
					$dblist->tt_contentConfig['showSingleCol'] = current($colList);	// The column(s) to show if single mode (under each other)
				}
				$dblist->tt_contentConfig['cols'] = implode(',',$colList);		// The order of the rows: Default is left(1), Normal(0), right(2), margin(3)
				$dblist->tt_contentConfig['showHidden'] = $this->MOD_SETTINGS['tt_content_showHidden'];
				$dblist->tt_contentConfig['sys_language_uid'] = intval($this->current_sys_language);

					// If the function menu is set to "Language":
				if ($this->MOD_SETTINGS['function']==2)	{
					$dblist->tt_contentConfig['single'] = 0;
					$dblist->tt_contentConfig['languageMode'] = 1;
					$dblist->tt_contentConfig['languageCols'] = $this->MOD_MENU['language'];
					$dblist->tt_contentConfig['languageColsPointer'] = $this->current_sys_language;
				}
			} else {
				if (isset($this->MOD_SETTINGS) && isset($this->MOD_MENU)) {
					$h_func = t3lib_BEfunc::getFuncMenu($this->id, 'SET[' . $table . ']', $this->MOD_SETTINGS[$table], $this->MOD_MENU[$table], 'db_layout.php', '');
				} else {
				$h_func = '';
			}
 			}

				// Start the dblist object:
			$dblist->itemsLimitSingleTable = 1000;
			$dblist->start($this->id,$table,$this->pointer,$this->search_field,$this->search_levels,$this->showLimit);
			$dblist->counter = $CMcounter;
			$dblist->ext_function = $this->MOD_SETTINGS['function'];

				// Render versioning selector:
			$dblist->HTMLcode.= $this->doc->getVersionSelector($this->id);

				// Generate the list of elements here:
			$dblist->generateList();

				// Adding the list content to the tableOutput variable:
			$tableOutput[$table]=
							($h_func?$h_func.'<br /><img src="clear.gif" width="1" height="4" alt="" /><br />':'').
							$dblist->HTMLcode.
							($h_func_b?'<img src="clear.gif" width="1" height="10" alt="" /><br />'.$h_func_b:'');

				// ... and any accumulated JavaScript goes the same way!
			$tableJSOutput[$table] = $dblist->JScode;

				// Increase global counter:
			$CMcounter+= $dblist->counter;

				// Reset variables after operation:
			$dblist->HTMLcode='';
			$dblist->JScode='';
			$h_func = '';
			$h_func_b = '';
		}	// END: traverse tables


			// For Context Sensitive Menus:
		$this->doc->getContextMenuCode();

		$content .= $this->doc->header($this->pageinfo['title']);

			// Now, create listing based on which element is selected in the function menu:
		if ($this->MOD_SETTINGS['function']==3) {

				// Making page info:
			$content .= $this->doc->section($GLOBALS['LANG']->getLL('pageInformation'), $dblist->getPageInfoBox($this->pageinfo, $this->CALC_PERMS&2), 0, 1);
		} else {

				// Add the content for each table we have rendered (traversing $tableOutput variable)
			foreach ($tableOutput as $table => $output)	{
				$content .= $this->doc->section('', $output, TRUE, TRUE, 0, TRUE);
				$content .= $this->doc->spacer(15);
				$content .= $this->doc->sectionEnd();
			}

				// Making search form:
			if (!$this->modTSconfig['properties']['disableSearchBox'] && count($tableOutput))	{
				$sectionTitle = t3lib_BEfunc::wrapInHelp('xMOD_csh_corebe', 'list_searchbox', $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.search', TRUE));
				$content .= $this->doc->section(
					$sectionTitle,
					$dblist->getSearchBox(0),
					FALSE, TRUE, FALSE, TRUE
				);
			}

				// Making display of Sys-notes (from extension "sys_note")
			$dblist->id=$this->id;
			$sysNotes = $dblist->showSysNotesForPage();
			if ($sysNotes)	{
				$content.=$this->doc->spacer(10);
				$content.=$this->doc->section($GLOBALS['LANG']->getLL('internalNotes'), $sysNotes, 0, 1);
			}

				// Add spacer in bottom of page:
			$content.=$this->doc->spacer(10);
		}

				// Additional footer content
		$footerContentHook = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawFooterHook'];
		if (is_array($footerContentHook)) {
			foreach ($footerContentHook as $hook) {
				$params = array();
				$content .= t3lib_div::callUserFunction($hook, $params, $this);
			}
		}

			// Ending page:
		$content.=$this->doc->spacer(10);

		return $content;
	}
}