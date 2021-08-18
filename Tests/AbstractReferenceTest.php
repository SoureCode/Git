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

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class AbstractReferenceTest extends AbstractGitTestCase
{
    public function testGetRepository(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $branch = $commit->branch('feature/foo');

        // Act
        $actual = $branch->getRepository();

        // Assert
        self::assertSame($repository, $actual);
    }

    public function testGetPath(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $branch = $commit->branch('feature/foo');

        // Act
        $actual = $branch->getPath();

        // Assert
        self::assertNotNull($actual);
        self::assertSame('refs/heads/feature/foo', (string) $actual);
    }

    public function testToString(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $branch = $commit->branch('feature/foo');

        // Act
        $actual = (string) $branch;

        // Assert
        self::assertSame('refs/heads/feature/foo', $actual);
    }

    public function testGetName(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $branch = $commit->branch('feature/foo');

        // Act
        $actual = $branch->getName();

        // Assert
        self::assertSame('refs/heads/feature/foo', $actual);
    }

    public function testGetShortName(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $branch = $commit->branch('feature/foo');

        // Act
        $actual = $branch->getShortName();

        // Assert
        self::assertSame('feature/foo', $actual);
    }

    public function testTag(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $branch = $commit->branch('feature/foo');

        // Act
        $branch->tag('v1.0.3');

        // Assert
        $tags = $branch->getCommit()->getTags();

        self::assertCount(1, $tags);
        self::assertSame('v1.0.3', $tags[0]->getShortName());

        $tags = $repository->getTags();

        self::assertCount(1, $tags);
        self::assertSame('v1.0.3', $tags[0]->getShortName());
    }

    public function testBranch(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $branch = $commit->branch('feature/foo');

        // Act
        $branch->branch('feature/bar');

        // Assert
        $branches = $branch->getCommit()->getBranches();

        self::assertCount(3, $branches);
        self::assertSame('feature/bar', $branches[0]->getShortName());

        $branches = $repository->getBranches();

        self::assertCount(3, $branches);
        self::assertSame('feature/bar', $branches[0]->getShortName());
    }
}
