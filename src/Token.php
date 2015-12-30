<?php

namespace Koara;

class Token
{
    public $kind;
    public $beginLine;
    public $beginColumn;
    public $endLine;
    public $endColumn;
    public $image;
    public $next;
    public $specialToken;

    public function __construct($kind = 0, $beginLine = 0, $beginColumn = 0, $endLine = 0, $endColumn = 0, $image = NULL)
    {
        $this->kind = $kind;
        $this->beginLine = $beginLine;
        $this->beginColumn = $beginColumn;
        $this->endLine = $endLine;
        $this->endColumn = $endColumn;
        $this->image = $image;
    }
}
