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

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class OrdinaryChangedAbstractFile extends AbstractFile implements OrdinaryChangedFileInterface
{
    protected string $indexStatus;

    protected string $workTreeStatus;

    protected int $headFileMode;

    protected int $indexFileMode;

    protected int $workTreeFileMode;

    protected string $headObjectName;

    protected string $indexObjectName;

    public function __construct(
        string $file,
        string $relativePath,
        string $relativePathname,
        string $indexStatus,
        string $workTreeStatus,
        int $headFileMode,
        int $indexFileMode,
        int $workTreeFileMode,
        string $headObjectName,
        string $indexObjectName
    ) {
        parent::__construct($file, $relativePath, $relativePathname);

        $this->indexStatus = $indexStatus;
        $this->workTreeStatus = $workTreeStatus;
        $this->headFileMode = $headFileMode;
        $this->indexFileMode = $indexFileMode;
        $this->workTreeFileMode = $workTreeFileMode;
        $this->headObjectName = $headObjectName;
        $this->indexObjectName = $indexObjectName;
    }

    public function getIndexStatus(): string
    {
        return $this->indexStatus;
    }

    public function getWorkTreeStatus(): string
    {
        return $this->workTreeStatus;
    }

    public function getHeadFileMode(): int
    {
        return $this->headFileMode;
    }

    public function getIndexFileMode(): int
    {
        return $this->indexFileMode;
    }

    public function getWorkTreeFileMode(): int
    {
        return $this->workTreeFileMode;
    }

    public function getHeadObjectName(): string
    {
        return $this->headObjectName;
    }

    public function getIndexObjectName(): string
    {
        return $this->indexObjectName;
    }
}
