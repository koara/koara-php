[![Koara](https://www.codeaddslife.com/koara.png)](https://www.codeaddslife.com/koara)

[![Build Status](https://img.shields.io/travis/koara/koara-php.svg)](https://travis-ci.org/koara/koara-php)
[![Coverage Status](https://img.shields.io/coveralls/koara/koara-php.svg)](https://coveralls.io/github/koara/koara-php?branch=master)
[![Latest Version](https://img.shields.io/packagist/v/koara/koara.svg)](https://packagist.org/packages/koara/koara)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/koara/koara-php/blob/master/LICENSE)

# Koara-php
[Koara](https://www.codeaddslife.com/koara) is a modular lightweight markup language. This project is the core koara parser written in PHP.  
If you are interested in converting koara to a specific outputFormat, please look the [Related Projects](#related-projects) section.

## Getting started
- Download [ZIP file](https://github.com/koara/koara-php/archive/0.15.0.zip)
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
$result1 = $parser->parse("Hello World!"); // parse a string
$result2 = $parser->parseFile('hello.kd'); // parse a file

?>
```

## Configuration
You can configure the Parser:
-  **setHardWrap($hardWrap)**  
   Default: `false`
   
   Specify if newlines should be hard-wrapped (return-based linebreaks) by default.
   
-  **setModules($modules)**  
   Default:	`array("paragraphs", "headings", "lists", "links", "images", "formatting", "blockquotes", "code")`
   
   Specify which parts of the syntax are allowed to be parsed. The rest will render as plain text.

## Related Projects

- [koara / koara-php-html](http://www.github.com/koara/koara-php-html): Koara to Html renderer written in PHP
- [koara / koara-php-xml](http://www.github.com/koara/koara-php-xml): Koara to Xml renderer written in PHP