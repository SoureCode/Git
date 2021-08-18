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

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface RenamedOrCopiedFileInterface extends OrdinaryChangedFileInterface
{
    public function getScore(): int;

    public function getOriginalPath(): string;

    public function getOriginalRelativePath(): string;

    public function getOriginalFilename(): string;

    public function getOriginalPathname(): string;

    public function getOriginalRelativePathname(): string;

    public function getOriginalBasename(): string;

    public function getOriginalFilenameWithoutExtension(): string;
}
