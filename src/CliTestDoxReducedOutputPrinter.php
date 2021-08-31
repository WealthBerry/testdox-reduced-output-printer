<?php
declare(strict_types=1);
namespace Wealthberry\TestDox;

use PHPUnit\Framework\TestResult;
use PHPUnit\Util\TestDox\CliTestDoxPrinter;
use Throwable;

class CliTestDoxReducedOutputPrinter extends CliTestDoxPrinter
{
    const MAX_LINE_LENGTH = 100;
    const MAX_LINES = 2;

    protected function formatThrowable(Throwable $t, ?int $status = null): string
    {
        $lines = explode("\n", parent::formatThrowable($t, $status));
        $originalLines = count($lines);
        if ($originalLines > self::MAX_LINES) {
            $lines = array_slice($lines, 0,self::MAX_LINES);
            $lines[] = '(' . $originalLines - self::MAX_LINES . ' more lines...)';
        }

        return implode(
            "\n",
            array_map(
                function ($lineMessage) {
                    return strlen($lineMessage) > self::MAX_LINE_LENGTH ?
                        substr($lineMessage, 0, self::MAX_LINE_LENGTH) . '(...)' :
                        $lineMessage;
                }, $lines
            )
        );
    }

    public function printResult(TestResult $result): void
    {
        $this->printHeader($result);
        $this->printFooter($result);
    }
}