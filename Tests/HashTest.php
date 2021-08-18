<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Component\Git\Tests;

use PHPUnit\Framework\TestCase;
use SoureCode\Component\Git\Hash;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class HashTest extends TestCase
{
    public function testGetValue(): void
    {
        // Arrange
        $hash = new Hash('d75896845cd9ad75bb498a872c6f8fba8a7352ea');

        // Act
        $actual = $hash->getValue();

        // Assert
        self::assertSame('d75896845cd9ad75bb498a872c6f8fba8a7352ea', $actual);
    }

    public function testSlice(): void
    {
        // Arrange
        $hash = new Hash('d75896845cd9ad75bb498a872c6f8fba8a7352ea');

        // Act
        $actual = $hash->slice(0, 7);

        // Assert
        self::assertSame('d758968', $actual);
    }

    public function testToString(): void
    {
        // Arrange
        $hash = new Hash('d75896845cd9ad75bb498a872c6f8fba8a7352ea');

        // Act
        $actual = (string) $hash;

        // Assert
        self::assertSame('d75896845cd9ad75bb498a872c6f8fba8a7352ea', $actual);
    }
}
