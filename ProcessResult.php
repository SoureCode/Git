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

use InvalidArgumentException;
use Symfony\Component\Process\Process;
use Symfony\Component\String\AbstractString;
use Symfony\Component\String\UnicodeString;

/**
 * @internal
 *
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ProcessResult
{
    private UnicodeString $output;

    public function __construct(Process $process)
    {
        $this->output = new UnicodeString($process->getOutput());
    }

    /**
     * @return list<AbstractString>
     */
    public function getRevListOutput(): array
    {
        $lines = $this->getOutputLines();

        $lines = array_filter(
            array_map(static function (AbstractString $line) {
                return $line->replace('commit -', '')->replace('commit ', '');
            }, $lines),
            static function (AbstractString $line) {
                return $line->length() > 0;
            }
        );

        return array_values(array_unique($lines));
    }

    /**
     * @return list<AbstractString>
     */
    public function getOutputLines(): array
    {
        return $this->getLines($this->getNormalizedOutput());
    }

    /**
     * @return list<AbstractString>
     */
    private function getLines(AbstractString $string): array
    {
        return array_values($string->split("\n"));
    }

    public function getNormalizedOutput(): AbstractString
    {
        return $this->getNormalized($this->output);
    }

    private function getNormalized(AbstractString $string): AbstractString
    {
        return $string->trim()->replaceMatches("/\r?\n/", "\n");
    }

    public function getCommitOutput(): string
    {
        /**
         * @var array<int, string> $matches
         */
        $matches = $this->output->match("/^\[.*?\s+([A-Fa-f0-9]+)\]/m");

        if (0 === \count($matches)) {
            throw new InvalidArgumentException('Could not parse commit output.');
        }

        return $matches[1];
    }
}
