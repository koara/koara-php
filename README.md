[![Koara](http://www.koara.io/logo.png)](http://www.koara.io)

[![Build Status](https://img.shields.io/travis/koara/koara-php.svg)](https://travis-ci.org/koara/koara-php)
[![Coverage Status](https://img.shields.io/coveralls/koara/koara-php.svg)](https://coveralls.io/github/koara/koara-php?branch=master)
[![Latest Version](https://img.shields.io/packagist/v/koara/koara.svg)](https://packagist.org/packages/koara/koara)
[![License](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](https://github.com/koara/koara-java/blob/master/LICENSE)

> Koara to HTML parser written in PHP

## Getting Started
- Composer:

  ```bash
  composer require koara/koara
  ```
  
## Usage
```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Koara\Parser;
use Koara\Module;
use Koara\Renderer\Html5Renderer;

$parser = new Parser();

// Enable which modules to parse (all are parsed by default)
$parser->setModules(Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS,
        Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE);

// Parse string or file and generate AST
$document = $parser->parseFile('Hello World!');

// Render AST as HTML
$renderer = new Html5Renderer();
$document->accept($renderer);

// Prints '<p>Hello World!</p>'
echo $renderer->getOutput();
```

## Community
- Mailing Lists: [archive](http://groups.google.com/group/koara-users/topics), [subscribe](mailto:koara-users+subscribe@googlegroups.com), [unsubscribe](mailto:koara-users+unsubscribe@googlegroups.com)
- Projects: [http://koara.io/projects.html](http://koara.io/projects)
  