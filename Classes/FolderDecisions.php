<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

final readonly class FolderDecisions
{
    public function __construct(private ExtensionConfiguration $extensionConfiguration)
    {
    }

    public function switchMode(): SwitchMode
    {
        try {
            $configuredMode = (string)$this->extensionConfiguration->get('autoswitchtolistview', 'mode');
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException) {
            return SwitchMode::Switch;
        }

        return SwitchMode::tryFrom($configuredMode) ?? SwitchMode::Switch;
    }

    public function decisionFor(int $pageId): ?FolderDecision
    {
        $decision = $this->getBackendUser()->uc['autoswitchtolistview']['folderDecision'][$pageId] ?? null;

        return is_string($decision) ? FolderDecision::tryFrom($decision) : null;
    }

    public function isDisabledByPageTsconfig(int $pageId): bool
    {
        $tsconfig = BackendUtility::getPagesTSconfig($pageId);

        return (int)($tsconfig['autoswitchtolistview.']['disable'] ?? 0) === 1;
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
