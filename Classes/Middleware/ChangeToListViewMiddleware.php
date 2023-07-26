<?php

namespace GeorgRinger\Autoswitchlistview\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ChangeToListViewMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** $request TYPO3\CMS\Core\Http\ServerRequest */
        if (!isset($request->getQueryParams()['id'])) {
            return $handler->handle($request);
        }

        $id = $request->getQueryParams()['id'];

        if ($this->checkPage($id) && $this->checkRoute($request->getUri()->getPath())) {

            $backendUriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            /** @var \TYPO3\CMS\Core\Http\Uri $uri */
            $uri = $backendUriBuilder->buildUriFromRoute('web_list', ['id' => $id]);

            return new RedirectResponse(
                $uri->__toString(),
                301,
                ['X-Redirect-By' => 'TYPO3 Redirect - Autoswitch to list view']
            );
        }
        return $handler->handle($request);
    }

    private function checkPage(int $pageUid): bool
    {
        $page = BackendUtility::getRecord('pages', $pageUid, 'doktype');
        return class_exists(PageRepository::class) && $page['doktype'] === PageRepository::DOKTYPE_SYSFOLDER;
    }

    private function checkRoute(string $backendRoute): bool
    {
        return !str_contains($backendRoute, '/typo3/module/web/list');
    }
}

