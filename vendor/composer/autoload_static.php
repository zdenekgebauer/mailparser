<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbf5047c038a7f200db7b5eebc1027d96
{
    public static $prefixLengthsPsr4 = array (
        'Z' => 
        array (
            'ZdenekGebauer\\MailParser\\' => 25,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ZdenekGebauer\\MailParser\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbf5047c038a7f200db7b5eebc1027d96::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbf5047c038a7f200db7b5eebc1027d96::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}