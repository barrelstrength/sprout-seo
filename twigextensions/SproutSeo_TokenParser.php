<?php
namespace Craft;

require_once 'SproutSeo_Node.php';

class SproutSeo_TokenParser extends \Twig_TokenParser
{
	public function getTag()
	{
		return 'sproutseo';
	}

	public function parse(\Twig_Token $token)
	{
		$lineno = $token->getLine();
		$action = $this->parser->getExpressionParser()->parseExpression();
		$this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

		return new SproutSeo_Node(array('action' => $action), array(), $lineno, $this->getTag());
	}
}

