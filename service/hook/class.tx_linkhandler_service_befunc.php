<?php
/***************************************************************
 *  Copyright notice
 *
 *  Copyright (c) 2016, AOE GmbH <dev@aoe.com>
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
 * @author  Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class tx_linkhandler_service_befunc {

	/**
	 * Restore link handler generated preview link on save-n-preview event.
	 *
	 * The link is overwritten by the workspace module.
	 *
	 * @param  integer $pageUid
	 * @param  string  $backPath
	 * @param  array   $rootLine
	 * @param  string  $anchorSection
	 * @param  string  $viewScript
	 * @param  string  $additionalGetVars
	 * @param  boolean $switchFocus
	 */
	public function preProcess($pageUid, $backPath, $rootLine, $anchorSection, &$viewScript, $additionalGetVars, $switchFocus) {
		if ($GLOBALS['BE_USER']->workspace != 0) {
			$additionalGetVars = t3lib_div::explodeUrl2Array($additionalGetVars);

			if (isset($additionalGetVars['eID']) && ($additionalGetVars['eID'] === 'linkhandlerPreview')) {
				$viewUrl = t3lib_div::_POST('viewUrl');

				if (strlen($viewUrl)) {
					$viewScript = $viewUrl;
				}
			}
		}
	}
}
