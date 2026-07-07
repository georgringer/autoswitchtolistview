<?php
declare(strict_types=1);

namespace GeorgRinger\Autoswitchlistview;

enum SwitchMode: string
{
    case Switch = 'switch';
    case Ask = 'ask';
    case AskOnce = 'askOnce';
}
