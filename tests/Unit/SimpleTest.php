<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    /**
     * 简单的断言测试
     */
    public function test_basic_assertion(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);
        $this->assertEquals(1, 1);
        $this->assertNotEmpty('test');
    }

    /**
     * 数组测试
     */
    public function test_array_operations(): void
    {
        $array = [1, 2, 3, 4, 5];
        $this->assertCount(5, $array);
        $this->assertContains(3, $array);
        $this->assertEquals([1, 2, 3, 4, 5], $array);
    }

    /**
     * 字符串测试
     */
    public function test_string_operations(): void
    {
        $string = 'Hello, World!';
        $this->assertStringContains('Hello', $string);
        $this->assertStringStartsWith('Hello', $string);
        $this->assertStringEndsWith('!', $string);
        $this->assertEquals(13, strlen($string));
    }

    /**
     * 数值测试
     */
    public function test_numeric_operations(): void
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertEquals(10, 5 * 2);
        $this->assertTrue(10 > 5);
        $this->assertFalse(5 > 10);
    }

    /**
     * 空值测试
     */
    public function test_null_values(): void
    {
        $this->assertNull(null);
        $this->assertNotNull('not null');
        $this->assertEmpty('');
        $this->assertNotEmpty('not empty');
    }
}