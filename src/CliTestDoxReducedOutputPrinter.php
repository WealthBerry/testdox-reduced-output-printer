<?php
declare(strict_types = 1);

namespace Wealthberry\TestDox;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use Throwable;

class CliTestDoxReducedOutputPrinter extends CliTestDoxPrinter
{
    private const int MAX_LINE_LENGTH = 200;
    private const int MAX_LINES       = 150;

    private ?string $tempFile       = null;
    private mixed   $tempFileHandle = null;
    private int     $failureCount   = 0;

    public function __construct(
        $out = null,
        bool $verbose = false,
        string $colors = self::COLOR_DEFAULT,
        bool $debug = false,
        $numberOfColumns = 80,
        bool $reverse = false,
    )
    {
        $this->initTempFile();
        parent::__construct($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);
    }

    public function __destruct()
    {
        $this->cleanupTempFile();
    }

    public function addFailure(\PHPUnit\Framework\Test $test, AssertionFailedError $e, float $time): void
    {
        $this->writeFailureToFile($test, $e, $time);
        parent::addFailure($test, $e, $time);
    }

    public function addError(\PHPUnit\Framework\Test $test, Throwable $t, float $time): void
    {
        $this->writeFailureToFile($test, $t, $time);
        parent::addError($test, $t, $time);
    }

    public function printResult(TestResult $result): void
    {
        $this->printHeader($result);
        $this->printFailedTests();
        $this->printFooter($result);
    }

    protected function formatThrowable(Throwable $t, ?int $status = null): string
    {
        $lines = explode("\n", parent::formatThrowable($t, $status));
        $originalLines = count($lines);
        if ($originalLines > self::MAX_LINES) {
            $lines = array_slice($lines, 0, self::MAX_LINES);
            $lines[] = '(' . $originalLines - self::MAX_LINES . ' more lines...)';
        }

        return implode(
            "\n",
            array_map(
                function (string $lineMessage): string {
                    return strlen($lineMessage) > self::MAX_LINE_LENGTH ?
                        substr($lineMessage, 0, self::MAX_LINE_LENGTH) . '(...)' :
                        $lineMessage;
                }, $lines,
            ),
        );
    }

    private function initTempFile(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'phpunit_failures_');
        $this->tempFileHandle = fopen($this->tempFile, 'w');
    }

    private function printFailedTests(): void
    {
        ;
        if ($this->failureCount === 0) {
            return;
        }

        if ($this->tempFileHandle) {
            fclose($this->tempFileHandle);
        }

        $this->write("\n\nFailed Tests Summary:\n");
        $this->write(str_repeat('=', 80) . "\n");

        $readHandle = fopen($this->tempFile, 'r');
        $index = 1;

        while (($line = fgets($readHandle)) !== false) {
            $failureData = json_decode(trim($line), true);

            $this->write(sprintf(
                "\n%d) %s::%s\n",
                $index++,
                $failureData['class'],
                $failureData['method'],
            ));
            $this->write($failureData['exception'] . "\n");
        }

        fclose($readHandle);
        $this->cleanupTempFile();
    }

    private function cleanupTempFile(): void
    {
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    private function writeFailureToFile(\PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        // Skip non-error/failure issues
        if ($e instanceof SkippedTestError ||
            $e instanceof IncompleteTestError ||
            $e instanceof RiskyTestError ||
            $e instanceof Warning
        ) {
            return;
        }

        if ($this->tempFileHandle) {
            $failureData = [
                'class'     => get_class($test),
                'method'    => $test->getName(),
                'exception' => $this->formatThrowable($e),
                'time'      => $time,
            ];

            fwrite($this->tempFileHandle, json_encode($failureData) . "\n");
            $this->failureCount++;
        }
    }

}