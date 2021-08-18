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
use SoureCode\Component\Git\Status\UnmergedFile;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Merge implements MergeInterface
{
    protected RepositoryInterface $repository;

    protected Branch $branch;

    public function __construct(RepositoryInterface $repository, Branch $branch)
    {
        $this->repository = $repository;
        $this->branch = $branch;
    }

    public function continue(): BranchInterface|MergeInterface
    {
        try {
            $this->repository->execute([
                'merge',
                '--continue',
            ], null, [
                'GIT_EDITOR' => ':',
            ]);
        } catch (ProcessFailedException $exception) {
            return $this;
        }

        return $this->branch;
    }

    /**
     * {@inheritDoc}
     */
    public function getConflicts(): array
    {
        $status = $this->repository->getStatus();

        return array_filter($status->getFiles(), static function (FileInterface $file) {
            return $file instanceof UnmergedFile;
        });
    }

    public function abort(): BranchInterface
    {
        $this->repository->execute([
            'merge',
            '--abort',
        ]);

        return $this->branch;
    }
}
