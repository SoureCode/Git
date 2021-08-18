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

use SoureCode\Component\Git\Filesystem\FileInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface UnmergedFileInterface extends FileInterface
{
    public function getIndexStatus(): string;

    public function getWorkTreeStatus(): string;

    public function getStageOneFileMode(): int;

    public function getStageTwoFileMode(): int;

    public function getStageThreeFileMode(): int;

    public function getWorkTreeFileMode(): int;

    public function getStageOneObjectName(): string;

    public function getStageTwoObjectName(): string;

    public function getStageThreeObjectName(): string;

    /**
     * @return ConflictInterface[]
     */
    public function getConflicts(): array;
}
