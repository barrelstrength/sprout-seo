<?php
namespace Craft;

require_once 'Optimize_Node.php';

class Optimize_TokenParser extends \Twig_TokenParser
{
	public function getTag()
	{
		return 'optimize';
	}

	public function parse(\Twig_Token $token)
	{
		$lineno = $token->getLine();
		$this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

		return new Optimize_Node(array(), array(), $lineno, $this->getTag());
	}
}

