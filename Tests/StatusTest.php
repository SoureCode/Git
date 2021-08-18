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

use SoureCode\Component\Git\Repository;
use SoureCode\Component\Git\Status\IgnoredFileInterface;
use SoureCode\Component\Git\Status\OrdinaryChangedFileInterface;
use SoureCode\Component\Git\Status\RenamedFileInterface;
use SoureCode\Component\Git\Status\UntrackedFileInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class StatusTest extends AbstractGitTestCase
{
    public function testGetFiles(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);

        self::gitCreateFile('bar.txt', subDirectories: ['foo']);
        self::gitCreateFile('original.txt');
        self::gitCreateFile('base.js', "function bar(){\n    return 1;\n}");
        self::gitCreateFile('.gitignore', 'ignored-file.js');
        $stage = $repository->getStage();
        $stage->addAll()->commit('foo');

        self::gitMoveFile('original.txt', 'renamed.txt');
        self::gitCreateFile('base.js', "function bar(){\n    return 5;\n}");

        $repository->getStage()->addAll();
        self::gitCreateFile('ignored-file.js', "function bar(){\n    return 20;\n}");
        self::gitCreateFile('test.txt');

        // Act
        $files = $repository->getStatus()->getFiles();

        // Assert
        self::assertCount(4, $files);
        self::assertInstanceOf(OrdinaryChangedFileInterface::class, $files[0]);
        self::assertInstanceOf(RenamedFileInterface::class, $files[1]);
        self::assertInstanceOf(UntrackedFileInterface::class, $files[2]);
        self::assertInstanceOf(IgnoredFileInterface::class, $files[3]);
    }

    public function testGetBranchObjectName(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        self::gitCreateFile('base.js', "function bar(){\n    return 1;\n}");
        $stage = $repository->getStage();
        $stage->addAll()->commit('foo');

        // Act
        $objectName = $repository->getStatus()->getBranchObjectName();

        // Assert
        self::assertSame(40, \strlen($objectName));
    }

    public function testGetBranchHead(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        self::gitCreateFile('base.js', "function bar(){\n    return 1;\n}");
        $stage = $repository->getStage();
        $stage->addAll()->commit('foo');

        // Act
        $head = $repository->getStatus()->getBranchHead();

        // Assert
        self::assertSame('master', $head);
    }
}
