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

use Avisota\Contao\Selectri\DataContainer\DatabaseTrait;
use Avisota\Contao\Selectri\Model\Tree\SQLAdjacencyTreeDataConfigWithItems;
use Avisota\Contao\Selectri\Model\Tree\SQLAdjacencyTreeDataWithItems;
use Contao\BackendUser;
use Contao\Database;
use Hofff\Contao\Selectri\Exception\SelectriException;
use Hofff\Contao\Selectri\Model\AbstractData;
use Hofff\Contao\Selectri\Model\Tree\SQLAdjacencyTreeDataConfig;
use Hofff\Contao\Selectri\Util\SQLUtil;
use Hofff\Contao\Selectri\Widget;

/**
 * Class ArticleListData
 *
 * @package Avisota\Contao\Message\Element\Article\DataContainer
 */
class ArticleListData extends AbstractData
{
    use DatabaseTrait;
    
    /**
     * ArticleListData constructor.
     *
     * @param Widget   $widget
     *
     * @param Database $database
     */
    public function __construct(Widget $widget, Database $database)
    {
        parent::__construct($widget);
        $this->setDatabase($database);


    }

    /**
     * @see \Hofff\Contao\Selectri\Model\Data::browseFrom()
     */
    public function browseFrom($key = null)
    {
        $treeData = new SQLAdjacencyTreeDataWithItems($this->getWidget(), $this->getDatabase(), $this->getTreeDataConfig());

        return $treeData->browseFrom($key);
    }

    /**
     * Configure the tree data.
     *
     * @return SQLAdjacencyTreeDataConfigWithItems
     */
    protected function getTreeDataConfig()
    {
        $config = new SQLAdjacencyTreeDataConfigWithItems();

        $config->setRoots($this->getRoots());
        $config->setSelectionMode($this->getSelectionMode());
        $config->setTable('tl_page');
        $config->setRootValue(0);
        $config->setParentKeyColumn('pid');
        $config->setKeyColumn('id');
        $config->addColumns($this->getColumns());
        // Todo is search column must be configured
        $config->addSearchColumns('title');
        $config->setOrderByExpr('sorting');
        $config->setLabelCallback($this->getLabelCallback($config));
        $config->setIconCallback($this->getIconCallback());
        $config->setItemCallback($this->getItemsCallback());


        return $config;
    }

    /**
     * Get the roots, who has user permission of it.
     *
     * @return array|int
     */
    protected function getRoots()
    {
        $user = BackendUser::getInstance();

        if ($user->isAdmin) {
            return 0;
        }

        return $user->pagemounts;
    }

    /**
     * Get the Selection mode.
     *
     * @return string
     */
    protected function getSelectionMode()
    {
        return SQLAdjacencyTreeDataConfig::SELECTION_MODE_ALL;
    }

    /**
     * Get the page columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return array(
            'title',
            'id',
            'tstamp',
            'type',
            'published',
            'start',
            'stop',
            'hide',
            'protected',
        );
    }

    /**
     * Get the label callback.
     *
     * @param $config
     *
     * @return callable
     */
    protected function getLabelCallback($config)
    {
        $labelFormatter = SQLUtil::createLabelFormatter(
            $this->getDatabase(),
            $config->getTable(),
            $config->getKeyColumn()
        );

        return $labelFormatter->getCallback();
    }

    /**
     * Get the icon callback.
     *
     * @return array
     */
    protected function getIconCallback()
    {
        return array(
            '\Hofff\Contao\Selectri\Util\Icons',
            'getPageIcon'
        );
    }

    /**
     * Get items callback.
     *
     * @return array
     */
    protected function getItemsCallback()
    {
        return array(
            '\Avisota\Contao\Message\Element\Article\DataContainer\ArticleListController',
            'getItems'
        );
    }

    /**
     * @throws SelectriException If this data instance is not configured correctly
     *
     * @return void
     */
    public function validate()
    {
        // Do nothing, is ever valid.
    }

    /**
     * Returns an iterator over nodes identified by the given primary
     * keys.
     *
     * The returned nodes should NOT be traversed recursivly through the node's
     * getChildrenIterator method.
     *
     * @param         array <string> $keys An array of primary key values in their
     *                      string representation
     * @param boolean $selectableOnly
     *
     * @return Iterator<Node> An iterator over the nodes identified by
     *        the given primary keys
     */
    public function getNodes(array $keys, $selectableOnly = true)
    {
        $articleList = new ArticleListController($this->getWidget()->getData());

        return $articleList->getNodes($keys, $selectableOnly);
    }

    /**
     * Filters the given primary keys for values identifing only existing
     * records.
     *
     * @param array <string> $keys An array of primary key values in their
     *              string representation
     *
     * @return array<string> The input array with all invalid values removed
     */
    public function filter(array $keys)
    {
        $articleList = new ArticleListController($this->getWidget()->getData());

        return $articleList->filter($keys);
    }

    /**
     * @see \Hofff\Contao\Selectri\Model\Data::isSearchable()
     */
    public function isSearchable()
    {
        return false;
    }

    /**
     * @see \Hofff\Contao\Selectri\Model\Data::isBrowsable()
     */
    public function isBrowsable()
    {
        return true;
    }
}
