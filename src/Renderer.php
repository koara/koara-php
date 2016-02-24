<?php

namespace Koara\Renderer;

interface Renderer
{
    public function visitDocument($node);

    public function visitHeading($node);

    public function visitBlockQuote($node);

    public function visitListBlock($node);

    public function visitListItem($node);

    public function visitCodeBlock($node);

    public function visitParagraph($node);

    public function visitBlockElement($node);

    public function visitImage($node);

    public function visitLink($node);

    public function visitText($node);

    public function visitStrong($node);

    public function visitEm($node);

    public function visitCode($node);

    public function visitLineBreak($node);
}
