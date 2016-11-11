<?php

namespace Craft;

class SproutSeo_Node extends \Twig_Node
{
	/**
	 * Compiles a Optimize_Node into PHP.
	 */
	public function compile(\Twig_Compiler $compiler)
	{
		// $this->getNode('action')

		$compiler
			->addDebugInfo($this)
			->write("echo \Craft\craft()->sproutSeo->optimize->getMetadata(\$context);\n\n");
	}
}
