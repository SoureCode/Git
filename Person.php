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
class Person implements Stringable
{
    protected string $name;

    protected string $email;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function __toString()
    {
        return sprintf('%s <%s>', $this->name, $this->email);
    }
}
