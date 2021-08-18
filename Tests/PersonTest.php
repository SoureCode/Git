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
use SoureCode\Component\Git\Person;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class PersonTest extends TestCase
{
    public function testGetName(): void
    {
        // Arrange
        $person = new Person('John Doe', 'john@doe.com');

        // Act
        $actual = $person->getName();

        // Assert
        self::assertSame('John Doe', $actual);
    }

    public function testGetMessage(): void
    {
        // Arrange
        $person = new Person('John Doe', 'john@doe.com');

        // Act
        $actual = $person->getEmail();

        // Assert
        self::assertSame('john@doe.com', $actual);
    }

    public function testToString(): void
    {
        // Arrange
        $person = new Person('John Doe', 'john@doe.com');

        // Act
        $actual = (string) $person;

        // Assert
        self::assertSame('John Doe <john@doe.com>', $actual);
    }
}
