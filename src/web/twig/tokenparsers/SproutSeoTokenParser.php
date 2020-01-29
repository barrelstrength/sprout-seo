<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\web\twig\tokenparsers;

use barrelstrength\sproutseo\web\twig\nodes\SproutSeoNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class SproutSeoTokenParser extends AbstractTokenParser
{
    public function getTag(): string
    {
        return 'sproutseo';
    }

    public function parse(Token $token)
    {
        $lineno = $token->getLine();
        $nodes = [
            'action' => $this->parser->getExpressionParser()->parseExpression(),
        ];
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new SproutSeoNode($nodes, [], $lineno, $this->getTag());
    }
}

