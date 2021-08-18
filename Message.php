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
class Message implements MessageInterface
{
    protected string $subject;

    protected string $body;

    public function __construct(string $subject, string $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function __toString()
    {
        if (empty($this->body)) {
            return $this->subject;
        }

        return sprintf("%s\n%s", $this->subject, $this->body);
    }
}
