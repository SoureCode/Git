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

use Stringable;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface ReferenceInterface extends Stringable
{
    public function getRepository(): RepositoryInterface;

    public function getPath(): ReferencePath;

    public function getName(): string;

    public function getShortName(): string;

    public function getCommit(): CommitInterface;

    public function tag(string $name): TagInterface;

    public function branch(string $name): BranchInterface;

    public function checkout(): self;
}
