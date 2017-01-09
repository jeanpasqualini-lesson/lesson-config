<?php
namespace tests;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * BadConfigurationTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class BadConfigurationTest extends AbstractConfigurationTest
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testEnumNodeWithBadValue()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->enumNode('enum')
                    ->values(array('un', 'deux'))
                ->end()
            ->end()
        ;

        $this->process(
            $treeBuilder->buildTree(),
            <<<EVBUFFER_EOF
enum: trois
EVBUFFER_EOF
        );
    }

    public function testRequiresAtLeastOneElement()
    {
        $this->setExpectedException(InvalidConfigurationException::class);

        $this->rootNode
            ->children()
                ->arrayNode('array')
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        $yaml = <<<EVBUFFER_EOF
array: []
EVBUFFER_EOF;

        $this->process(
            $this->treeBuilder->buildTree(),
            $yaml
        );
    }
}