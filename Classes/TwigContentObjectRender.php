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

class TwigContentObjectRender {

	const CONTENT_OBJECT_NAME = 'TWIG_CONTENT';

	private $cObj = null;

	public function cObjGetSingleExt( $name, $conf, $TSkey, $parent ) {
		global $TSFE, $TWYPO;

		$this->cObj = $parent;

		// Get current page info
		$pageData = $TSFE->page;
		$page = array(
			'title' => $pageData['title'],
			'subtitle' => $pageData['subtitle'],
			'url' => $TWYPO->get('baseUrl') . $TWYPO->get('currentPageUrl'),
			'meta' => array(
				'keywords' => $pageData['keywords'],
				'description' => $pageData['description'],
				'abstract' => $pageData['abstract']
			)
		);
		$TWYPO->assign('page', $page);

		// Get current page layout
		$TWYPO->assignInternal('pageLayout', $pageData['layout'] );

		// Get current page items and push to the array
		$colMapping = $TWYPO->getInternal('colMapping');
		$items = $TWYPO->renderConf( $conf );
		$content = array();
		if( count($items) > 0 ) {
			foreach($items as $item) {
				if ( !isset($content[ $colMapping[intval($item['colPos'])] ]) )
					$content[ $colMapping[intval($item['colPos'])] ] = array();

				array_push($content[ $colMapping[intval($item['colPos'])] ], array(
					'title' => $item['header'],
					'text' => $this->parseRTE( $item['bodytext'] ),
					'link' => array(
						'url' => $this->getLink( $item['header_link'], 'url' ),
						'target' => $this->getLink( $item['header_link'], 'target' )
					),
					'imageUrls' => $this->getImages($item['uid'])
				));
			}
		}
		$TWYPO->assign('content', $content);
	}

	// Utility
	// @part = url|target
	private function getLink($link, $part = 'url') {
		return $this->cObj->typoLink( $link, array(
			'parameter' => $link,
			'forceAbsoluteUrl' => true,
			'returnLast' => 'url'
		));
	}

	private function parseRTE($text) {
		global $TSFE;
		$ret = '';

		$parseFunc = $TSFE->tmpl->setup['lib.']['parseFunc_RTE.'];
		if ( is_array($parseFunc) ) $ret = $this->cObj->parseFunc($text, $parseFunc);
		return $ret;
	}

	/*
		Snippet got from Official WIKI documentation.
		@ref: http://wiki.typo3.org/File_Abstraction_Layer
	*/
	private function getImages($uid, $conf) {
		$ret = array();

		$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$fileObjects = $fileRepository->findByRelation('tt_content', 'image', $uid);

		// get Imageobject information
		$files = array();
		foreach ($fileObjects as $key => $value) {
			$fileDefinition = $value->getOriginalFile()->getProperties();
			array_push( $ret, array(
				'url' => $this->getAbsFilePath( $fileDefinition['identifier'] )
			));
		}

		return $ret;
	}

	private function getAbsFilePath($filePath){
		global $TWYPO;

		return $TWYPO->get('baseUrl') . 'fileadmin/' . $filePath;
	}
}