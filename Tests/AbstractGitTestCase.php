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

use PHPUnit\Framework\TestCase;
use SoureCode\Component\Git\Repository;
use SoureCode\Component\Git\RepositoryInterface;
use SoureCode\Component\Git\Test\FilesystemTestTrait;
use SoureCode\Component\Git\Test\GitTestTrait;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractGitTestCase extends TestCase
{
    use FilesystemTestTrait;
    use GitTestTrait;

    protected static ?string $directory = null;

    protected static ?RepositoryInterface $repository = null;

    protected function setUp(): void
    {
        self::$directory = self::createTemporaryDirectory('sourecode');
        self::$repository = Repository::init(self::$directory);
    }

    protected function tearDown(): void
    {
        self::$repository = null;
        self::removeDirectory(self::$directory);
        self::$directory = null;
    }
}
