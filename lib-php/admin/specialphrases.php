
<?php
@define('CONST_LibDir', dirname(dirname(__FILE__)));

require_once(CONST_LibDir.'/init-cmd.php');

loadSettings(getcwd());

(new \Nominatim\Shell(getSetting('NOMINATIM_TOOL')))
    ->addParams('special-phrases', '--import-from-wiki')
    ->run();
