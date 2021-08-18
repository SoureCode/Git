<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Component\Git\Test;

use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
trait GitTestTrait
{
    protected static function gitCreateFile(
        string $filename = null,
        string $content = null,
        array $subDirectories = []
    ): SplFileInfo {
        $subDirectories = !empty($subDirectories) ? $subDirectories : [];
        $relativePath = implode('/', $subDirectories);
        $absolutePath = implode('/', [self::$directory, $relativePath]);

        $fileInfo = self::createFile($filename, $content, $absolutePath);
        $file = $fileInfo->getPathname();
        $filename = $fileInfo->getFilename();

        return new SplFileInfo($file, $relativePath, $relativePath.'/'.$filename);
    }

    protected static function gitMoveFile(string $filename, string $newFilename): void
    {
        self::moveFile(self::$directory.'/'.$filename, self::$directory.'/'.$newFilename);
    }

    protected static function gitRemoveFile(string $filename): void
    {
        self::removeFile(self::$directory.'/'.$filename);
    }
}
