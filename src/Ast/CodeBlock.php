<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class CodeBlock extends BlockElement
{
    /**
     * @var string
     */
    private $language;

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function accept(Renderer $renderer)
    {
        $renderer->visitCodeBlock($this);
    }
}
