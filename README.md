# TYPO3 extension `autoswitchtolistview`

Whenever you are in the page module and clicking on a sys folder in the page tree, you will be forwarded to list view automatically.

**Requirements**

- TYPO3 12 (use 3.x) and older versions use version 2.x

## Installation

1) Install extension. Use either the extension manager or do a `composer require georgringer/autoswitchtolistview`.
2) You are done.

#### Configuration

You can disable the behaviour by using the following code in Page TsConfig: `autoswitchtolistview.disable = 1`

The extension configuration (*Admin Tools > Settings > Extension Configuration > autoswitchtolistview*) offers a
`mode` setting with three options:

- **Switch immediately** (default): switch to the list module without asking.
- **Ask every time**: a confirmation modal is shown on every visit of a folder.
- **Ask once per folder and remember the decision**: the modal is shown once per folder, the decision is stored in
  the backend user settings and applied automatically on the next visit.

