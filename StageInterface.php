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

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface StageInterface
{
    public function add(string $file): self;

    public function remove(string $file): self;

    public function commit(string $message): CommitInterface;

    /**
     * @return FileInterface[]
     */
    public function all(): array;

    public function addAll(): self;
}
