import Modal from '@typo3/backend/modal.js';
import { SeverityEnum } from '@typo3/backend/enum/severity.js';
import Persistent from '@typo3/backend/storage/persistent.js';
import DocumentService from '@typo3/core/document-service.js';

const switchToListModule = (trigger) => {
  top.TYPO3.ModuleMenu.App.showModule(trigger.dataset.module, `id=${trigger.dataset.pageId}`);
};

const rememberDecision = (trigger, decision) => trigger.dataset.remember === '1'
  ? Persistent.set(`autoswitchtolistview.folderDecision.${trigger.dataset.pageId}`, decision)
  : Promise.resolve();

const buildModalContent = (trigger) => {
  const content = document.createElement('div');

  const message = document.createElement('p');
  message.textContent = trigger.dataset.modalMessage;
  content.appendChild(message);

  if (trigger.dataset.remember === '1') {
    const rememberHint = document.createElement('p');
    rememberHint.classList.add('text-muted');
    rememberHint.textContent = trigger.dataset.modalRememberHint;
    content.appendChild(rememberHint);
  }

  return content;
};

const askBeforeSwitching = (trigger) => {
  Modal.confirm(trigger.dataset.modalTitle, buildModalContent(trigger), SeverityEnum.info, [
    {
      text: trigger.dataset.modalCancel,
      active: true,
      btnClass: 'btn-default',
      name: 'stay',
      trigger: (event, modal) => {
        rememberDecision(trigger, 'stay');
        modal.hideModal();
      },
    },
    {
      text: trigger.dataset.modalSwitch,
      btnClass: 'btn-primary',
      name: 'switch',
      trigger: (event, modal) => {
        modal.hideModal();
        rememberDecision(trigger, 'switch').finally(() => switchToListModule(trigger));
      },
    },
  ]);
};

DocumentService.ready().then(() => {
  const trigger = document.getElementById('autoswitchtolistview');
  if (!trigger || !trigger.dataset.module) {
    return;
  }

  if (trigger.dataset.confirm === '1') {
    askBeforeSwitching(trigger);
  } else {
    switchToListModule(trigger);
  }
});
