<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

/* Declarating the Twig Template cObj */
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][\JX\Twypo\TwigPageObjectRender::CONTENT_OBJECT_NAME] = array(
	\JX\Twypo\TwigPageObjectRender::CONTENT_OBJECT_NAME,
	'\JX\Twypo\TwigPageObjectRender'
);
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][\JX\Twypo\TwigContentObjectRender::CONTENT_OBJECT_NAME] = array(
	\JX\Twypo\TwigContentObjectRender::CONTENT_OBJECT_NAME,
	'\JX\Twypo\TwigContentObjectRender'
);
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][\JX\Twypo\Helper\TwigColumnMapperObjectRender::CONTENT_OBJECT_NAME] = array(
	\JX\Twypo\Helper\TwigColumnMapperObjectRender::CONTENT_OBJECT_NAME,
	'\JX\Twypo\Helper\TwigColumnMapperObjectRender'
);
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['cObjTypeAndClass'][\JX\Twypo\Helper\TwigLayoutMapperObjectRender::CONTENT_OBJECT_NAME] = array(
	\JX\Twypo\Helper\TwigLayoutMapperObjectRender::CONTENT_OBJECT_NAME,
	'\JX\Twypo\Helper\TwigLayoutMapperObjectRender'
);
/* Override all the cObjs! */
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\Menu\\TextMenuContentObject'] = array(
    'className' => 'JX\\Twypo\\Xclass\\Menu\\TextMenuContentObject',
);