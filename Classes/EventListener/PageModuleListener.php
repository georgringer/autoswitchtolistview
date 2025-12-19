<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview\EventListener;

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageModuleListener
{

    public function __invoke(ModifyPageLayoutContentEvent $event)
    {
        $pageId = $id = (int)($event->getRequest()->getQueryParams()['id'] ?? 0);
        $pageRecord = BackendUtility::getRecord('pages', $pageId);
        $doktypeSysfolder = PageRepository::DOKTYPE_SYSFOLDER;

        if ($pageRecord && $pageRecord['doktype'] === $doktypeSysfolder && $event->getRequest()->getUri()->getPath() === '/typo3/module/web/layout') {
            if (ExtensionManagementUtility::isLoaded('news_content_elements') && isset($event->getRequest()->getQueryParams()['tx_news_id'])) {
                return;
            }
            $tsconfig = BackendUtility::getPagesTSconfig($pageId);
            if (!(isset($tsconfig['autoswitchtolistview.']) && isset($tsconfig['autoswitchtolistview.']['disable']) && $tsconfig['autoswitchtolistview.']['disable'] == 1)) {
                $path = ((new Typo3Version())->getMajorVersion() > 13) ? '/typo3/module/content/records' : '/typo3/module/web/list';

                $uri = $event->getRequest()->getUri()->withPath($path);
                $event->setFooterContent(sprintf('<span id="autoswitchtolistview" data-uri="%s" data-pageid="%s"></span>', (string)$uri, $pageId));

                $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
                $pageRenderer->loadJavaScriptModule('@georgringer/autoswitchtolistview/switch.js');
            }
        }
    }
}
