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

    public function __construct($kind = null, $beginLine = null, $beginColumn = null, $endLine = null, $endColumn = null, $image = null)
    {
        $this->kind = $kind;
        $this->beginLine = $beginLine;
        $this->beginColumn = $beginColumn;
        $this->endLine = $endLine;
        $this->endColumn = $endColumn;
        $this->image = $image;
    }
}
