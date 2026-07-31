<?php

namespace Cuakx\Core\Tests\Utils;

use Cuakx\Core\Tests\TestCase;
use Cuakx\Core\Utils\Console;
use Illuminate\Support\Facades\Log;

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

    public function test_writeLaravelLog_defaults_to_info(): void
    {
        Log::spy();

        Console::writeLaravelLog('default level');

        Log::shouldHaveReceived('info')
            ->once()
            ->with('default level', []);
    }

    public function test_writeLaravelLog_maps_error_type(): void
    {
        Log::spy();

        Console::writeLaravelLog('something broke', 'e');

        Log::shouldHaveReceived('error')
            ->once()
            ->with('something broke', []);
    }

    public function test_writeLaravelLog_passes_context(): void
    {
        Log::spy();

        Console::writeLaravelLog('contexted', 'd', ['job_id' => 12]);

        Log::shouldHaveReceived('debug')
            ->once()
            ->with('contexted', ['job_id' => 12]);
    }
}
