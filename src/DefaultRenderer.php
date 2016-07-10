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

namespace Avisota\Contao\Message\Element\Article;

use Avisota\Contao\Core\Message\Renderer;
use Avisota\Contao\Message\Core\Event\AvisotaMessageEvents;
use Avisota\Contao\Message\Core\Event\RenderMessageContentEvent;
use Contao\ArticleModel;
use Contao\Doctrine\ORM\Entity;
use Contao\Doctrine\ORM\EntityAccessor;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetArticleEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TwigTemplate;

/**
 * Class DefaultRenderer
 */
class DefaultRenderer implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            AvisotaMessageEvents::RENDER_MESSAGE_CONTENT => array(
                array('renderContent'),
            ),
        );
    }

    /**
     * Render a single message content element.
     *
     * @param RenderMessageContentEvent $event
     *
     * @return string
     */
    public function renderContent(RenderMessageContentEvent $event)
    {
        global $container;

        $content = $event->getMessageContent();

        if ($content->getType() != 'article' || $event->getRenderedContent() || !$content->getArticleId()) {
            return;
        }

        /** @var EntityAccessor $entityAccessor */
        $entityAccessor = $container['doctrine.orm.entityAccessor'];

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $container['event-dispatcher'];

        $articles = array();
        foreach ($content->getArticleId() as $articleId) {
            if (empty($articleId)) {
                continue;
            }

            $getArticleEvent = new GetArticleEvent(
                $articleId,
                !$content->getArticleFull(),
                $content->getCell()
            );

            if (!$content->getArticleFull()) {
                $article             = ArticleModel::findByPk($articleId);
                $article->showTeaser = 1;

                global $objPage;
                if (!$objPage) {
                    $objPage = $article->getRelated('pid');
                    $objPage->loadDetails();
                }
            }

            $eventDispatcher->dispatch(ContaoEvents::CONTROLLER_GET_ARTICLE, $getArticleEvent);

            $context            = $entityAccessor->getProperties($content);
            $context['article'] = $getArticleEvent->getArticle();

            array_push($articles, $context);
        }


        $buffer = '';
        foreach ($articles as $article) {
            $template = new TwigTemplate('avisota/message/renderer/default/mce_article', 'html');
            $buffer .= $template->parse($article);
        }

        $event->setRenderedContent($buffer);
    }
}
