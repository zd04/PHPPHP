<?php

error_reporting(E_ALL | E_STRICT);

require_once __DIR__ . '/vendor/autoload.php';

/**
 * 输出日志的
 * @param  [type] $msg [description]
 * @return [type]      [description]
 */
function loggerInfo($msg){
    echo $msg,PHP_EOL;
}

$php = new PHPPHP\PHP;

$php->registerExtensionByName('Shim'); // This *MUST* be the last core extension to be loaded!!!

list($options, $args) = parseCliArgs($argv);

define("DEBUG",true);
//调试模式的
if(isset($options['d'])){
    defined("DEBUG") or define("DEBUG",true);
}else{
    defined("DEBUG") or define("DEBUG",false);
}

if (isset($options['v'])) {
    echo "PHPPHP - Dev Master\n";
} elseif (isset($options['f'])) {
    $php->executeFile(realpath($options['f']));
} elseif (isset($options['r'])) {
    $php->setCWD(getcwd());
    if(isset($options['c'])){
        $php->compile('<?php ' . $options['r']);
    }else{
        $php->execute('<?php ' . $options['r']);
    }
} elseif (isset($args[0])) {
    if(isset($options['c'])){
        $php->compileFile(realpath($args[0]));
    }else{
        $php->executeFile(realpath($args[0]));
    }
} else {
    echo "Invalid arguments\n";
}

function parseCliArgs(array $args) {
    // first element is script name
    array_shift($args);

    $options = array();
    $arguments = array();

    $currentOption = null;
    foreach ($args as $arg) {
        if (strlen($arg) == 2 && $arg[0] == '-') {
            if ($currentOption) {
                $options[$currentOption] = '';
            }
            $currentOption = $arg[1];
        } elseif ($currentOption) {
            $options[$currentOption] = $arg;
            $currentOption = null;
        } else {
            $arguments[] = $arg;
        }
    }

    if ($currentOption) {
        $options[$currentOption] = '';
    }

    return array($options, $arguments);
}