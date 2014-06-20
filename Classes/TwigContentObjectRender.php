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

	const CONTENT_OBJECT_NAME = 'TWIGCONTENT';

	public function cObjGetSingleExt( $name, $conf, $TSkey, $parent ) {
		global $TSFE, $TWYPO;

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
				$content[ $colMapping[intval($item['colPos'])] ] = $item;
			}
		}
		$TWYPO->assign('content', $content);
	}
}