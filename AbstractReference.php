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

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractReference implements ReferenceInterface
{
    protected RepositoryInterface $repository;

    protected ReferencePath $path;

    public function __construct(RepositoryInterface $repository, ReferencePath $path)
    {
        $this->repository = $repository;
        $this->path = $path;
    }

    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function getPath(): ReferencePath
    {
        return $this->path;
    }

    public function getName(): string
    {
        return (string) $this->path;
    }

    abstract public function getShortName(): string;

    public function getCommit(): CommitInterface
    {
        return $this->repository->getCommit((string) $this->path);
    }

    public function checkout(): self
    {
        $this->repository->execute([
            'checkout',
            $this->getShortName(),
        ]);

        return $this;
    }

    public function branch(string $name): BranchInterface
    {
        $this->repository->execute([
            'branch',
            $name,
            (string) $this->path,
        ]);

        $path = ReferencePath::fromString('refs/heads/'.$name);

        return new Branch($this->repository, $path);
    }

    public function tag(string $name): TagInterface
    {
        $this->repository->execute([
            'tag',
            $name,
            (string) $this->path,
        ]);

        $path = ReferencePath::fromString('refs/tags/'.$name);

        return new Tag($this->repository, $path);
    }

    public function __toString()
    {
        return (string) $this->path;
    }
}
