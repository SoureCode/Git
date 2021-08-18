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
use SoureCode\Component\Git\Message;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class MessageTest extends TestCase
{
    public function testGetSubject(): void
    {
        // Arrange
        $message = new Message('foo', 'bar');

        // Act
        $actual = $message->getSubject();

        // Assert
        self::assertSame('foo', $actual);
    }

    public function testGetMessage(): void
    {
        // Arrange
        $message = new Message('foo', 'bar');

        // Act
        $actual = $message->getBody();

        // Assert
        self::assertSame('bar', $actual);
    }

    public function testToString(): void
    {
        // Arrange
        $message = new Message('foo', 'bar');

        // Act
        $actual = (string) $message;

        // Assert
        self::assertSame("foo\nbar", $actual);
    }
}
