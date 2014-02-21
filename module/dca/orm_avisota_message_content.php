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
	'include' => array('articleId', 'articleFull'),
	'expert'  => array(':hide', 'cssID', 'space')
);

$GLOBALS['TL_DCA']['orm_avisota_message_content']['fields']['articleId']   = array
(
	'label'     => &$GLOBALS['TL_LANG']['orm_avisota_message_content']['articleId'],
	'exclude'   => true,
	'inputType' => 'selectri',
	'eval'      => array(
		'min'  => 1,
		'data' => function () {
				/** @var SelectriContaoTableDataFactory $data */
				$data = SelectriContaoTableDataFactory::create();
				$data->setItemTable('tl_article');
				$data->getConfig()
					->setItemSearchColumns(array('title'));
				$data->getConfig()
					->setItemConditionExpr('tstamp > 0');
				return $data;
			},
	),
	'field'     => array(
		'type'     => 'integer',
		'nullable' => true,
	),
);
$GLOBALS['TL_DCA']['orm_avisota_message_content']['fields']['articleFull'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['orm_avisota_message_content']['articleFull'],
	'default'   => false,
	'exclude'   => true,
	'inputType' => 'checkbox',
);
