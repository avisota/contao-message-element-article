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
use Avisota\Contao\Entity\Message;
use Avisota\Contao\Message\Core\Event\AvisotaMessageEvents;
use Avisota\Contao\Message\Core\Event\RenderMessageContentEvent;
use Contao\Doctrine\ORM\Entity;
use Contao\Doctrine\ORM\EntityAccessor;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetArticleEvent;
use Pimple;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
            AvisotaMessageEvents::RENDER_MESSAGE_CONTENT => 'renderContent',
        );
    }

    /**
     * Render a single message content element.
     *
     * @param RenderMessageContentEvent $event
     *
     * @return string
     * @internal param MessageContent $content
     * @internal param RecipientInterface $recipient
     */
    public function renderContent(RenderMessageContentEvent $event)
    {
        global $container;

        $content = $event->getMessageContent();

        if ($content->getType() != 'article' || $event->getRenderedContent()) {
            return;
        }

        /** @var EntityAccessor $entityAccessor */
        $entityAccessor = $container['doctrine.orm.entityAccessor'];

        $getArticleEvent = new GetArticleEvent(
            $content->getArticleId(),
            !$content->getArticleFull(),
            $content->getCell()
        );

        $GLOBALS['TL_HOOKS']['isVisibleElement']['avisota-message-article'] = array(
            DefaultRenderer::class,
            'handleElementTemplate'
        );

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $container['event-dispatcher'];
        $eventDispatcher->dispatch(ContaoEvents::CONTROLLER_GET_ARTICLE, $getArticleEvent);

        $context            = $entityAccessor->getProperties($content);
        $context['article'] = $getArticleEvent->getArticle();

        $template = new \TwigTemplate('avisota/message/renderer/default/mce_article', 'html');
        $buffer   = $template->parse($context);

        unset($GLOBALS['TL_HOOKS']['isVisibleElement']['avisota-message-article']);
        $containerId = 'avisota.article_' . $content->getArticleId();
        if ($container->offsetExists($containerId)) {
            $this->removeEachTemplate($container->offsetGet($containerId));
            $container->offsetSet($containerId, array());
        }

        $event->setRenderedContent($buffer);
    }

    public function handleElementTemplate(\Model &$model, $isVisible)
    {
        if ($model instanceof \ContentModel) {
            $this->handleContentElement($model);
        }

        return true;
    }

    protected function handleContentElement(\ContentModel &$model)
    {
        foreach (array('type', 'galleryTpl', 'customTpl',) as $propertyTemplate) {
            if (empty($model->$propertyTemplate)) {
                continue;
            }

            $template = $this->findTemplate($model->$propertyTemplate);
            if ($model->$propertyTemplate !== $template) {
                /** @var Pimple $container */
                global $container;

                $containerId = 'avisota.article_' . $model->pid;
                if (!$container->offsetExists($containerId)) {
                    $container->offsetSet($containerId, array());
                }

                $containerTemplates   = $container->offsetGet($containerId);
                $containerTemplates[] = $template;
                $container->offsetSet($containerId, $containerTemplates);

                $model->$propertyTemplate = $template;
            }
        }
    }

    protected function findTemplate($searchTemplate)
    {
        /** @var Pimple $container */
        global $container;

        /** @var Message $message */
        $message         = $container->offsetGet('avisota.current-message');
        $messageCategory = $message->getCategory();
        $messageTheme    = $messageCategory->getLayout()->getTheme();

        $template = null;
        if ($messageTheme->getTemplateDirectory()
            && file_exists(TL_ROOT . '/templates/' . $messageTheme->getTemplateDirectory() . '/' . $searchTemplate . '.html5')
        ) {
            $template = $this->copyTemplateInRootTemplates(
                $messageTheme->getTemplateDirectory() . '/' . $searchTemplate,
                '.' . microtime(true)
            );
        }
        if (!$template
            && $messageCategory->getViewOnlinePage() > 0
        ) {
            $viewOnlinePage = \PageModel::findByPk($messageCategory->getViewOnlinePage());

            $pageTheme = null;
            if ($viewOnlinePage) {
                $viewOnlinePage->loadDetails();
                $pageTheme = $viewOnlinePage->getRelated('layout')->getRelated('pid');
            }

            if ($pageTheme
                && file_exists(TL_ROOT . '/' . $pageTheme->templates . '/' . $searchTemplate . '.html5')
            ) {
                $source = $pageTheme->templates;
                $chunks = explode('/', $source);
                if (count($chunks) > 1) {
                    if (in_array('templates', array_values($chunks))) {
                        $unset = array_flip($chunks)['templates'];
                        unset($chunks[$unset]);
                    }
                }
                $source = implode('/', $chunks);

                $template = $this->copyTemplateInRootTemplates(
                    $source . '/' . $searchTemplate,
                    '.' . microtime(true)
                );
            }
        }

        if (!$template) {
            $template = $searchTemplate;
        }

        return $template;
    }

    protected function copyTemplateInRootTemplates($source, $destination)
    {
        $sourceFile = new \File('templates/' . $source . '.html5');
        $sourceFile->copyTo('templates/' . $destination . '.html5');

        return $destination;
    }

    protected function removeEachTemplate(array $removes)
    {
        if (count($removes) < 1) {
            return;
        }

        foreach ($removes as $remove) {
            $removeFile = new \File('templates/' . $remove . '.html5', true);
            if (!$removeFile->exists()) {
                continue;
            }

            $removeFile->delete();
        }
    }
}
