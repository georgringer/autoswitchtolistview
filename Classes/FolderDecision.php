<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview;

enum FolderDecision: string
{
    case SwitchToList = 'switch';
    case Stay = 'stay';
}
