import Persistent from '@typo3/backend/storage/persistent.js';
import Notification from '@typo3/backend/notification.js';
import DocumentService from '@typo3/core/document-service.js';

DocumentService.ready().then(() => {
  const hint = document.getElementById('autoswitchtolistview-hint');
  if (!hint) {
    return;
  }

  const forgetLink = document.getElementById('autoswitchtolistview-forget');
  if (forgetLink) {
    forgetLink.addEventListener('click', (event) => {
      event.preventDefault();
      Persistent.set(`autoswitchtolistview.folderDecision.${hint.dataset.pageId}`, '').then(() => {
        Notification.success(hint.dataset.notificationTitle, hint.dataset.notificationMessage);
        hint.remove();
      });
    });
  }

  const openPageModuleLink = document.getElementById('autoswitchtolistview-open-page-module');
  if (openPageModuleLink) {
    openPageModuleLink.addEventListener('click', (event) => {
      event.preventDefault();
      top.TYPO3.ModuleMenu.App.showModule('web_layout', `id=${hint.dataset.pageId}&tx_autoswitchtolistview_skip=1`);
    });
  }
});
