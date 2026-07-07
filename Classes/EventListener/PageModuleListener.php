<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview\EventListener;

use GeorgRinger\Autoswitchlistview\FolderDecision;
use GeorgRinger\Autoswitchlistview\FolderDecisions;
use GeorgRinger\Autoswitchlistview\HintRenderer;
use GeorgRinger\Autoswitchlistview\SwitchMode;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageModuleListener
{
    private const LANGUAGE_PREFIX = 'LLL:EXT:autoswitchtolistview/Resources/Private/Language/locallang.xlf:';

    public function __construct(
        private readonly FolderDecisions $folderDecisions,
        private readonly HintRenderer $hintRenderer,
    ) {
    }

    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $request = $event->getRequest();
        $pageId = (int)($request->getQueryParams()['id'] ?? 0);
        $pageRecord = BackendUtility::getRecord('pages', $pageId);

        if ($pageRecord === null || !$this->isAutoswitchContext($request, $pageRecord, $pageId)) {
            return;
        }

        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        if ($this->userDecidedToStay($pageId)) {
            $event->setFooterContent($this->hintRenderer->renderStayHint($pageId));
            $pageRenderer->loadJavaScriptModule('@georgringer/autoswitchtolistview/hint.js');
            return;
        }

        $event->setFooterContent($this->buildTriggerMarkup($pageId, $pageRecord));
        $pageRenderer->loadJavaScriptModule('@georgringer/autoswitchtolistview/switch.js');
    }

    /**
     * @param array<string, mixed> $pageRecord
     */
    private function isAutoswitchContext(ServerRequestInterface $request, array $pageRecord, int $pageId): bool
    {
        if ($pageRecord['doktype'] !== PageRepository::DOKTYPE_SYSFOLDER) {
            return false;
        }
        if ($request->getUri()->getPath() !== '/typo3/module/web/layout') {
            return false;
        }
        if (($request->getQueryParams()['tx_autoswitchtolistview_skip'] ?? '') === '1') {
            return false;
        }
        if ($this->isNewsContentElementsRequest($request)) {
            return false;
        }

        return !$this->folderDecisions->isDisabledByPageTsconfig($pageId);
    }

    private function isNewsContentElementsRequest(ServerRequestInterface $request): bool
    {
        return ExtensionManagementUtility::isLoaded('news_content_elements')
            && isset($request->getQueryParams()['tx_news_id']);
    }

    private function userDecidedToStay(int $pageId): bool
    {
        return $this->folderDecisions->switchMode() === SwitchMode::AskOnce
            && $this->folderDecisions->decisionFor($pageId) === FolderDecision::Stay;
    }

    /**
     * @param array<string, mixed> $pageRecord
     */
    private function buildTriggerMarkup(int $pageId, array $pageRecord): string
    {
        $attributes = [
            'id' => 'autoswitchtolistview',
            'data-module' => ((new Typo3Version())->getMajorVersion() > 13) ? 'records' : 'web_list',
            'data-page-id' => (string)$pageId,
        ];
        if ($this->needsConfirmation($pageId)) {
            $attributes += $this->confirmationAttributes($pageRecord);
        }

        return '<span ' . GeneralUtility::implodeAttributes($attributes, true) . '></span>';
    }

    private function needsConfirmation(int $pageId): bool
    {
        return match ($this->folderDecisions->switchMode()) {
            SwitchMode::Switch => false,
            SwitchMode::Ask => true,
            SwitchMode::AskOnce => $this->folderDecisions->decisionFor($pageId) === null,
        };
    }

    /**
     * @param array<string, mixed> $pageRecord
     * @return array<string, string>
     */
    private function confirmationAttributes(array $pageRecord): array
    {
        $languageService = $this->getLanguageService();

        $attributes = [
            'data-confirm' => '1',
            'data-modal-title' => $languageService->sL(self::LANGUAGE_PREFIX . 'modal.title'),
            'data-modal-message' => sprintf($languageService->sL(self::LANGUAGE_PREFIX . 'modal.message'), (string)$pageRecord['title']),
            'data-modal-cancel' => $languageService->sL(self::LANGUAGE_PREFIX . 'modal.cancel'),
            'data-modal-switch' => $languageService->sL(self::LANGUAGE_PREFIX . 'modal.switch'),
        ];
        if ($this->folderDecisions->switchMode() === SwitchMode::AskOnce) {
            $attributes['data-remember'] = '1';
            $attributes['data-modal-remember-hint'] = $languageService->sL(self::LANGUAGE_PREFIX . 'modal.rememberHint');
        }

        return $attributes;
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
