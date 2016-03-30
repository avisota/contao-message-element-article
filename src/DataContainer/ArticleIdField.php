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

use Hofff\Contao\Selectri\Model\Flat\SQLListDataFactory;


/**
 * Class ArticleIdField
 *
 * @package Avisota\Contao\Message\Element\Article\DataContainer
 */
class ArticleIdField
{
    /**
     * @return SQLListDataFactory
     */
    public static function getDataForSelectri()
    {
        $dataFactory = new SQLListDataFactory();
        $dataFactory->getConfig()->setTable('tl_article');
        $dataFactory->getConfig()->addColumns([ 'title', 'id' ]);
        $dataFactory->getConfig()->addSearchColumns('title');
        $dataFactory->getConfig()->setConditionExpr('tstamp > 0');

        return $dataFactory;
    }
}
