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
class Tag extends AbstractReference implements TagInterface
{
    public function getShortName(): string
    {
        return (string) $this->path->slice(2);
    }
}
