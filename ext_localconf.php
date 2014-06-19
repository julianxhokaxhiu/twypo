<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/* Declarating the Twig Template cObj */
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][\JX\Twypo\TwigContentObjectRenderer::CONTENT_OBJECT_NAME] = array(
	\JX\Twypo\TwigContentObjectRenderer::CONTENT_OBJECT_NAME,
	'\JX\Twypo\TwigContentObjectRenderer'
);

/* Override all the cObjs! */
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\Menu\\TextMenuContentObject'] = array(
    'className' => 'JX\\Twypo\\Xclass\\Menu\\TextMenuContentObject',
);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\ContentContentObject'] = array(
    'className' => 'JX\\Twypo\\Xclass\\ContentContentObject',
);