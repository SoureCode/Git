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

use SoureCode\Component\Git\Status\UnmergedFileInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface MergeInterface
{
    /**
     * Continues the merge process, returns the merge instance on conflicts otherwise the branch the merge started from.
     *
     * @see https://git-scm.com/docs/git-merge#Documentation/git-merge.txt---continue
     */
    public function continue(): BranchInterface|MergeInterface;

    /**
     * Returns all conflicting files.
     *
     * @return UnmergedFileInterface[]
     */
    public function getConflicts(): array;

    /**
     * Abort the current rebase process.
     *
     * @see https://git-scm.com/docs/git-merge#Documentation/git-merge.txt---abort
     */
    public function abort(): BranchInterface;
}
