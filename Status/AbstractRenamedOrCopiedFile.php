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

use const PATHINFO_FILENAME;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractRenamedOrCopiedFile extends OrdinaryChangedAbstractFile implements RenamedOrCopiedFileInterface
{
    private int $score;
    private string $originalFile;
    private string $originalRelativePath;
    private string $originalRelativePathname;

    public function __construct(
        string $file,
        string $relativePath,
        string $relativePathname,
        string $originalFile,
        string $originalRelativePath,
        string $originalRelativePathname,
        string $indexStatus,
        string $workTreeStatus,
        int $headFileMode,
        int $indexFileMode,
        int $workTreeFileMode,
        string $headObjectName,
        string $indexObjectName,
        int $score,
    ) {
        parent::__construct(
            $file,
            $relativePath,
            $relativePathname,
            $indexStatus,
            $workTreeStatus,
            $headFileMode,
            $indexFileMode,
            $workTreeFileMode,
            $headObjectName,
            $indexObjectName
        );

        $this->score = $score;
        $this->originalFile = $originalFile;
        $this->originalRelativePath = $originalRelativePath;
        $this->originalRelativePathname = $originalRelativePathname;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getOriginalPath(): string
    {
        return \dirname($this->originalFile);
    }

    public function getOriginalRelativePath(): string
    {
        return $this->originalRelativePath;
    }

    public function getOriginalPathname(): string
    {
        return $this->originalFile;
    }

    public function getOriginalRelativePathname(): string
    {
        return $this->originalRelativePathname;
    }

    public function getOriginalBasename(): string
    {
        return basename($this->originalFile);
    }

    public function getOriginalFilenameWithoutExtension(): string
    {
        $filename = $this->getOriginalFilename();

        return pathinfo($filename, PATHINFO_FILENAME);
    }

    public function getOriginalFilename(): string
    {
        return basename($this->originalFile);
    }
}
