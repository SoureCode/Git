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

use SoureCode\Component\Git\Filesystem\AbstractFile;
use Symfony\Component\String\UnicodeString;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class UnmergedFile extends AbstractFile implements UnmergedFileInterface
{
    private string $indexStatus;

    private string $workTreeStatus;

    private int $stageOneFileMode;

    private int $stageTwoFileMode;

    private int $stageThreeFileMode;

    private int $workTreeFileMode;

    private string $stageOneObjectName;

    private string $stageTwoObjectName;

    private string $stageThreeObjectName;

    public function __construct(
        string $file,
        string $relativePath,
        string $relativePathname,
        string $indexStatus,
        string $workTreeStatus,
        int $stageOneFileMode,
        int $stageTwoFileMode,
        int $stageThreeFileMode,
        int $workTreeFileMode,
        string $stageOneObjectName,
        string $stageTwoObjectName,
        string $stageThreeObjectName
    ) {
        parent::__construct($file, $relativePath, $relativePathname);

        $this->indexStatus = $indexStatus;
        $this->workTreeStatus = $workTreeStatus;
        $this->stageOneFileMode = $stageOneFileMode;
        $this->stageTwoFileMode = $stageTwoFileMode;
        $this->stageThreeFileMode = $stageThreeFileMode;
        $this->workTreeFileMode = $workTreeFileMode;
        $this->stageOneObjectName = $stageOneObjectName;
        $this->stageTwoObjectName = $stageTwoObjectName;
        $this->stageThreeObjectName = $stageThreeObjectName;
    }

    public function getIndexStatus(): string
    {
        return $this->indexStatus;
    }

    public function getWorkTreeStatus(): string
    {
        return $this->workTreeStatus;
    }

    public function getStageOneFileMode(): int
    {
        return $this->stageOneFileMode;
    }

    public function getStageTwoFileMode(): int
    {
        return $this->stageTwoFileMode;
    }

    public function getStageThreeFileMode(): int
    {
        return $this->stageThreeFileMode;
    }

    public function getWorkTreeFileMode(): int
    {
        return $this->workTreeFileMode;
    }

    public function getStageOneObjectName(): string
    {
        return $this->stageOneObjectName;
    }

    public function getStageTwoObjectName(): string
    {
        return $this->stageTwoObjectName;
    }

    public function getStageThreeObjectName(): string
    {
        return $this->stageThreeObjectName;
    }

    public function getConflicts(): array
    {
        $expression = self::getConflictExpression();
        $contents = new UnicodeString($this->getContents());

        /**
         * @var list<array{0: string, from: string, to: string, fromcontent: string, tocontent: string}> $matches
         */
        $matches = $contents->match($expression, \PREG_SET_ORDER);
        $conflicts = [];

        foreach ($matches as $match) {
            $all = $match[0];
            $toContent = $match['tocontent'];
            $fromContent = $match['fromcontent'];

            $conflicts[] = new Conflict($this, $all, $toContent, $fromContent);
        }

        return $conflicts;
    }

    protected static function getConflictExpression(): string
    {
        $newline = '\r?\n';
        $startMarker = '<{7}';
        $to = '(?<to>.*?$)';
        $middleMarker = '={7}';
        $stopMarker = '>{7}';
        $from = '(?<from>.*?$)';

        return implode('', [
            '/^',
            $startMarker,
            ' ',
            $to,
            $newline,

            implode('', [
                '(?<tocontent>(?!',
                $middleMarker,
                $newline,
                ').*?)',
            ]),

            $newline,
            $middleMarker,
            $newline,

            implode('', [
                '(?<fromcontent>(?!',
                $stopMarker,
                ' ).*?)',
            ]),

            $newline,
            $stopMarker,
            ' ',
            $from,
            $newline,
            '/ms',
        ]);
    }
}
