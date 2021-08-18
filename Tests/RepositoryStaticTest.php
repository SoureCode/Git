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

use SoureCode\Component\Git\Exception\InvalidArgumentException;
use SoureCode\Component\Git\Repository;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class RepositoryStaticTest extends AbstractGitTestCase
{
    /**
     * @dataProvider runDataProvider
     */
    public function testRun(array $commitMessages, array $parameters, bool $autorun, string $expectedNeedle): void
    {
        // Arrange
        $repository = Repository::init(self::$directory);

        if (\count($commitMessages) > 0) {
            $stage = $repository->getStage();

            foreach ($commitMessages as $commitMessage) {
                $file = self::gitCreateFile();
                $stage->add($file);

                $stage->commit($commitMessage);
            }
        }

        // Act
        $process = Repository::executeGit($parameters, self::$directory, $autorun);

        if (!$autorun) {
            $process->run();
        }

        // Assert
        $stdout = $process->getOutput();
        $stderr = $process->getErrorOutput();

        self::assertStringContainsString($expectedNeedle, $stdout.$stderr);
    }

    public function testOpen(): void
    {
        // Arrange
        Repository::init(self::$directory);

        // Act
        $repository = Repository::open(self::$directory);

        // Assert
        self::assertInstanceOf(Repository::class, $repository);
    }

    public function testOpenDirectoryNotExist(): void
    {
        self::expectExceptionMessageMatches('/does not exist/');

        // Act
        Repository::open(self::$directory.'/not-exist');
    }

    public function testInit(): void
    {
        // Act
        $repository = Repository::init(self::$directory);

        // Assert
        self::assertInstanceOf(Repository::class, $repository);
        self::assertDirectoryExists(self::$directory.'/.git');
    }

    public function testInitBare(): void
    {
        // Act
        $repository = Repository::init(self::$directory, true);

        // Assert
        self::assertInstanceOf(Repository::class, $repository);
        self::assertFileExists(self::$directory.'/HEAD');
    }

    public function testOpenBare(): void
    {
        // Arrange
        Repository::init(self::$directory, true);

        // Act
        $repository = Repository::open(self::$directory);

        // Assert
        self::assertInstanceOf(Repository::class, $repository);
        self::assertFileExists(self::$directory.'/HEAD');
    }

    public function testInitDirectoryNotExist(): void
    {
        self::expectExceptionMessageMatches('/does not exist/');

        // Act
        Repository::init(self::$directory.'/not-exist');
    }

    public function testExecuteGitWithInvalidParameter(): void
    {
        self::expectException(InvalidArgumentException::class);

        // Arrange
        $repository = Repository::init(self::$directory, true);

        // Act
        $repository->execute(['this' => 'is-invalid']);
    }

    public function runDataProvider(): array
    {
        return [
            [[], ['log'], false, 'does not have any commits yet'],
            [[], ['status'], true, 'No commits yet'],

            [['foo'], ['log'], false, 'foo'],
            [['foo'], ['status'], true, 'nothing to commit, working tree clean'],

            [['bar'], ['log'], false, 'bar'],
            [['foo', "bar\n\nYeet\nFoo"], ['status'], true, 'nothing to commit, working tree clean'],
        ];
    }
}
