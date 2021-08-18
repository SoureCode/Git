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

use SoureCode\Component\Git\Branch;
use SoureCode\Component\Git\Commit;
use SoureCode\Component\Git\Repository;
use SoureCode\Component\Git\Stage;
use SoureCode\Component\Git\Status;
use SoureCode\Component\Git\Tag;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class RepositoryTest extends AbstractGitTestCase
{
    public function testExecute(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);

        // Act
        $process = $repository->execute(['status']);

        // Assert
        $output = $process->getOutput();

        self::assertStringContainsString('No commits yet', $output);
    }

    public function testStatus(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);

        // Act
        $status = $repository->getStatus();

        // Assert
        self::assertInstanceOf(Status::class, $status);
    }

    public function testGetDirectory(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);

        // Act
        $directory = $repository->getDirectory();

        // Assert
        self::assertSame(self::$directory, $directory);
    }

    public function testStage(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);

        // Act
        $stage = $repository->getStage();

        // Assert
        self::assertInstanceOf(Stage::class, $stage);
    }

    public function testGetCommit(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');

        // Act
        $actual = $repository->getCommit($commit->getHash());

        // Assert
        self::assertEquals($actual, $commit);
    }

    public function testGetCommits(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();

        self::gitCreateFile();
        self::gitCreateFile();
        $stage->addAll()->commit('foo');
        self::gitCreateFile();
        self::gitCreateFile();
        $stage->addAll()->commit('bar');

        // Act
        $commits = $repository->getCommits();

        // Assert
        self::assertCount(2, $commits);
        self::assertInstanceOf(Commit::class, $commits[0]);
        self::assertInstanceOf(Commit::class, $commits[1]);
        self::assertSame('bar', (string) $commits[0]->getMessage());
        self::assertSame('foo', (string) $commits[1]->getMessage());
    }

    public function testGetBranches(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();

        self::gitCreateFile();
        self::gitCreateFile();
        $stage->addAll()->commit('foo')->branch('feature/x');
        self::gitCreateFile();
        self::gitCreateFile();
        $stage->addAll()->commit('bar')->branch('feature/y');

        // Act
        $branches = $repository->getBranches();

        // Assert
        self::assertCount(3, $branches);
        self::assertInstanceOf(Branch::class, $branches[0]);
        self::assertInstanceOf(Branch::class, $branches[1]);
        self::assertInstanceOf(Branch::class, $branches[2]);
        self::assertSame('feature/x', $branches[0]->getShortName());
        self::assertSame('feature/y', $branches[1]->getShortName());
        self::assertSame('master', $branches[2]->getShortName());
        self::assertSame('foo', (string) $branches[0]->getCommit()->getMessage());
        self::assertSame('bar', (string) $branches[1]->getCommit()->getMessage());
        self::assertSame('bar', (string) $branches[2]->getCommit()->getMessage());
    }

    public function testGetTags(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();

        self::gitCreateFile();
        self::gitCreateFile();
        $stage->addAll()->commit('foo')->tag('v1.0.2');
        self::gitCreateFile();
        self::gitCreateFile();
        $stage->addAll()->commit('bar')->tag('3.5.2');

        // Act
        $tags = $repository->getTags();

        // Assert
        self::assertCount(2, $tags);
        self::assertInstanceOf(Tag::class, $tags[0]);
        self::assertInstanceOf(Tag::class, $tags[1]);
        self::assertSame('3.5.2', $tags[0]->getShortName());
        self::assertSame('v1.0.2', $tags[1]->getShortName());
        self::assertSame('bar', (string) $tags[0]->getCommit()->getMessage());
        self::assertSame('foo', (string) $tags[1]->getCommit()->getMessage());
    }

    public function testResolveFile(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);

        self::gitCreateFile('foo.txt', 'lorem', ['bar']);

        // Act
        $file = $repository->resolveFile('bar/foo.txt');

        // Assert
        self::assertNotNull($file);
        self::assertSame('bar/foo.txt', $file->getRelativePathname());
        self::assertSame('bar', $file->getRelativePath());
        self::assertSame('foo.txt', $file->getBasename());
        self::assertSame('lorem', $file->getContents());
        self::assertSame(self::$directory.'/bar', $file->getPath());
        self::assertSame(self::$directory.'/bar/foo.txt', $file->getPathname());
    }
}
