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

namespace JX\Twypo\Xclass\Menu;

class TextMenuContentObject extends \TYPO3\CMS\Frontend\ContentObject\Menu\TextMenuContentObject {

	public function writeMenu() {
		global $TWYPO;

		if (is_array($this->result) && count($this->result)) {
			// Create new \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer for our use
			$this->WMcObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
			$this->WMresult = '';
			$this->INPfixMD5 = substr(md5(microtime() . 'tmenu'), 0, 4);
			$this->WMmenuItems = count($this->result);
			$this->WMsubmenuObjSuffixes = $this->tmpl->splitConfArray(array('sOSuffix' => $this->mconf['submenuObjSuffixes']), $this->WMmenuItems);
			$this->extProc_init();
			foreach ($this->result as $key => $val) {
				$this->WMcObj->start($this->menuArr[$key], 'pages');
				$this->extProc_beforeLinking($key);
				$TWYPO->scrapeData( 'MENU', array(
					'data' => $this->WMcObj->data,
					'linkData' => $this->link($key, $this->I['val']['altTarget'], $this->mconf['forceTypeValue'])
				));
				$this->subMenu($this->menuArr[$key]['uid'], $this->WMsubmenuObjSuffixes[$key]['sOSuffix']);
			}

			return $this->extProc_finish();
		}
	}

}