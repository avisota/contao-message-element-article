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

use Contao\BackendUser;
use Contao\Controller;
use Contao\Database;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use Hofff\Contao\Selectri\Model\Data;
use Hofff\Contao\Selectri\Model\Flat\SQLListData;
use Hofff\Contao\Selectri\Model\Flat\SQLListDataConfig;
use Hofff\Contao\Selectri\Util\Icons;
use Hofff\Contao\Selectri\Util\SQLUtil;

/**
 * Class ArticleListController
 *
 * @package Avisota\Contao\Message\Element\Article\DataContainer
 */
class ArticleListController
{
    protected $data;

    protected $node;

    /**
     * ArticleListController constructor.
     *
     * @param Data $data
     * @param      $node
     */
    public function __construct(Data $data, $node = null)
    {
        $this->setNode($node);
        $this->setData($data);
    }

    /**
     * Callback listener for get node items.
     *
     * @return null|string
     */
    public function getItems()
    {
        if ($this->node['type'] !== 'regular') {
            return null;
        }

        $dataProvider = new DefaultDataProvider();
        $dataProvider->setBaseConfig(
            array(
                'source' => 'tl_article'
            )
        );

        $count = $dataProvider->getCount(
            $dataProvider->getEmptyConfig()->setFilter(
                array(
                    array(
                        'property'  => 'pid',
                        'value'     => $this->node['id'],
                        'operation' => '='
                    )
                )
            )
        );

        if (intval($count) <= 0) {
            return null;
        }

        return $this->getArticleNode($this->node['id']);
    }

    /**
     * Get article for this node.
     *
     * @param $pid
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getArticleNode($pid)
    {
        $listData = new SQLListData(
            $this->getData()->getWidget(),
            Database::getInstance(),
            $this->getListDataConfig($pid)
        );

        list($level, $start) = $listData->browseFrom();

        ob_start();
        include Controller::getTemplate('avisota_selectri_with_items');
        return ob_get_clean();
    }

    /**
     * Get nodes.
     *
     * @param array $keys
     * @param bool  $selectableOnly
     *
     * @return array
     */
    public function getNodes(array $keys, $selectableOnly = true)
    {
        $listData = new SQLListData(
            $this->getData()->getWidget(),
            Database::getInstance(),
            $this->getListDataConfig()
        );

        return $listData->getNodes($keys, $selectableOnly);
    }

    public function filter(array $keys)
    {
        $listData = new SQLListData(
            $this->getData()->getWidget(),
            Database::getInstance(),
            $this->getListDataConfig()
        );

        return $listData->filter($keys);
    }

    /**
     * Get list data config.
     *
     * @param $pid
     *
     * @return SQLListDataConfig
     */
    protected function getListDataConfig($pid = null)
    {
        $config = new SQLListDataConfig();

        $config->setTable('tl_article');
        $config->setKeyColumn('id');
        $config->setOrderByExpr('sorting');
        $config->setColumns($this->getColumns());
        // Todo is search column must be configured
        $config->addSearchColumns('title');
        $config->setLabelCallback($this->getLabelCallback($config));
        $config->setIconCallback($this->getIconCallback());
        $config->setContentCallback($this->getContentCallback());

        if ($pid) {
            $config->setConditionExpr('pid=' . $pid);
        }

        return $config;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $database = Database::getInstance();
        $result   = $database->listFields('tl_article');

        $columns = array();
        foreach ($result as $field) {
            if (!array_key_exists('origtype', $field)) {
                continue;
            }

            array_push($columns, $field['name']);
        }

        return $columns;
    }

    /**
     * Get label callback.
     *
     * @param $config
     *
     * @return callable
     */
    protected function getLabelCallback($config)
    {
        $labelFormatter = SQLUtil::createLabelFormatter(
            Database::getInstance(),
            $config->getTable(),
            $config->getKeyColumn()
        );

        return $labelFormatter->getCallback();
    }

    /**
     * Get icon callback.
     *
     * @return array
     */
    protected function getIconCallback()
    {
        return array(
            __CLASS__,
            'getArticleTableIconCallback'
        );
    }

    /**
     * Get content callback.
     *
     * @return array
     */
    protected function getContentCallback()
    {
        return array(
            '\Avisota\Contao\Message\Element\Article\DataContainer\ArticleListContentController',
            'getContent'
        );
    }

    /**
     * Get article table icon callback.
     *
     * @return string
     */
    public function getArticleTableIconCallback()
    {
        $user = BackendUser::getInstance();

        return sprintf(
            'system/themes/%s/images/%s',
            $user->backendTheme,
            Icons::getTableIcon('tl_article')
        );
    }

    /**
     * Get sort.
     *
     * @return string
     */
    public function getSort()
    {
        return 'list';
    }

    /**
     * Get this mode.
     *
     * @return mixed
     */
    protected function getNode()
    {
        return $this->node;
    }

    /**
     * Set this node.
     *
     * @param mixed $node
     */
    protected function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * Get this data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set this data.
     *
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
