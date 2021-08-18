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
class CommitTest extends AbstractGitTestCase
{
    public function testGetRepository(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');

        // Act
        $actual = $commit->getRepository();

        // Assert
        self::assertSame($repository, $actual);
    }

    public function testGetMessage(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('bar');

        // Act
        $message = $commit->getMessage();

        // Assert
        self::assertSame('bar', (string) $message);
    }

    public function testGetHash(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('bar');

        // Act
        $actual = $commit->getHash();

        // Assert
        $foundCommit = $repository->getCommits()[0];
        self::assertEquals($foundCommit->getHash(), $actual);
    }

    public function testTo(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $stage->addAll()->commit('footer');
        self::gitCreateFile();
        $first = $stage->addAll()->commit('foo');
        self::gitCreateFile();
        $stage->addAll()->commit('bar');
        self::gitCreateFile();
        $stage->addAll()->commit('barter');
        self::gitCreateFile();
        $last = $stage->addAll()->commit('lorem');
        self::gitCreateFile();
        $stage->addAll()->commit('ipsum');

        // Act
        $commits = $repository->getCommit($first->getHash())->to($last->getHash());

        // Assert
        self::assertCount(4, $commits);
        self::assertSame('lorem', (string) $commits[0]->getMessage());
        self::assertSame('barter', (string) $commits[1]->getMessage());
        self::assertSame('bar', (string) $commits[2]->getMessage());
        self::assertSame('foo', (string) $commits[3]->getMessage());
    }

    public function testTag(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');

        // Act
        $commit->tag('v1.0.3');

        // Assert
        $tags = $commit->getTags();

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

        // Act
        $commit->branch('feature/bar');

        // Assert
        $branches = $commit->getBranches();

        self::assertCount(2, $branches); // +master branch
        self::assertSame('feature/bar', $branches[0]->getShortName());

        $branches = $repository->getBranches();

        self::assertCount(2, $branches); // +master branch
        self::assertSame('feature/bar', $branches[0]->getShortName());
    }

    public function testGetTags(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $commit->tag('v1.4.3');
        $commit->tag('hotfix/bar');

        // Act
        $tags = $commit->getTags();

        // Assert
        self::assertCount(2, $tags);
        self::assertSame('hotfix/bar', $tags[0]->getShortName());
        self::assertSame('v1.4.3', $tags[1]->getShortName());
    }

    public function testGetBranches(): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);
        $stage = $repository->getStage();
        self::gitCreateFile();
        $commit = $stage->addAll()->commit('foo');
        $commit->branch('feature/bar');
        $commit->branch('feature/foo');

        // Act
        $branches = $commit->getBranches();

        // Assert
        self::assertCount(3, $branches); // +master
        self::assertSame('feature/bar', $branches[0]->getShortName());
        self::assertSame('feature/foo', $branches[1]->getShortName());
    }
}
