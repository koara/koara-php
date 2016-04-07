[![Koara](http://www.koara.io/logo.png)](http://www.koara.io)

[![Build Status](https://img.shields.io/travis/koara/koara-php.svg)](https://travis-ci.org/koara/koara-php)
[![Coverage Status](https://img.shields.io/coveralls/koara/koara-php.svg)](https://coveralls.io/github/koara/koara-php?branch=master)
[![Latest Version](https://img.shields.io/maven-central/v/io.koara/koara.svg?label=Maven Central)](http://search.maven.org/#search%7Cga%7C1%7Ckoara)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/koara/koara-php/blob/master/LICENSE)

# Koara-php
[Koara](http://www.koara.io) is a modular lightweight markup language. This project is the core koara parser written in PHP.  
If you are interested in converting koara to a specific outputFormat, please look the [Related Projects](#related-projects) section.

## Getting started
- Download [ZIP file]()
- Composer

  ``` bash
  $ composer require koara/koara
  ```

## Usage
```php
<?php 

require_once __DIR__ . '/vendor/autoload.php';
	
use Koara\Parser;

$parser = new Parser();
$document1 = $parser->parse("Hello World!"); // parse a string
$document2 = $parser->parseFile('hello.kd'); // parse a file

?>
```

## Configuration
You can configure the Parser:

-  **setModules($modules)**  
   Default:	`array("paragraphs", "headings", "lists", "links", "images", "formatting", "blockquotes", "code")`
   
   Specify which parts of the syntax are allowed to be parsed. The rest will render as plain text.

## Related Projects

- [koara / koara-php-html](http://www.github.com/koara/koara-php-html): Koara to Html renderer written in PHP
- [koara / koara-php-xml](http://www.github.com/koara/koara-php-html): Koara to Xml renderer written in PHP