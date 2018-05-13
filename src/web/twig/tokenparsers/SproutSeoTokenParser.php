<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\twig\tokenparsers;

use barrelstrength\sproutseo\web\twig\nodes\SproutSeoNode;

class SproutSeoTokenParser extends \Twig_TokenParser
{
    public function getTag()
    {
        return 'sproutseo';
    }

    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $nodes = [
            'action' => $this->parser->getExpressionParser()->parseExpression(),
        ];
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new SproutSeoNode($nodes, [], $lineno, $this->getTag());
    }
}

