<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview\EventListener;

use GeorgRinger\Autoswitchlistview\FolderDecision;
use GeorgRinger\Autoswitchlistview\SwitchMode;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageModuleListener
{
    private const LANGUAGE_PREFIX = 'LLL:EXT:autoswitchtolistview/Resources/Private/Language/locallang.xlf:';

    public function __construct(private readonly ExtensionConfiguration $extensionConfiguration)
    {
    }

    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $request = $event->getRequest();
        $pageId = (int)($request->getQueryParams()['id'] ?? 0);
        $pageRecord = BackendUtility::getRecord('pages', $pageId);

        if ($pageRecord === null || !$this->shouldSwitchToListModule($request, $pageRecord, $pageId)) {
            return;
        }

        $event->setFooterContent($this->buildTriggerMarkup($pageId, $pageRecord));
        GeneralUtility::makeInstance(PageRenderer::class)->loadJavaScriptModule('@georgringer/autoswitchtolistview/switch.js');
    }

    /**
     * @param array<string, mixed> $pageRecord
     */
    private function shouldSwitchToListModule(ServerRequestInterface $request, array $pageRecord, int $pageId): bool
    {
        if ($pageRecord['doktype'] !== PageRepository::DOKTYPE_SYSFOLDER) {
            return false;
        }
        if ($request->getUri()->getPath() !== '/typo3/module/web/layout') {
            return false;
        }
        if ($this->isNewsContentElementsRequest($request)) {
            return false;
        }
        if ($this->userDecidedToStay($pageId)) {
            return false;
        }

        return !$this->isDisabledByPageTsconfig($pageId);
    }

    private function isNewsContentElementsRequest(ServerRequestInterface $request): bool
    {
        return ExtensionManagementUtility::isLoaded('news_content_elements')
            && isset($request->getQueryParams()['tx_news_id']);
    }

    private function userDecidedToStay(int $pageId): bool
    {
        return $this->switchMode() === SwitchMode::AskOnce
            && $this->rememberedFolderDecision($pageId) === FolderDecision::Stay;
    }

    private function isDisabledByPageTsconfig(int $pageId): bool
    {
        $tsconfig = BackendUtility::getPagesTSconfig($pageId);

        return (int)($tsconfig['autoswitchtolistview.']['disable'] ?? 0) === 1;
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
        return match ($this->switchMode()) {
            SwitchMode::Switch => false,
            SwitchMode::Ask => true,
            SwitchMode::AskOnce => $this->rememberedFolderDecision($pageId) === null,
        };
    }

    private function switchMode(): SwitchMode
    {
        try {
            $configuredMode = (string)$this->extensionConfiguration->get('autoswitchtolistview', 'mode');
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException) {
            return SwitchMode::Switch;
        }

        return SwitchMode::tryFrom($configuredMode) ?? SwitchMode::Switch;
    }

    private function rememberedFolderDecision(int $pageId): ?FolderDecision
    {
        $decision = $this->getBackendUser()->uc['autoswitchtolistview']['folderDecision'][$pageId] ?? null;

        return is_string($decision) ? FolderDecision::tryFrom($decision) : null;
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
        if ($this->switchMode() === SwitchMode::AskOnce) {
            $attributes['data-remember'] = '1';
            $attributes['data-modal-remember-hint'] = $languageService->sL(self::LANGUAGE_PREFIX . 'modal.rememberHint');
        }

        return $attributes;
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
