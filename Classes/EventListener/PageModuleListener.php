<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview\EventListener;

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageModuleListener
{

    public function __invoke(ModifyPageLayoutContentEvent $event)
    {
        $pageId = $id = (int)($event->getRequest()->getQueryParams()['id'] ?? 0);
        $pageRecord = BackendUtility::getRecord('pages', $pageId);
        $doktypeSysfolder = PageRepository::DOKTYPE_SYSFOLDER;

        if ($pageRecord && $pageRecord['doktype'] === $doktypeSysfolder && $event->getRequest()->getUri()->getPath() === '/typo3/module/web/layout') {
            $uri = $event->getRequest()->getUri()->withPath('/typo3/module/web/list');
            $event->setFooterContent(sprintf('<span id="autoswitchtolistview" data-uri="%s" data-pageid="%s"></span>', (string)$uri, $pageId));

            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->loadJavaScriptModule('@georgringer/autoswitchtolistview/switch.js');
        }
    }
}
