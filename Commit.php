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

use DateTimeImmutable;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Commit implements CommitInterface
{
    protected RepositoryInterface $repository;

    protected Hash $hash;

    protected Person $authoredBy;

    protected DateTimeImmutable $authoredAt;

    protected Person $committedBy;

    protected DateTimeImmutable $committedAt;

    protected MessageInterface $message;

    public function __construct(
        RepositoryInterface $repository,
        Hash $hash,
        Person $authoredBy,
        DateTimeImmutable $authoredAt,
        Person $committedBy,
        DateTimeImmutable $committedAt,
        MessageInterface $message,
    ) {
        $this->repository = $repository;
        $this->hash = $hash;
        $this->authoredBy = $authoredBy;
        $this->authoredAt = $authoredAt;
        $this->committedBy = $committedBy;
        $this->committedAt = $committedAt;
        $this->message = $message;
    }

    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function to(string $object): array
    {
        return $this->repository->list(
            sprintf('%s...%s', (string) $this->hash, $object),
            boundary: true,
            merges: false,
        );
    }

    public function tag(string $name): TagInterface
    {
        $this->repository->execute([
            'tag',
            $name,
            (string) $this->hash,
        ]);

        $path = ReferencePath::fromString('refs/tags/'.$name);

        return new Tag($this->repository, $path);
    }

    public function branch(string $name): BranchInterface
    {
        $this->repository->execute([
            'branch',
            $name,
            (string) $this->hash,
        ]);

        $path = ReferencePath::fromString('refs/heads/'.$name);

        return new Branch($this->repository, $path);
    }

    public function getTags(): array
    {
        $output = $this->repository->execute([
            'tag',
            '--list',
            '--points-at',
            (string) $this->hash,
            '--format=%(refname)',
        ]);

        $result = new ProcessResult($output);
        $tagReferences = $result->getOutputLines();
        $tags = [];

        foreach ($tagReferences as $reference) {
            $path = ReferencePath::fromString((string) $reference);

            $tags[] = new Tag($this->repository, $path);
        }

        return $tags;
    }

    public function getBranches(): array
    {
        $output = $this->repository->execute([
            'branch',
            '--list',
            '--points-at',
            (string) $this->hash,
            '--format=%(refname)',
        ]);

        $result = new ProcessResult($output);
        $branchReferences = $result->getOutputLines();
        $branches = [];

        foreach ($branchReferences as $reference) {
            $path = ReferencePath::fromString((string) $reference);

            $branches[] = new Branch($this->repository, $path);
        }

        return $branches;
    }

    public function __toString()
    {
        return (string) $this->hash;
    }

    #[ArrayShape([
        'hash' => 'string',
        'message' => 'array',
        'committedBy' => Person::class,
        'committedAt' => DateTimeImmutable::class,
        'authoredBy' => Person::class,
        'authoredAt' => DateTimeImmutable::class,
    ])]
    public function jsonSerialize(): array
    {
        return [
            'hash' => $this->getHash()->getValue(),
            'message' => [
                'subject' => $this->getMessage()->getSubject(),
                'body' => $this->getMessage()->getBody(),
            ],
            'committedBy' => $this->getCommittedBy(),
            'committedAt' => $this->getCommittedAt(),
            'authoredBy' => $this->getAuthoredBy(),
            'authoredAt' => $this->getAuthoredAt(),
        ];
    }

    public function getHash(): Hash
    {
        return $this->hash;
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function getCommittedBy(): Person
    {
        return $this->committedBy;
    }

    public function getCommittedAt(): DateTimeImmutable
    {
        return $this->committedAt;
    }

    public function getAuthoredBy(): Person
    {
        return $this->authoredBy;
    }

    public function getAuthoredAt(): DateTimeImmutable
    {
        return $this->authoredAt;
    }
}
