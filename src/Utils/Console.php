<?php

namespace Cuakx\Core\Utils;

use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @author https://github.com/CuaMcCarsaree44
 * @since  July, 7/24/2021 03:45:25
 *
 * A static utility to write formatted, timestamped messages to the Artisan
 * console. Simplifies console output by wrapping Symfony's ConsoleOutput
 * with typed log levels and automatic timestamps.
 */
class Console
{
    /**
     * Writes a formatted log line to the console output.
     *
     * Output format: [LOG_TYPE][YYYY-MM-DD HH:MM:SS] message
     *
     * @param string      $message The message to output.
     * @param string|null $type    Log level shorthand:
     *                             'e'   = ERROR
     *                             'w'   = WARNING
     *                             'v'   = VERBOSE
     *                             'i'   = INFO
     *                             'd'   = DEBUG
     *                             'wtf' = WTF
     *                             Any other value (or null) defaults to LOG.
     */
    public static function writeLine(string $message, ?string $type = null): void
    {
        $logType = match ($type) {
            'e'     => 'ERROR',
            'w'     => 'WARNING',
            'v'     => 'VERBOSE',
            'i'     => 'INFO',
            'd'     => 'DEBUG',
            'wtf'   => 'WTF',
            default => 'LOG',
        };

        $currentEpoch = date('Y-m-d H:i:s');

        $console = new ConsoleOutput();
        $console->writeln("[{$logType}][{$currentEpoch}] {$message}");
    }
}
