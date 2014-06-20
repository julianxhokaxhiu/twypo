<?php /*
	The MIT License (MIT)

	Copyright (c) 2014 Julian Xhokaxhiu

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
*/

namespace JX\Twypo;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TwigPageObjectRenderer {

	const CONTENT_OBJECT_NAME = 'TWIGTEMPLATE';

	private $cObj = null;

	private $templatesPath = '';

	private $templateData = array();

	private $twigUserConf = array();

	private $tplEngine = null;

	public function cObjGetSingleExt( $name, $conf, $TSkey, $parent ) {
		require_once $this->_getPath( 'Vendor/Twig/lib/Twig/Autoloader.php' );
        \Twig_Autoloader::register();

		$ret = '';

		// Prepare our global container
		$GLOBALS['TWYPO'] = $this;

		// Read the configuration
		$this->cObj = $parent;
		$this->templatesPath = GeneralUtility::getFileAbsFileName( $conf['path'] );
		$this->cachePath = GeneralUtility::getFileAbsFileName( $this->cachePath );
		$this->twigUserConf = $conf['twigInitConf.'];

		// Initialize the template engine
		$this->initTwig();

		// Assign data
		$this->renderData($conf);

		// Render the content
		$ret = $this->render();

		// Return it!
		return $ret;
	}

	// Public API to be called by Xclass components
	public function scrapeData( $type = '', $params = array() ) {
        $key = '';
        $ret = array();

        switch ( $type ) {
        	case 'MENU': {
        		$key = 'menu';
        		$ret = $this->get('menu');
        		$data = $params['data'];
        		$linkDefinition = $params['linkData'];

        		// Prepare the array
				$item = array(
					'title' => $data['title'],
					'href' => $this->templateData['baseUrl'] . $linkDefinition['HREF'],
					'target' => $linkDefinition['TARGET']
				);

				if ( array_key_exists($data['pid'], $ret) ) {
					// It's a children menu
					if ( !isset( $ret[ $data['pid'] ]['submenu'] ) ) $ret[ $data['pid'] ]['submenu'] = array();
					array_push( $ret[ $data['pid'] ]['submenu'], $item );
				} else {
					// It's a global menu
					$ret[ $data['uid'] ] = $item;
				}

        		break;
        	}
        	case 'CONTENT': {
        		$key = 'page';
        		$data = $params['data'];

        		$ret = array(
					'title' => $data['title'],
					'subtitle' => $data['subtitle'],
					'url' => $this->templateData['baseUrl'] . $this->templateData['currentPageUrl'],
					'meta' => array(
						'keywords' => $data['keywords'],
						'description' => $data['description'],
						'abstract' => $data['abstract']
					),
					'content' => array()
				);
        		break;
        	}
        	default: break;
        }

        $this->assign( $key, $ret );
    }

    // Public API to get parsed data from TypoScript configuration
    public function get($key) {
    	return $this->templateData[$key];
    }

	// Public API to assign template data from any extension
	public function assign($key, $value) {
		$this->templateData[$key] = $value;
	}

	// Public API to query the DB and get the result back
	// @ credits: https://github.com/dkd/T3CON12DE---TYPO3-meets-Sencha-Touch---Example-Code/blob/master/typo3/json_content/Classes/Utility/JsonContent.php
	// Modified to accomplish the goal of the project
	public function renderConf($conf) {
		$resultRecords = array();

		$originalRec = $GLOBALS['TSFE']->currentRecord;
		if ($originalRec) { // If the currentRecord is set, we register, that this record has invoked this function. It's should not be allowed to do this again then!!
			$GLOBALS['TSFE']->recordRegister[$originalRec]++;
		}

		$conf['table'] = isset($conf['table.'])
		? trim($this->cObj->stdWrap($conf['table'], $conf['table.']))
		: trim($conf['table']);
		$tablePrefix = GeneralUtility::trimExplode('_', $conf['table'], TRUE);
		if (GeneralUtility::inList('pages,tt,fe,tx,ttx,user,static', $tablePrefix[0])) {

			$again = FALSE;

			do {
				$res = $this->cObj->exec_getQuery($conf['table'], $conf['select.']);
				if (!$GLOBALS['TYPO3_DB']->sql_error()) {
					$this->cObj->currentRecordTotal = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
					/* @var $cObj tslib_cObj */
					$cObj = GeneralUtility::makeInstance('tslib_cObj');
					$cObj->setParent($this->cObj->data, $this->cObj->currentRecord);
					$this->cObj->currentRecordNumber = 0;
					$cobjValue = '';
					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

						// Versioning preview:
						$GLOBALS['TSFE']->sys_page->versionOL($conf['table'], $row, TRUE);

						// Language overlay:
						if (is_array($row) && $GLOBALS['TSFE']->sys_language_contentOL) {
							if ($conf['table'] == 'pages') {
								$row = $GLOBALS['TSFE']->sys_page->getPageOverlay($row);
							} else {
								$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay($conf['table'], $row, $GLOBALS['TSFE']->sys_language_content, $GLOBALS['TSFE']->sys_language_contentOL);
							}
						}

						if (is_array($row)) { // Might be unset in the sys_language_contentOL
							// Call hook for possible manipulation of database row for cObj->data

							if (!$GLOBALS['TSFE']->recordRegister[$conf['table'] . ':' . $row['uid']]) {
								$this->cObj->currentRecordNumber++;
								$cObj->parentRecordNumber = $this->cObj->currentRecordNumber;
								$GLOBALS['TSFE']->currentRecord = $conf['table'] . ':' . $row['uid'];
								$this->cObj->lastChanged($row['tstamp']);
								$cObj->start($row, $conf['table']);
								if (isset($conf['fieldRendering.'])) {
									foreach ($row as $field => &$value){
										if (isset($conf['fieldRendering.'][$field . '.']) && !empty($value)) {
											$value = $cObj->stdWrap($value, $conf['fieldRendering.'][$field . '.']);
										}
									}
								}

								$resultRecords[] = $row;
							}
						}
					}
					$GLOBALS['TYPO3_DB']->sql_free_result($res);
				}
			} while ($again);
		}

		$GLOBALS['TSFE']->currentRecord = $originalRec; // Restore

		return $resultRecords;
	}

	// Internal Only
	private function initTwig() {

		$baseConfig = array_merge( array(
			'debug' => true
		), $this->twigUserConf );

		$loader = new \Twig_Loader_Filesystem($this->templatesPath);
		$twig = new \Twig_Environment($loader, $baseConfig);

		if ( $baseConfig['debug'] )
			$twig->addExtension(new \Twig_Extension_Debug());

		$this->tplEngine = $twig;
	}

	private function renderData($conf) {
        // Render TypoScript objects
		foreach( $conf['data.'] as $key => $value ) {
			if ( !(substr( $key, -1, 1 ) == '.') ) {
				$this->templateData[$key] = $this->cObj->cObjGetSingle( $conf['data.'][$key], $conf['data.'][$key . '.'] );
			}
		}
	}

	private function render() {
		return $this->tplEngine->render( 'index.twig', array(
			'app' => $this->templateData
		));
	}

	private function _getPath($path) {
		return ExtensionManagementUtility::extPath('twypo') . $path;
	}
}