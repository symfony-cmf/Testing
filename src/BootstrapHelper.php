<?php

namespace Symfony\Cmf\Component\Testing;

/**
 * Helper used for locating distribution
 * files.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class BootstrapHelper
{
    public static function getDirectory()
    {
        return __DIR__;
    }

    public static function getDistFile($relativePath)
    {
        return sprintf('%s/%s', self::getDirectory(), $relativePath);
    }
}
