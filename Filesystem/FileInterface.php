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

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
interface FileInterface
{
    /**
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getPathname();

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @return string
     */
    public function getBasename();

    /**
     * @return string
     */
    public function getRelativePath();

    /**
     * @return string
     */
    public function getRelativePathname();

    public function getFilenameWithoutExtension(): string;

    /**
     * @return string
     */
    public function getContents();

    /**
     * @return string
     */
    public function getExtension();
}
