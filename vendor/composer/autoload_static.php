<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6a6772df2521c6860a448e9115f2793a
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6a6772df2521c6860a448e9115f2793a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6a6772df2521c6860a448e9115f2793a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6a6772df2521c6860a448e9115f2793a::$classMap;

        }, null, ClassLoader::class);
    }
}
