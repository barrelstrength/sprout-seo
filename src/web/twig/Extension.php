<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\web\twig;

use barrelstrength\sproutseo\web\twig\tokenparsers\SproutSeoTokenParser;
use Twig_Extension;

class Extension extends Twig_Extension
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
        return [
            new SproutSeoTokenParser()
        ];
    }

}