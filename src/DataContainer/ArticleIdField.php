<?php

/**
 * Avisota newsletter and mailing system
 * Copyright © 2016 Sven Baumann
 *
 * PHP version 5
 *
 * @copyright  way.vision 2016
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @package    avisota/contao-message-element-article
 * @license    LGPL-3.0+
 * @filesource
 */

namespace Avisota\Contao\Message\Element\Article\DataContainer;

use SelectriContaoTableDataFactory;

/**
 * Class ArticleIdField
 *
 * @package Avisota\Contao\Message\Element\Article\DataContainer
 */
class ArticleIdField
{
    /**
     * @return SelectriContaoTableDataFactory
     */
    public static function getDataForSelectri()
    {
        /** @var SelectriContaoTableDataFactory $data */
        $data = SelectriContaoTableDataFactory::create();
        $data->setItemTable('tl_article');
        $data->getConfig()
            ->setItemSearchColumns(array('title'));
        $data->getConfig()
            ->setItemConditionExpr('tstamp > 0');

        return $data;
    }
}
