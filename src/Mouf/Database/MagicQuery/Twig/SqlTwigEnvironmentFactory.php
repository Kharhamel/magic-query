<?php

namespace Mouf\Database\MagicQuery\Twig;

use Twig_Extension_Core;

/**
 * Class in charge of creating the Twig environment.
 */
class SqlTwigEnvironmentFactory
{
    private static $twig;

    public static function getTwigEnvironment()
    {
        if (self::$twig) {
            return self::$twig;
        }

        $stringLoader = new StringLoader();

        $options = array(
            // The cache directory is in the temporary directory and reproduces the path to the directory (to avoid cache conflict between apps).
            'cache' => self::getCacheDirectory(),
            'strict_variables' => true,
            'autoescape' => 'sql' // Default autoescape mode: sql
        );

        $twig = new \Twig_Environment($stringLoader, $options);

        // Default escaper will throw an exception. This is because we want to use SQL parameters instead of Twig.
        // This has a number of advantages, especially in terms of caching.

        /** @var Twig_Extension_Core $twigExtensionCore */
        $twigExtensionCore = $twig->getExtension('Twig_Extension_Core');
        $twigExtensionCore->setEscaper('sql', function () {
            throw new ForbiddenTwigParameterInSqlException('You cannot use Twig expressions (like "{{ id }}"). Instead, you should use SQL parameters (like ":id"). Twig integration is limited to Twig statements (like "{% for .... %}"');
        });

        self::$twig = $twig;

        return $twig;
    }

    private static function getCacheDirectory()
    {
        // If we are running on a Unix environment, let's prepend the cache with the user id of the PHP process.
        // This way, we can avoid rights conflicts.

        // @codeCoverageIgnoreStart
        if (function_exists('posix_geteuid')) {
            $posixGetuid = '_'.posix_geteuid();
        } else {
            $posixGetuid = '';
        }
        // @codeCoverageIgnoreEnd
        $cacheDirectory = rtrim(sys_get_temp_dir(), '/\\').'/magicquerysqltwigtemplate'.$posixGetuid.str_replace(':', '', __DIR__);

        return $cacheDirectory;
    }
}
