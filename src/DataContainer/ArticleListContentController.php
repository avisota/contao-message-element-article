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

use Contao\System;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Hofff\Contao\Selectri\Model\Node;

/**
 * Class ArticleListContentController
 *
 * @package Avisota\Contao\Message\Element\Article\DataContainer
 */
class ArticleListContentController
{
    /**
     * Get the content callback listener.
     *
     * @param Node $node
     *
     * @return string
     */
    public function getContent(Node $node)
    {
        return sprintf(
            "<div style=\" float: right; margin-right: 64px; margin-top: 3px;\">%s</div>",
            self::getButtons(explode('::', $node->getKey())[1])
        );
    }

    /**
     * Get buttons.
     *
     * @param $articleId
     *
     * @return string
     */
    protected function getButtons($articleId)
    {
        System::loadLanguageFile('tl_article');

        $buttons = self::getModalEditButton($articleId);
        $buttons .= self::getModalShowButton($articleId);

        return $buttons;
    }

    /**
     * Get modal edit button.
     *
     * @param $articleId
     *
     * @return string
     */
    protected function getModalEditButton($articleId)
    {
        $urlParams = array(
            array(
                'name'  => 'id',
                'value' => $articleId
            ),
            array(
                'name'  => 'popup',
                'value' => 1
            ),
        );

        $label = $GLOBALS['TL_LANG']['tl_article']['edit'][1];

        return '<a ' .
               'href="' . self::getBackendUrl($urlParams) . '" ' .
               'title="' . self::getTitle($label, $articleId) . '" ' .
               'onclick="' . self::getOnClickModal($label, $articleId) . '" ' .
               'class="edit">' .
               \Image::getHtml('edit.gif', $GLOBALS['TL_LANG']['tl_article']['edit'][0]) .
               '</a> ';
    }

    /**
     * Get modal show button.
     *
     * @param $articleId
     *
     * @return string
     */
    protected function getModalShowButton($articleId)
    {
        $urlParams = array(
            array(
                'name'  => 'act',
                'value' => 'show'
            ),
            array(
                'name'  => 'table',
                'value' => 'tl_article'
            ),
            array(
                'name'  => 'id',
                'value' => $articleId
            ),
            array(
                'name'  => 'popup',
                'value' => 1
            ),
        );

        $label = $GLOBALS['TL_LANG']['tl_article']['show'][1];

        return '<a ' .
               'href="' . self::getBackendUrl($urlParams) . '" ' .
               'title="' . self::getTitle($label, $articleId) . '" ' .
               'onclick="' . self::getOnClickModal($label, $articleId) . '" ' .
               'class="edit">' .
               \Image::getHtml('show.gif', $GLOBALS['TL_LANG']['tl_article']['show'][0]) .
               '</a> ';
    }

    /**
     * Get backend url.
     *
     * @param array $params
     *
     * @return string
     */
    protected function getBackendUrl(array $params)
    {
        $urlBuilder = new UrlBuilder();
        $urlBuilder->setPath('contao/main.php')
            ->setQueryParameter('do', 'article')
            ->setQueryParameter('table', 'tl_content');


        foreach ($params as $param) {
            $urlBuilder->setQueryParameter($param['name'], $param['value']);
        }

        $urlBuilder->setQueryParameter('rt', REQUEST_TOKEN);
        $urlBuilder->setQueryParameter('ref', TL_REFERER_ID);

        return $urlBuilder->getUrl();
    }

    /**
     * Get title.
     *
     * @param $label
     * @param $articleId
     *
     * @return string
     */
    protected function getTitle($label, $articleId)
    {
        return specialchars(sprintf($label, $articleId));
    }

    /**
     * Get on click for modal.
     *
     * @param $label
     * @param $articleId
     *
     * @return string
     */
    protected function getOnClickModal($label, $articleId)
    {
        return 'Backend.openModalIframe({\'width\':768,\'title\':\'' .
               specialchars(str_replace("'", "\\'", sprintf($label, $articleId))) .
               '\',\'url\':this.href});return false';
    }
}
