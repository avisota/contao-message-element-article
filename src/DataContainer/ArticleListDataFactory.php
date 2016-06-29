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

use Contao\Database;
use Hofff\Contao\Selectri\Model\Data;
use Hofff\Contao\Selectri\Model\DataFactory;
use Hofff\Contao\Selectri\Widget;

/**
 * Class ArticleListDataFactory
 *
 * @package Avisota\Contao\Message\Element\Article\DataContainer
 */
class ArticleListDataFactory implements DataFactory
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * ArticleListDataFactory constructor.
     */
    public function __construct()
    {
        $this->setDatabase(Database::getInstance());
    }

    /**
     * @param mixed $params Configuration parameters (usally the eval array of
     *                      the DCA field the widget using this factory)
     *
     * @return void
     */
    public function setParameters($params)
    {
        // TODO: Implement setParameters() method.
    }

    /**
     * @param Widget $widget The widget the created data instance will belong to
     *
     * @return Data A new data instance
     */
    public function createData(Widget $widget = null)
    {
        return new ArticleListData($widget, $this->getDatabase());
    }

    /**
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param Database $database
     */
    protected function setDatabase($database)
    {
        $this->database = $database;
    }
}
