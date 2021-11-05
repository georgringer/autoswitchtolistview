<?php

namespace GeorgRinger\Autoswitchlistview\Hooks;

use TYPO3\CMS\Backend\Module\ModuleLoader;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageLayoutControllerHook
{

    /**
     * @param array $configuration
     * @param $parentObject
     * @return string
     */
    public function render(array $configuration, $parentObject)
    {
        $out = '';

        // If doktype is type sysfolder
        if (class_exists(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class)) {
            $doktypeSysfolder = \TYPO3\CMS\Core\Domain\Repository\PageRepository::DOKTYPE_SYSFOLDER;
        } else {
            $doktypeSysfolder = \TYPO3\CMS\Frontend\Page\PageRepository::DOKTYPE_SYSFOLDER;
        }
        if ($parentObject->pageinfo['doktype'] === $doktypeSysfolder) {
            $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);
            $moduleLoader->load($GLOBALS['TBE_MODULES']);
            $modules = $moduleLoader->modules;
            if (\is_array($modules['web']['sub']['list'])) {
                $tsconfig = BackendUtility::getPagesTSconfig($parentObject->id);

                if (!(isset($tsconfig['autoswitchtolistview.']) && isset($tsconfig['autoswitchtolistview.']['disable']) && $tsconfig['autoswitchtolistview.']['disable'] == 1)) {
                    $out .= '<script>top.goToModule(\'web_list\',1);</script>';
                }
            }
        }
        return $out;
    }

}

