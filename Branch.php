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

use JetBrains\PhpStorm\Pure;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Branch extends AbstractReference implements BranchInterface
{
    #[Pure]
    public function getShortName(): string
    {
        return (string) $this->path->slice(2);
    }

    public function merge(string $branch): self|MergeInterface
    {
        $this->checkout();

        try {
            $this->repository->execute([
                'merge',
                '--no-edit',
                '--no-ff',
                $branch,
            ], null, [
                'GIT_EDITOR' => ':',
            ]);
        } catch (ProcessFailedException $exception) {
            return new Merge($this->repository, $this);
        }

        return $this;
    }
}
