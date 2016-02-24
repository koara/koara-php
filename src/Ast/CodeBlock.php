<?php

namespace Koara\Ast;

use Koara\Renderer\Renderer;

class CodeBlock extends BlockElement
{
    /**
     * @var string
     */
    private $language;

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @param Renderer $renderer
     */
    public function accept(Renderer $renderer)
    {
        $renderer->visitCodeBlock($this);
    }
    
}
