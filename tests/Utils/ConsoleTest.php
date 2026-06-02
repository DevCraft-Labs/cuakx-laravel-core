<?php

namespace Cuakx\Core\Tests\Utils;

use Cuakx\Core\Utils\Console;
use PHPUnit\Framework\TestCase;

class ConsoleTest extends TestCase
{
    public function test_writeLine_does_not_throw(): void
    {
        // Console writes to stdout. We just assert it doesn't throw.
        $this->expectNotToPerformAssertions();
        Console::writeLine('test message');
    }

    public function test_writeLine_with_error_type(): void
    {
        $this->expectNotToPerformAssertions();
        Console::writeLine('something broke', 'e');
    }

    public function test_writeLine_with_warning_type(): void
    {
        $this->expectNotToPerformAssertions();
        Console::writeLine('heads up', 'w');
    }

    public function test_writeLine_with_info_type(): void
    {
        $this->expectNotToPerformAssertions();
        Console::writeLine('all good', 'i');
    }

    public function test_writeLine_with_debug_type(): void
    {
        $this->expectNotToPerformAssertions();
        Console::writeLine('debug detail', 'd');
    }

    public function test_writeLine_with_wtf_type(): void
    {
        $this->expectNotToPerformAssertions();
        Console::writeLine('truly unexpected', 'wtf');
    }

    public function test_writeLine_with_unknown_type_defaults_to_log(): void
    {
        $this->expectNotToPerformAssertions();
        Console::writeLine('some message', 'xyz');
    }

    public function test_writeLine_with_null_type(): void
    {
        $this->expectNotToPerformAssertions();
        Console::writeLine('no type given', null);
    }
}
