<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class HintRenderer
{
    private const LANGUAGE_PREFIX = 'LLL:EXT:autoswitchtolistview/Resources/Private/Language/locallang.xlf:';

    public function renderStayHint(int $pageId): string
    {
        return $this->renderHint($pageId, 'hint.stay.message', [$this->forgetLink()]);
    }

    public function renderListModuleHint(int $pageId, bool $withForgetLink): string
    {
        $links = [$this->openPageModuleLink()];
        if ($withForgetLink) {
            $links[] = $this->forgetLink();
        }

        return $this->renderHint($pageId, 'hint.list.message', $links);
    }

    /**
     * @param list<string> $links
     */
    private function renderHint(int $pageId, string $messageKey, array $links): string
    {
        $languageService = $this->getLanguageService();
        $attributes = [
            'id' => 'autoswitchtolistview-hint',
            'class' => 'text-muted',
            'data-page-id' => (string)$pageId,
            'data-notification-title' => $languageService->sL(self::LANGUAGE_PREFIX . 'hint.notification.title'),
            'data-notification-message' => $languageService->sL(self::LANGUAGE_PREFIX . 'hint.notification.message'),
        ];

        return sprintf(
            '<p %s>%s %s</p>',
            GeneralUtility::implodeAttributes($attributes, true),
            htmlspecialchars($languageService->sL(self::LANGUAGE_PREFIX . $messageKey)),
            implode(' &middot; ', $links)
        );
    }

    private function openPageModuleLink(): string
    {
        return sprintf(
            '<a href="#" id="autoswitchtolistview-open-page-module">%s</a>',
            htmlspecialchars($this->getLanguageService()->sL(self::LANGUAGE_PREFIX . 'hint.openPageModule'))
        );
    }

    private function forgetLink(): string
    {
        return sprintf(
            '<a href="#" id="autoswitchtolistview-forget">%s</a>',
            htmlspecialchars($this->getLanguageService()->sL(self::LANGUAGE_PREFIX . 'hint.forget'))
        );
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
