<?php

namespace GeorgRinger\Autoswitchlistview\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ChangeToListViewMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        /** $request TYPO3\CMS\Core\Http\ServerRequest */
        if (isset($request->getQueryParams()['id'], $request->getQueryParams()['token'])) {
            $id = $request->getQueryParams()['id'];
            $token = $request->getQueryParams()['token'];
        } else {
            return $handler->handle($request);
        }

        if ($this->checkPage($id) && $this->checkRoute($request->getUri()->getPath())) {
             return  GeneralUtility::makeInstance(RedirectResponse::class, sprintf('/typo3/module/web/list?token=%s&id=%s', $token, $id));
        }
        return $handler->handle($request);
    }

    private function checkPage(int $pageUid): bool
    {
        $parentObject = BackendUtility::getRecord('pages', $pageUid, 'uid,pid,doktype');

        if (class_exists(PageRepository::class)) {
            $doktypeSysfolder = PageRepository::DOKTYPE_SYSFOLDER;

            if ($parentObject['doktype'] === $doktypeSysfolder) {
                return true;
            }
        }

       return false;
    }

    private function checkRoute(string $backendRoute): bool
    {
        return str_contains($backendRoute, '/typo3/module/web/layout');
    }
}

