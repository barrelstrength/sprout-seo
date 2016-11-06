<?php

namespace Craft;

require_once 'Optimize_TokenParser.php';
require_once 'SproutSeo_TokenParser.php';

class SproutSeoTwigExtension extends \Twig_Extension
{
	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'Sprout SEO Optimize';
	}

	public function getTokenParsers()
	{
		return array(
			new Optimize_TokenParser(),
			new SproutSeo_TokenParser()
		);
	}

}