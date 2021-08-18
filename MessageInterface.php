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
interface MessageInterface extends Stringable
{
    public function getSubject(): string;

    public function getBody(): string;
}
