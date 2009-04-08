<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Daniel P�tzinger 
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/




interface tx_linkhandler_tabHandler {
	/**
	 * constructur for the tabHandler. Normally used to sets some internal vars
	 *
	 * @param browse_links $browseLinksObj
	 * @param string $addPassOnParams
	 * @param array $configuration
	 * @param string $currentLinkValue
	 * @param boolean $isRTE
	 */
	public function __construct($browseLinksObj,$addPassOnParams,$configuration,$currentLinkValue,$isRTE) ;
	
	/**
	 * should return the correct info array that is required for the link wizard.
	 * It should detect if the current value is a link where this tabHandler should be responsible.
	 * else it should return a emty array
	 *
	 * @param string $href
	 * @param array $tabsConfig
	 * @return array
	 */
	static public function getLinkBrowserInfoArray($href,$tabsConfig);
	
	/**
	 * returns a new tab for the browse links wizard
	 *
	 * @param	string		current link selector action
	 * @return	string		a tab for the selected link action
	 */
	function getTabContent();

   
}


?>