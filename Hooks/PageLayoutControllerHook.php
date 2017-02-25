<?php

namespace GeorgRinger\Autoswitchlistview\Hooks;

use TYPO3\CMS\Backend\Module\ModuleLoader;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

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
        if ($parentObject->pageinfo['doktype'] == PageRepository::DOKTYPE_SYSFOLDER) {
            $moduleLoader = GeneralUtility::makeInstance(ModuleLoader::class);
            $moduleLoader->load($GLOBALS['TBE_MODULES']);
            $modules = $moduleLoader->modules;
            if (is_array($modules['web']['sub']['list'])) {
                $tsconfig = BackendUtility::getPagesTSconfig($parentObject->id);

                if (!(isset($tsconfig['autoswitchtolistview.']) && isset($tsconfig['autoswitchtolistview.']['disable']) && $tsconfig['autoswitchtolistview.']['disable'] == 1)) {
                    $out .= '<script type="text/javascript">top.goToModule(\'web_list\',1);</script>';
                }
            }
        }
        return $out;
    }

}

