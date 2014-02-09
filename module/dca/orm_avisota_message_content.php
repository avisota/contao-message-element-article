<?php

/**
 * Avisota newsletter and mailing system
 * Copyright (C) 2013 Tristan Lins
 *
 * PHP version 5
 *
 * @copyright  bit3 UG 2013
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    avisota/contao-message-element-article
 * @license    LGPL-3.0+
 * @filesource
 */


/**
 * Table orm_avisota_message_content
 * Entity Avisota\Contao:MessageContent
 */
$GLOBALS['TL_DCA']['orm_avisota_message_content']['metapalettes']['article'] = array
(
	'type'    => array('type', 'cell', 'headline'),
	'include' => array('article'),
	'expert'  => array(':hide', 'cssID', 'space')
);

$GLOBALS['TL_DCA']['orm_avisota_message_content']['fields']['article'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['orm_avisota_message_content']['articleAlias'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => CreateOptionsEventCallbackFactory::createCallback('avisota.create-article-options'),
	'eval'             => array('mandatory' => true, 'submitOnChange' => true),
	'wizard'           => array
	(
		array('Avisota\Contao\Core\DataContainer\MessageContent', 'editArticleAlias')
	),
	'field'            => array(
		'type'   => 'serialized',
		'length' => 65532
	),
);
