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
use Stringable;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ReferencePath implements Stringable
{
    /**
     * @var string[]
     */
    protected array $items;

    /**
     * @param string[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    #[Pure]
    public static function fromString(string $referenceName): self
    {
        return new self(explode('/', $referenceName));
    }

    #[Pure]
    public function slice(int $offset, int $length = null): self
    {
        return new self(\array_slice($this->items, $offset, $length));
    }

    public function __toString()
    {
        return implode('/', $this->items);
    }
}
