<?php
declare(strict_types=1);
namespace Wealthberry\TestDox\Tests;

use PHPUnit\Framework\Error;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Wealthberry\TestDox\CliTestDoxReducedOutputPrinter;

class CliTestDoxReducedOutputPrinterTest extends TestCase
{
    private ReflectionMethod $formatThrowableMethod;

    public function setUp(): void
    {
        $reflectionClass = new ReflectionClass(CliTestDoxReducedOutputPrinter::class);
        $this->formatThrowableMethod = $reflectionClass->getMethod('formatThrowable');
        $this->formatThrowableMethod->setAccessible(true);
    }

    /**
     * @dataProvider provideTestErrorMessages
     */
    public function testFormatMessage(string $providedMessage, string $expectedMessage)
    {
        $formattedMessage = $this->formatThrowableMethod->invoke(new CliTestDoxReducedOutputPrinter(), new Error($providedMessage));
        $this->assertSame($expectedMessage, $formattedMessage);
    }

    public function provideTestErrorMessages(): array
    {
        $cases = [];

        $cases['No change in small messages'] = [
            'providedMessage' => 'abc',
            'expectedMessage' => 'abc'
        ];

        $message = rtrim(str_repeat("abc\n", CliTestDoxReducedOutputPrinter::MAX_LINES),"\n");
        $cases['No change in small multiline messages below line limit'] = [
            'providedMessage' => $message,
            'expectedMessage' => $message
        ];

        $message = trim(str_repeat('test', (int) floor(CliTestDoxReducedOutputPrinter::MAX_LINE_LENGTH / strlen('test')) + 1));
        $cases['Trimmed long line message'] = [
            'providedMessage' => $message,
            'expectedMessage' => substr($message, 0, CliTestDoxReducedOutputPrinter::MAX_LINE_LENGTH) . '(...)'
        ];

        $message = rtrim(str_repeat("abc\n", CliTestDoxReducedOutputPrinter::MAX_LINES+2),"\n");
        $expectedMessage = substr($message, 0,-8) . "\n(2 more lines...)";
        $cases['Remove lines in excess of ' . CliTestDoxReducedOutputPrinter::MAX_LINES] = [
            'providedMessage' => $message,
            'expectedMessage' => $expectedMessage
        ];

        $longLine = trim(str_repeat('test', (int) floor(CliTestDoxReducedOutputPrinter::MAX_LINE_LENGTH / strlen('test')) + 1));
        $message = rtrim(str_repeat("$longLine\n", CliTestDoxReducedOutputPrinter::MAX_LINES+2),"\n");
        $trimmedLine = substr($longLine, 0, CliTestDoxReducedOutputPrinter::MAX_LINE_LENGTH) . "(...)\n";
        $expectedMessage = str_repeat($trimmedLine, CliTestDoxReducedOutputPrinter::MAX_LINES) . "(2 more lines...)";

        $cases['Remove lines above limit and trim long messages'] = [
            'providedMessage' => $message,
            'expectedMessage' => $expectedMessage
        ];

        return $cases;
    }
}
