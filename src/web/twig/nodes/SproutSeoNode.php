<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\web\twig\nodes;

use barrelstrength\sproutseo\SproutSeo;
use Twig\Compiler;
use Twig\Node\Node as TwigNode;

class SproutSeoNode extends TwigNode
{
    /**
     * Compiles a Optimize_Node into PHP.
     *
     * @param Compiler $compiler
     */
    public function compile(Compiler $compiler)
    {
        $action = $this->getNode('action')->getAttribute('value');

        if ($action == 'optimize') {
            $compiler
                ->addDebugInfo($this)
                ->write('echo '.SproutSeo::class."::\$app->optimize->getMetadataViaContext(\$context);\n\n");
        }
    }
}
