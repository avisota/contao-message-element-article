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
        global $container;

        System::loadLanguageFile('tl_article');

        $translator = $container['translator'];

        $buttons = self::getModalEditButton($articleId, $translator);
        $buttons .= self::getModalShowButton($articleId, $translator);

        return $buttons;
    }

    /**
     * Get modal edit button.
     *
     * @param $articleId
     *
     * @param $translator
     *
     * @return string
     */
    protected function getModalEditButton($articleId, $translator)
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

        $label = $translator->translate('edit.1', 'tl_article');

        return '<a ' .
               'href="' . self::getBackendUrl($urlParams) . '" ' .
               'title="' . self::getTitle($label, $articleId) . '" ' .
               'onclick="' . self::getOnClickModal($label, $articleId) . '" ' .
               'class="edit">' .
               \Image::getHtml('edit.gif', $translator->translate('edit.0', 'tl_article')) .
               '</a> ';
    }

    /**
     * Get modal show button.
     *
     * @param $articleId
     *
     * @return string
     */
    protected function getModalShowButton($articleId, $translator)
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

        $label = $translator->translate('show.1', 'tl_article');

        return '<a ' .
               'href="' . self::getBackendUrl($urlParams) . '" ' .
               'title="' . self::getTitle($label, $articleId) . '" ' .
               'onclick="' . self::getOnClickModal($label, $articleId) . '" ' .
               'class="edit">' .
               \Image::getHtml('show.gif', $translator->translate('show.0', 'tl_article')) .
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
