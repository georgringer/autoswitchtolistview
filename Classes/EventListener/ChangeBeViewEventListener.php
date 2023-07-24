<?php

namespace GeorgRinger\Autoswitchlistview\EventListener;

use TYPO3\CMS\Backend\Backend\Event\ModifyClearCacheActionsEvent;
use \TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use TYPO3\CMS\Backend\Module\ModuleLoader;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ChangeBeViewEventListener
{
    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        \TYPO3\CMS\Core\Utility\DebugUtility::debug($event);
        die;

    }
}

