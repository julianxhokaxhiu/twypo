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

class TwigContentObjectRenderer {

	const CONTENT_OBJECT_NAME = 'TWIGTEMPLATE';

	private $cObj = null;

	private $templatesPath = '';

	private $templateData = array();

	private $twigUserConf = array();

	private $cachePath = 'typo3temp/Cache/Twig';

	private $tplEngine = null;

	public function cObjGetSingleExt( $name, $conf, $TSkey, $objR ) {
		require_once $this->_getPath( 'Vendor/Twig/lib/Twig/Autoloader.php' );
        \Twig_Autoloader::register();

		$ret = '';

		// Prepare our global container
		$GLOBALS['TWYPO'] = array();

		// Read the configuration
		$this->cObj = $objR;
		$this->templatesPath = GeneralUtility::getFileAbsFileName( $conf['path'] );
		$this->cachePath = GeneralUtility::getFileAbsFileName( $this->cachePath );
		$this->twigUserConf = $conf['twigInitConf.'];

		// Initialize the template engine
		$this->initTwig();

		// Assign data
		$this->assignData($conf);

		// Render the content
		$ret = $this->render();

		// Return it!
		return $ret;
	}

	// Internal Only
	private function initTwig() {

		$baseConfig = array_merge( array(
			'debug' => true,
			'cache' => $this->cachePath
		), $this->twigUserConf );

		$loader = new \Twig_Loader_Filesystem($this->templatesPath);
		$twig = new \Twig_Environment($loader, $baseConfig);

		if ( $baseConfig['debug'] )
			$twig->addExtension(new \Twig_Extension_Debug());

		$this->tplEngine = $twig;
	}

	private function assignData($conf) {
		global $TWYPO;

		$ret = array();

        // Render TypoScript objects
		foreach( $conf['data.'] as $key => $value ) {
			if ( !(substr( $key, -1, 1 ) == '.') ) {
				$ret[$key] = $this->cObj->cObjGetSingle( $conf['data.'][$key], $conf['data.'][$key . '.'] );
			}
		}

		// Save the data for later use
		$this->templateData = array_merge( $ret, $TWYPO );
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