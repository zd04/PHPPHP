<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit73b29ca81cb0af9b45d5a6ae2e199020
{
    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPParser' => 
            array (
                0 => __DIR__ . '/..' . '/nikic/php-parser/lib',
            ),
            'PHPPHP' => 
            array (
                0 => __DIR__ . '/../..' . '/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit73b29ca81cb0af9b45d5a6ae2e199020::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
