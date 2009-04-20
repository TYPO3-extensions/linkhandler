<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2008, Daniel P�tzinger <daniel.poetzinger@aoemedia.de>
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

if (!defined ('TYPO3_MODE'))
	die ('Access denied.');

require_once t3lib_extMgm::extPath('linkhandler') . 'classes/record/class.tx_linkhandler_recordsTree.php';
require_once t3lib_extMgm::extPath('linkhandler') . 'classes/record/class.TBE_browser_recordListRTE.php';
require_once t3lib_extMgm::extPath('linkhandler') . 'classes/interface.tx_linkhandler_tabHandler.php';

/**
 * hook to adjust linkwizard (linkbrowser)
 *
 * @author	Daniel Poetzinger (AOE media GmbH)
 * @version $Id: $
 * @date 08.04.2009 - 15:06:25
 * @package TYPO3
 * @subpackage tx_linkhandler
 * @access public
 */
class tx_linkhandler_recordTab implements tx_linkhandler_tabHandler {

	/**
	 * @var boolean
	 */
	protected $isRTE;

	/**
	 * @var tx_rtehtmlarea_browse_links
	 */
	protected $browseLinksObj;

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * Initialize the class
	 *
	 * @param tx_rtehtmlarea_browse_links $browseLinksObj
	 * @param string $addPassOnParams
	 * @param array $configuration
	 * @param string $currentLinkValue
	 * @param boolean $isRTE
	 * @access pubic
	 * @return void
	 */
	public function __construct($browseLinksObj, $addPassOnParams, $configuration, $currentLinkValue, $isRTE, $currentPid) {
		$this->browseLinksObj=$browseLinksObj;

		// first step to refactoring (no dependenciy to $browseLinksObj), make the required methodcalls known in membervariables
		$this->isRTE=$isRTE;
		$this->expandPage=$browseLinksObj->expandPage;
		$this->configuration=$configuration;
		$this->pointer=$browseLinksObj->pointer;

		$P = t3lib_div::_GP('P');
		if (is_array($P)) {
			$environment = t3lib_div::implodeArrayForUrl('P', $P);
		}

		$this->addPassOnParams=$addPassOnParams . $environment;
	}

	/**
	 * interface function. should return the correct info array that is required for the link wizard.
	 * It should detect if the current value is a link where this tabHandler should be responsible.
	 * else it should return a emty array
	 *
	 * @param string $href
	 * @param array $tabsConfig
	 * @access public
	 * @static
	 * @return array
	 */
	static public function getLinkBrowserInfoArray($href, $tabsConfig) {
		$info = array();

		if (strtolower(substr($href, 0, 7)) == 'record:') {
			$parts = explode(":", $href);

				// check the linkhandler TSConfig and find out  which config is responsible for the current table:
			foreach ($tabsConfig as $key => $tabConfig) {
				if ($parts[1] == $tabConfig['listTables']) {
					$info['act'] = $key;
				}
			}

			$info['recordTable'] = $parts[1];
			$info['recordUid']   = $parts[2];
		}

		return $info;
	}

	/**
	 * Build the content of an tab
	 *
	 * @access public
	 * @uses tx_rtehtmlarea_browse_links
	 * @return	string a tab for the selected link action
	 */
	public function getTabContent() {
		global $LANG;
		$content = '';

		if ($this->isRTE) {
			$content .= $this->browseLinksObj->addAttributesForm();
		}

		$pagetree = t3lib_div::makeInstance('tx_linkhandler_recordsTree');
		$pagetree->browselistObj = $this->browseLinksObj;
		$tree = $pagetree->getBrowsableTree();
		$cElements = $this->expandPageRecords();
		$content.= '
		<!--
			Wrapper table for page tree / record list:
		-->
				<table border="0" cellpadding="0" cellspacing="0" id="typo3-linkPages">
					<tr>
						<td class="c-wCell" valign="top">'.$this->browseLinksObj->barheader($LANG->getLL('pageTree').':').$tree.'</td>
						<td class="c-wCell" valign="top">'.$cElements.'</td>
					</tr>
				</table>
				';

		return $content;
	}

	/******************************************************************
	 *
	 * Record listing
	 *
	 ******************************************************************/
	/**
	 * For RTE: This displays all content elements on a page and lets you create a link to the element.
	 *
	 * @access protected
	 * @return	string HTML output. Returns content only if the ->expandPage value is set (pointing to a page uid to show tt_content records from ...)
	 */
	protected function expandPageRecords()	{
		global $TCA,$BE_USER, $BACK_PATH;
		$out = '';

		if ( $this->expandPage >= 0 && t3lib_div::testInt($this->expandPage) && $BE_USER->isInWebMount($this->expandPage) )	{
			$tables = '*';

			if (isset($this->configuration['listTables'])) {
				$tables = $this->configuration['listTables'];
			}
				// Set array with table names to list:
			if (! strcmp(trim($tables), '*'))	{
				$tablesArr = array_keys($TCA);
			} else {
				$tablesArr = t3lib_div::trimExplode(',',$tables,1);
			}
			reset($tablesArr);

				// Headline for selecting records:
			$out .= $this->browseLinksObj->barheader($GLOBALS['LANG']->getLL('selectRecords') . ':');

				// Create the header, showing the current page for which the listing is. Includes link to the page itself, if pages are amount allowed tables.
			$titleLen = intval($GLOBALS['BE_USER']->uc['titleLen']);
			$mainPageRec = t3lib_BEfunc::getRecordWSOL('pages',$this->expandPage);
			$ATag ='';
			$ATag_e = '';
			$ATag2 = '';
			if (in_array('pages', $tablesArr))	{
				$ficon    = t3lib_iconWorks::getIcon('pages', $mainPageRec);
				$ATag     = "<a href=\"#\" onclick=\"return insertElement('pages', '" . $mainPageRec['uid'] . "', 'db', " . t3lib_div::quoteJSvalue($mainPageRec['title']) . ", '', '', '".$ficon."', '',1);\">";
				$ATag2    = "<a href=\"#\" onclick=\"return insertElement('pages', '" . $mainPageRec['uid'] . "', 'db', " . t3lib_div::quoteJSvalue($mainPageRec['title']) . ", '', '', '".$ficon."', '',0);\">";
				$ATag_alt = substr($ATag, 0, -4) . ", '', 1);\">";
				$ATag_e   = '</a>';
			}
			$picon=t3lib_iconWorks::getIconImage('pages',$mainPageRec,$BACK_PATH,'');
			$pBicon=$ATag2?'<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/plusbullet2.gif','width="18" height="16"').' alt="" />':'';
			$pText=htmlspecialchars(t3lib_div::fixed_lgd_cs($mainPageRec['title'],$titleLen));
			$out.=$picon.$ATag2.$pBicon.$ATag_e.$ATag.$pText.$ATag_e.'<br />';

				// Initialize the record listing:
			$id = $this->expandPage;
			$pointer = t3lib_div::intInRange($this->pointer,0,100000);
			$perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(1);
			$pageinfo = t3lib_BEfunc::readPageAccess($id,$perms_clause);

				// Generate the record list:
				// unfortunatly we have to set weird dependencies.
			$dblist = t3lib_div::makeInstance('TBE_browser_recordListRTE');
			$dblist->setAddPassOnParams($this->addPassOnParams);
			$dblist->browselistObj=$this->browseLinksObj;
			$dblist->this->pObjScript=$this->browseLinksObj->this->pObjScript;
			$dblist->backPath = $GLOBALS['BACK_PATH'];
			$dblist->thumbs = 0;
			$dblist->calcPerms = $GLOBALS['BE_USER']->calcPerms($pageinfo);
			$dblist->noControlPanels=1;
			$dblist->clickMenuEnabled=0;
			$dblist->tableList=implode(',',$tablesArr);

			$dblist->start($id,t3lib_div::_GP('table'),$pointer,
				t3lib_div::_GP('search_field'),
				t3lib_div::_GP('search_levels'),
				t3lib_div::_GP('showLimit')
			);

			$dblist->setDispFields();
			$dblist->generateList();
			$dblist->writeBottom();

				//	Add the HTML for the record list to output variable:
			$out.=$dblist->HTMLcode;
			$out.=$dblist->getSearchBox();
		}

			// Return accumulated content:
		return $out;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/linkhandler/classes/class.tx_linkhandler_recordTab.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/linkhandler/classes/class.tx_linkhandler_recordTab.php']);
}

?>