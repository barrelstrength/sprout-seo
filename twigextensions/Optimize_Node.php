<?php

namespace Craft;

class Optimize_Node extends \Twig_Node
{
	/**
	 * Compiles a Optimize_Node into PHP.
	 */
	public function compile(\Twig_Compiler $compiler)
	{
		$compiler
			->addDebugInfo($this)
			->write("echo \Craft\craft()->sproutSeo->optimize->prepareLinkedData(\$context);\n\n");
	}
}
