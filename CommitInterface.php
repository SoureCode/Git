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
use JsonSerializable;
use Stringable;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface CommitInterface extends Stringable, JsonSerializable
{
    public function getRepository(): RepositoryInterface;

    public function getHash(): Hash;

    public function getAuthoredBy(): Person;

    public function getAuthoredAt(): DateTimeImmutable;

    public function getCommittedBy(): Person;

    public function getCommittedAt(): DateTimeImmutable;

    public function getMessage(): MessageInterface;

    /**
     * @return list<CommitInterface>
     */
    public function to(string $object): array;

    public function branch(string $name): BranchInterface;

    public function tag(string $name): TagInterface;

    /**
     * @return TagInterface[]
     */
    public function getTags(): array;

    /**
     * @return BranchInterface[]
     */
    public function getBranches(): array;
}
