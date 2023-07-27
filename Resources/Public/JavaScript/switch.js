const el = document.getElementById('autoswitchtolistview');
if (el && el.dataset.uri) {
  parent.location.href = el.dataset.uri;
}
