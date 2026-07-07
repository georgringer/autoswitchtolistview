<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview\EventListener;

use GeorgRinger\Autoswitchlistview\FolderDecision;
use GeorgRinger\Autoswitchlistview\FolderDecisions;
use GeorgRinger\Autoswitchlistview\HintRenderer;
use GeorgRinger\Autoswitchlistview\SwitchMode;
use TYPO3\CMS\Backend\Controller\Event\RenderAdditionalContentToRecordListEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RecordListListener
{
    public function __construct(
        private readonly FolderDecisions $folderDecisions,
        private readonly HintRenderer $hintRenderer,
    ) {
    }

    public function __invoke(RenderAdditionalContentToRecordListEvent $event): void
    {
        $pageId = (int)($event->getRequest()->getQueryParams()['id'] ?? 0);
        $pageRecord = BackendUtility::getRecord('pages', $pageId);

        if ($pageRecord === null || $pageRecord['doktype'] !== PageRepository::DOKTYPE_SYSFOLDER) {
            return;
        }
        if ($this->folderDecisions->isDisabledByPageTsconfig($pageId)) {
            return;
        }

        $switchMode = $this->folderDecisions->switchMode();
        $folderSwitchesAutomatically = match ($switchMode) {
            SwitchMode::Switch => true,
            SwitchMode::Ask => false,
            SwitchMode::AskOnce => $this->folderDecisions->decisionFor($pageId) === FolderDecision::SwitchToList,
        };
        if (!$folderSwitchesAutomatically) {
            return;
        }

        $withForgetLink = $switchMode === SwitchMode::AskOnce;
        $event->addContentBelow($this->hintRenderer->renderListModuleHint($pageId, $withForgetLink));
        GeneralUtility::makeInstance(PageRenderer::class)->loadJavaScriptModule('@georgringer/autoswitchtolistview/hint.js');
    }
}
