<?php

namespace Craft;

class SproutSeo_Node extends \Twig_Node
{
	/**
	 * Compiles a Optimize_Node into PHP.
	 */
	public function compile(\Twig_Compiler $compiler)
	{
		$action = $this->getNode('action')->getAttribute('value');

		if ($action == 'optimize')
		{
			$compiler
				->addDebugInfo($this)
				->write("echo \Craft\craft()->sproutSeo->optimize->getMetadata(\$context);\n\n");
		}
	}
}
