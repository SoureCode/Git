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
use SoureCode\Component\Git\Status\UntrackedFile;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Stage implements StageInterface
{
    private RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function add(string $file, bool $force = false): self
    {
        $this->repository->execute(['add', '--force' => $force, $file]);

        return $this;
    }

    public function remove(string $file): self
    {
        $this->repository->execute(['reset', $file]);

        return $this;
    }

    public function commit(string $message): CommitInterface
    {
        $process = $this->repository->execute(['commit', '--no-status', '-F', '-'], $message);
        $result = new ProcessResult($process);
        $commitHash = $result->getCommitOutput();

        return $this->repository->getCommit($commitHash);
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        $status = $this->repository->getStatus();
        $files = $status->getFiles();

        return array_filter($files, static function (FileInterface $file) {
            if ($file instanceof UntrackedFile) {
                return false;
            }

            // @todo status does not list ignored files?
            // if ($file instanceof IgnoredFile) {
            //     return false;
            // }

            // @todo check what is it about, might be with merge identifiers.
            //if ($file instanceof UnmergedFile) {
            //    return false;
            //}

            return true;
        });
    }

    public function addAll(): self
    {
        $this->repository->execute(['add', '--all']);

        return $this;
    }
}
