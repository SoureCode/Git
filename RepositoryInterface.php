<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Component\Git;

use SoureCode\Component\Git\Filesystem\FileInterface;
use Symfony\Component\Process\Process;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface RepositoryInterface
{
    /**
     * @param array<array-key, string|bool> $parameters
     */
    public static function executeGit(
        array $parameters,
        string $directory,
        bool $autorun = true,
        string $input = null
    ): Process;

    public static function open(string $directory): self;

    public static function init(string $directory, bool $bare = false): self;

    public function getDirectory(): string;

    /**
     * @param array<array-key, string|bool> $parameters
     */
    public function execute(array $parameters, string $input = null, array $environment = []): Process;

    public function getStatus(): StatusInterface;

    public function getStage(): StageInterface;

    public function resolveFile(string $relativePathname): FileInterface;

    public function getCommit(string $object): CommitInterface;

    /**
     * @return list<CommitInterface>
     */
    public function list(string $commit, bool $boundary = false, bool $merges = true): array;

    /**
     * @return CommitInterface[]
     */
    public function getCommits(): array;

    /**
     * @return BranchInterface[]
     */
    public function getBranches(): array;

    public function getBranch(string $name): BranchInterface;

    /**
     * @return TagInterface[]
     */
    public function getTags(): array;

    public function getTag(string $name): TagInterface;
}
