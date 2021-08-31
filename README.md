# testdox-reduced-output-printer

[![Build](https://github.com/wealthberry/testdox-reduced-output-printer/workflows/Build/badge.svg?event=push)](https://github.com/wealthberry/testdox-reduced-output-printer/actions?query=workflow%3ABuild+event%3Apush)
[![Latest Stable Version](https://poser.pugx.org/wealthberry/testdox-reduced-output-printer/v/stable)](https://packagist.org/wealthberry/testdox-reduced-output-printer)
[![Total Downloads](https://poser.pugx.org/wealthberry/testdox-reduced-output-printer/downloads)](https://packagist.org/wealthberry/testdox-reduced-output-printer)
[![License](https://poser.pugx.org/wealthberry/testdox-reduced-output-printer/license)](https://packagist.org/packages/wealthberry/testdox-reduced-output-printer)

A PHPUnit result printer based on CliTestDoxPrinter, but with reduced output line count and line length.

Readability of results is much better for assertions with huge failure message strings. If you want to see the full messages, just omit the printer class option.



## Installation

```bash
composer require --dev wealthberry/testdox-reduced-output-printer
```

## Usage

You can use the printer with a phpunit command line parameter:

```bash
php vendor/bin/phpunit --printer 'Wealthberry\TestDox\CliTestDoxReducedOutputPrinter'
```

Or, by adding a **printerClass** property in `phpunit.xml`:

```xml
<phpunit bootstrap="bootstrap.php" colors="true" printerClass="Wealthberry\TestDox\CliTestDoxReducedOutputPrinter">
```


