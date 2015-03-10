<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/9/15
 * Time: 10:34 AM
 */
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class CustomConfiguration implements ConfigurationInterface {
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root("customconfiguration");

        $rootNode
            ->children()
                ->scalarNode("string")
                    ->isRequired(true)
                ->end()
                ->booleanNode("boolean")
                    ->isRequired(true)
                 ->end()
                ->arrayNode("db")
                     ->children()
                        ->scalarNode("driver")
                            ->isRequired(true)
                        ->end()
                        ->scalarNode("host")
                            ->isRequired(true)
                        ->end()
                        ->scalarNode("username")
                            ->isRequired(true)
                        ->end()
                        ->scalarNode("password")
                            ->isRequired(true)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("failovers")
                    ->isRequired("true")
                    ->requiresAtLeastOneElement()
                    ->prototype("scalar")
                    ->end()
                ->end()
                ->enumNode("enum")
                    ->values(array("un", "deux", "trois"))
                    ->isRequired(true)
                ->end()
                ->integerNode("integer")
                    ->min(10)
                    ->max(10)
                    ->isRequired(true)
                ->end()
                ->floatNode("float")
                    ->isRequired(true)
                ->end()
                ->variableNode("variable")
                    ->isRequired(true)
                ->end()
                ->scalarNode("defaultValueScalar")
                    ->defaultValue("ok")
                ->end()
                ->scalarNode("defaultValueTrue")
                    ->defaultTrue()
                ->end()
                ->scalarNode("cannotBeEmpty")
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode("treatLike")
                    ->treatTrueLike("salut")
                ->end()
                ->scalarNode("cannotBeOverwritten")
                    ->cannotBeOverwritten()
                ->end()
                ->arrayNode("performNoDeepMerging")
                    ->performNoDeepMerging() // Return 3, 4, 5 and not 1, 2, 3, 4, 5
                    ->prototype("scalar")
                    ->end()
                ->end()
                ->scalarNode("driver")
                    ->validate()
                        ->ifNotInArray(array("mysql", "sqlite"))
                       // ->then(function($val) { return "lol"; })
                        ->thenInvalid("Invalid database driver %s")
                    ->end()
                ->end()
                ->arrayNode("virgules")
                    ->isRequired(true)
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function($v)
                        {
                            return array_map(function($v) {
                                return intval($v);
                            }, explode(",", $v));
                        })
                    ->end()
                    ->prototype("integer")
                    ->end()
                ->end()
            ->end()
        ->end();

        $this->addCustomSection($rootNode);


        return $treeBuilder;

        // TODO: Implement getConfigTreeBuilder() method.
    }

    private function addCustomSection(\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $node)
    {

        $node
            ->children()
                ->arrayNode("dbs")
           //     ->canBeEnabled()
                ->useAttributeAsKey("name")
                ->prototype("array")
                    ->children()
                        ->scalarNode("driver")
                            ->isRequired(true)
                        ->end()
                        ->scalarNode("host")
                            ->isRequired(true)
                        ->end()
                        ->scalarNode("username")
                            ->isRequired(true)
                        ->end()
                        ->scalarNode("password")
                            ->isRequired(true)
                        ->end()
                    ->end()
                ->end()
            ->end()
            ;
    }


}