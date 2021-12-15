<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Component\Git\Status;

use Symfony\Component\String\UnicodeString;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class Conflict implements ConflictInterface
{
    protected UnmergedFileInterface $file;
    protected string $all;
    protected string $to;
    protected string $from;
    protected bool $resolved = false;

    public function __construct(UnmergedFileInterface $file, string $all, string $to, string $from)
    {
        $this->file = $file;
        $this->all = $all;
        $this->to = $to;
        $this->from = $from;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function takeFrom(): void
    {
        $this->resolve($this->from);
    }

    private function resolve(string $value): void
    {
        if (!$this->resolved) {
            $pathname = $this->file->getPathname();
            $contents = new UnicodeString($this->file->getContents());
            $resolved = (string) $contents->replace($this->all, $value);

            file_put_contents($pathname, $resolved);

            $this->resolved = true;
        }
    }

    public function takeTo(): void
    {
        $this->resolve($this->to);
    }

    public function set(string $value): void
    {
        $this->resolve($value);
    }

    public function isResolved(): bool
    {
        return $this->resolved;
    }
}
