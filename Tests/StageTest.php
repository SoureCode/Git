<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Component\Git\Tests;

use SoureCode\Component\Git\Filesystem\FileInterface;
use SoureCode\Component\Git\Repository;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class StageTest extends AbstractGitTestCase
{
    public function testAddAll(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        self::gitCreateFile(filename: 'bar.txt', subDirectories: ['foo']);
        self::gitCreateFile(filename: 'foo.txt');

        $files = self::resolveFiles($repository->getStage()->all());
        self::assertSame([], $files);

        // Act
        $repository->getStage()->addAll();

        // Assert
        $files = self::resolveFiles($repository->getStage()->all());

        self::assertSame(['foo.txt', 'foo/bar.txt'], $files);
    }

    protected static function resolveFiles(array $files): array
    {
        return array_map(static function (FileInterface $file) {
            return $file->getRelativePathname();
        }, $files);
    }

    public function testRemove(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        self::gitCreateFile(filename: 'bar.txt', subDirectories: ['foo']);
        self::gitCreateFile(filename: 'foo.txt');

        $repository->getStage()->addAll();
        $files = self::resolveFiles($repository->getStage()->all());

        self::assertSame(['foo.txt', 'foo/bar.txt'], $files);

        // Act
        $repository->getStage()->remove('foo/bar.txt');

        // Assert
        $files = self::resolveFiles($repository->getStage()->all());

        self::assertSame(['foo.txt'], $files);
    }

    public function testAll(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        self::gitCreateFile(filename: 'bar.txt', subDirectories: ['foo']);
        self::gitCreateFile(filename: 'foo.txt');
        $repository->getStage()->add('foo.txt');

        // Act
        $files = $repository->getStage()->all();

        // Assert
        self::assertSame(['foo.txt'], self::resolveFiles($files));
    }

    public function testAdd(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        self::gitCreateFile(filename: 'bar.txt', subDirectories: ['foo']);
        self::gitCreateFile(filename: 'foo.txt');

        $files = self::resolveFiles($repository->getStage()->all());
        self::assertSame([], $files);

        // Act
        $repository->getStage()->add('foo/bar.txt');

        // Assert
        $files = self::resolveFiles($repository->getStage()->all());

        self::assertSame(['foo/bar.txt'], $files);
    }

    public function testCommit(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        self::gitCreateFile();
        $stage->addAll();

        // Act
        $commit = $stage->commit('foo');

        // Assert
        $log = $repository->execute(['log'])->getOutput();

        self::assertStringContainsString('foo', $log);
        self::assertStringContainsString($commit->getHash(), $log);
    }
}
