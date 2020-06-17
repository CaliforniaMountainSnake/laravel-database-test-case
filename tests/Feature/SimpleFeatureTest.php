<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class SimpleFeatureTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testTest(): void
    {
        $this->assertTrue(true);
    }
}
