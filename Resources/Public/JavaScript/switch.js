const trigger = document.getElementById('autoswitchtolistview');
if (trigger && trigger.dataset.module) {
  top.TYPO3.ModuleMenu.App.showModule(trigger.dataset.module, `id=${trigger.dataset.pageId}`);
}
