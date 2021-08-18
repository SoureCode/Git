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

use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
trait FilesystemTestTrait
{
    private static ?Filesystem $filesystem = null;

    protected static function createTemporaryDirectory(string $prefix): string
    {
        $filesystem = self::getFilesystem();
        $temporaryDirectory = self::getTemporaryDirectory().'/'.self::createTemporaryName($prefix);

        $filesystem->mkdir($temporaryDirectory);

        return $temporaryDirectory;
    }

    protected static function getFilesystem(): Filesystem
    {
        if (null === self::$filesystem) {
            self::$filesystem = new Filesystem();
        }

        return self::$filesystem;
    }

    protected static function getTemporaryDirectory(): string
    {
        return realpath(sys_get_temp_dir());
    }

    protected static function createTemporaryName(string $prefix): string
    {
        return uniqid($prefix, true);
    }

    protected static function createFile(
        string $filename = null,
        string $content = null,
        string $directory = null
    ): SplFileInfo {
        $filename = $filename ?? self::createTemporaryName('file');
        $content = $content ?? self::createTemporaryName('content');
        $directory = $directory ?? self::getTemporaryDirectory();

        $file = $directory.'/'.$filename;

        $filesystem = self::getFilesystem();
        $filesystem->dumpFile($file, $content);

        return new SplFileInfo($file);
    }

    protected static function moveFile(string $filename, string $newFilename): void
    {
        $filesystem = self::getFilesystem();
        $filesystem->rename($filename, $newFilename);
    }

    protected static function removeFile(string $filename): void
    {
        self::removeDirectory($filename);
    }

    protected static function removeDirectory(string $directory): void
    {
        $filesystem = self::getFilesystem();

        if ($filesystem->exists($directory)) {
            $filesystem->remove($directory);
        }
    }
}
