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
class Hash implements Stringable
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function slice(int $offset, ?int $length): string
    {
        return substr($this->value, $offset, $length);
    }

    public function __toString()
    {
        return $this->value;
    }
}
