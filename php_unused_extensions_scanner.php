<?php
/**
 * Command line utilite for searching loaded, but unused php extenstion in files of indicated directory.
 *
 * This can be useful if you want to speed up your scripts.
 * To do this, disable unused extensions in the configuration file of PHP.
 * Some extensions may not be called out of the code  but they may perfom service functions (xDebug, eAccelerator, APC, etc)
 * Check the list of extensions before  switching off
 *
 * Caution: The author does not guarantee 100% accuracy of the scanner work
 * Make sure your applications work after you disable extensions.
 *
 * What extension is used, can be defined as follows:
 *   - with the reflection of the extension are extracted the names of classes, functions and constants
 *   - these names are searched in files of the indicated directory
 * @version 0.1
 * @author Levin Pavel
 */
set_time_limit(0);
include_once "unused_extensions_scanner.class.php";
if(count($argv)!=2) {
  echo "\nUsage: php -f php_unused_extensions_scanner.php <Directory of php project(s)>\n\n";
  exit;
}
$app=new ExtensionsScanner();
$result=$app->findUnusedExts($argv[1]);
if(empty($result)) {
  echo "\nAll extensions are used\n\n";
}
else {
  echo "\nNames of unused extensions: ".implode(', ',$result)."\n\n";
}
