<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Component\Git\Filesystem;

use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractFile extends SplFileInfo implements FileInterface
{
    public const STATUS_ADDED = 'A';
    public const STATUS_COPIED = 'C';
    public const STATUS_DELETED = 'D';
    public const STATUS_MODIFIED = 'M';
    public const STATUS_RENAMED = 'R';
    public const STATUS_UNMODIFIED = '.';
    /**
     * Updated but unmerged.
     */
    public const STATUS_UPDATED = 'U';
}
