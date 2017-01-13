<?php
namespace tests;

use Symfony\Component\Config\Definition\Builder\FloatNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * ConfigurationTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class ConfigurationTest extends AbstractConfigurationTest
{
    public function testScalarNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('customconfiguration');

        $rootNode
            ->children()
                ->scalarNode('string')
                    ->isRequired()
                ->end()
            ->end()
        ;

        $yaml = <<<EVBUFFER_EOF
string: scalar-node-string
EVBUFFER_EOF;

        $xml = <<<EVBUFFER_EOF
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <string>scalar-node-string</string>
</root>
EVBUFFER_EOF;

        $config = $this->process(
            $treeBuilder->buildTree(),
            $yaml,
            $xml
        );

        $this->assertEquals(array('string' => 'scalar-node-string'), $config);
    }



    public function testVariableNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('customconfiguration');

        $rootNode
            ->children()
                ->variableNode('variable')
                    ->isRequired()
                ->end()
            ->end()
        ;

        $config = $this->process(
            $treeBuilder->buildTree(),
            <<<EVBUFFER_EOF
variable:
   a: a
   b: b
EVBUFFER_EOF
        );

        $this->assertEquals(array('variable' => array('a' => 'a', 'b' => 'b')), $config);
    }

    public function testSimpleArrayNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->arrayNode('array')
                    ->isRequired()
                    ->prototype('integer')
                ->end()
            ->end()
        ;

        $config = $this->process(
            $treeBuilder->buildTree(),
            <<<EVBUFFER_EOF
array:
    - 4
    - 6
EVBUFFER_EOF
            );

            $this->assertEquals(array('array' => array(4, 6)), $config);
    }

    public function testMutipleArrayNode()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->arrayNode('array')
                    ->isRequired()
                    ->prototype('array')
                        ->prototype('integer')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $config = $this->process(
            $treeBuilder->buildTree(),
            <<<EVBUFFER_EOF
array:
    A: [4]
    B: [6]
EVBUFFER_EOF
            );

            $this->assertEquals(array('array' => array('A' => array(4), 'B' => array(6))), $config);
    }

    public function testEnumNode()
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

        $config = $this->process(
            $treeBuilder->buildTree(),
            <<<EVBUFFER_EOF
enum: deux
EVBUFFER_EOF
        );

        $this->assertEquals(array('enum' => 'deux'), $config);
    }

    public function testMerge()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->arrayNode('array')
                    ->prototype('integer')
                    ->end()
                ->end()
            ->end()
        ;

        $config = $this->process(
            $treeBuilder->buildTree(),
            <<<EVBUFFER_EOF
array: [1, 2]
-----
array: [3, 4, 5]
EVBUFFER_EOF
        );

        $this->assertEquals(array('array' => array(1, 2, 3, 4, 5)), $config);
    }

    public function testCannotBeOverwritten()
    {
        $this->setExpectedException(ForbiddenOverwriteException::class);

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->scalarNode('string')
                    ->cannotBeOverwritten()
                ->end()
            ->end()
            ;

        $yaml = <<<EVBUFFER_EOF
string: value
-----
string: value
EVBUFFER_EOF;

        $config = $this->process(
            $treeBuilder->buildTree(),
            $yaml
        );
    }

    public function testIsRequired()
    {
        $this->setExpectedException(InvalidConfigurationException::class);

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->scalarNode('string')
                    ->isRequired()
                ->end()
            ->end()
        ;

        $yaml = <<<EVBUFFER_EOF
EVBUFFER_EOF;

        $config = $this->process(
            $treeBuilder->buildTree(),
            $yaml
        );
    }

    public function testDefaultValue()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->integerNode('integer')
                    ->defaultValue('default')
                ->end()
                ->integerNode('integer_nullable')
                    ->isRequired()
                    ->beforeNormalization()
                        ->ifNull()
                        ->then(function($value) {
                            return 5;
                        })
                ->end()
            ->end()
        ;

        $yaml = <<<EVBUFFER_EOF
integer_nullable: ~
EVBUFFER_EOF;

        $config = $this->process(
            $treeBuilder->buildTree(),
            $yaml
        );

        $this->assertEquals(array('integer' => 'default', 'integer_nullable' => 5), $config);
    }

    public function testValidate()
    {
        $this->setExpectedException(InvalidConfigurationException::class);

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->scalarNode('db')
                    ->validate()
                        ->ifNotInArray(array('mysql', 'sqlite'))
                        ->thenInvalid('the db %s is unknow')
                    ->end()
                ->end()
            ->end()
        ;

        $yaml = <<<EVBUFFER_EOF
db: mongodb
EVBUFFER_EOF;

        $this->process($treeBuilder->buildTree(), $yaml);
    }

    public function testTreatTrueLike()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->variableNode('test')
                    ->treatTrueLike('vrai')
                    ->treatFalseLike('false')
                    ->treatNullLike('null')
                ->end()
            ->end()
        ;

        $yaml = <<<EVBUFFER_EOF
test: true
EVBUFFER_EOF;


        $config = $this->process($treeBuilder->buildTree(), $yaml);

        $this->assertEquals(array('test' => 'vrai'), $config);
    }

    public function testDumper()
    {
        $dumper = new YamlReferenceDumper();

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->variableNode('test')
                    ->example('valeur example')
                    ->info('une valeur')
                ->end()
            ->end()
        ;

        $dumped = $dumper->dumpNode($rootNode->getNode(true));

        $expected = <<<EVBUFFER_EOF
root:

    # une valeur
    test:                 ~ # Example: valeur example

EVBUFFER_EOF;

        $this->assertEquals($expected, $dumped, $dumped);
    }

    public function testNode()
    {
        $floatNode = new FloatNodeDefinition('float');
        $node = $floatNode->getNode();

        $this->assertEquals(4.4, $node->finalize(4.4));
    }

    public function testFloatNode()
    {
        $this->rootNode
            ->children()
                ->append(new FloatNodeDefinition('float'))
            ->end()
        ;

        $config = $this->process(
            $this->treeBuilder->buildTree(),
            <<<EVBUFFER_EOF
float: 4.4
EVBUFFER_EOF
        );

        $this->assertEquals(array('float' => 4.4), $config);
    }

    public function testUseAttributeAsKey()
    {
        $this->rootNode
            ->children()
                ->arrayNode('dbs')
                ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('server')->end()
                            ->scalarNode('username')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        $yaml = <<<EVBUFFER_EOF
dbs:
    - { name: game, server: minecraft, username: admin }
    - { name: os, server: microsoft, username: billgates }
EVBUFFER_EOF;

        $xml = <<<EVBUFFER_EOF
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <dbs>
        <name>game</name>
        <server>minecraft</server>
        <username>admin</username>
    </dbs>
    <dbs>
        <name>os</name>
        <server>microsoft</server>
        <username>billgates</username>
    </dbs>
</root>
EVBUFFER_EOF;


        $config = $this->process(
            $this->treeBuilder->buildTree(),
            $yaml,
            $xml
        );

        $this->assertEquals(array(
            'dbs' => array(
                'game' => array(
                    'server'    => 'minecraft',
                    'username'  => 'admin'
                ),
                'os' => array(
                    'server'    => 'microsoft',
                    'username'  => 'billgates'
                )
            )
        ), $config);
    }

    public function testNormalizeKeys()
    {
        // By default key defined normalized (- to _)

        // Disable normalizeKeys

        $this->setExpectedException(InvalidConfigurationException::class);

        $this->rootNode
            ->normalizeKeys(false)
            ->children()
                ->scalarNode('un_chat')->isRequired()->end()
            ->end()
        ;

        $yaml = <<<EVBUFFER_EOF
un-chat: perle
EVBUFFER_EOF;

        $config = $this->process(
            $this->treeBuilder->buildTree(),
            $yaml
        );

        $this->assertEquals(
            array(
                'un_chat' => 'perle'
            ),
            $config
        );
    }

    public function testFixXmlConfig()
    {
        $this->rootNode
            ->fixXmlConfig('chat')
            ->children()
                ->arrayNode('chats')
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        $yaml = <<<EVBUFFER_EOF
chats:
    - red
-----
chats:
    - blue
    - yellow
EVBUFFER_EOF;

        $xml = <<<EVBUFFER_EOF
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <chat>red</chat>
</root>
-----
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <chat>blue</chat>
    <chat>yellow</chat>
</root>
EVBUFFER_EOF;


        $config = $this->process(
            $this->treeBuilder->buildTree(),
            $yaml,
            $xml
        );

        $this->assertEquals(array('chats' => array('red', 'blue', 'yellow')), $config);
    }

    public function testAddDefaultsIfNotSet()
    {
        $this->rootNode
            ->children()
                ->scalarNode('name')
                    ->defaultValue('value')
                ->end()
                ->arrayNode('settings_with_adddefaultifnotset')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                            ->cannotBeEmpty()
                             ->defaultValue('value')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('settings_without_adddefaultifnotset')
                    ->children()
                        ->scalarNode('name')
                            ->isRequired()
                            ->cannotBeEmpty()
                             ->defaultValue('value')
                        ->end()
                    ->end()
                ->end()
            ->end();

        $yaml = "";

        $config = $this->process(
            $this->treeBuilder->buildTree(),
            $yaml
        );

        $this->assertEquals(array(
            'name' => 'value',
            'settings_with_adddefaultifnotset' => array('name' => 'value')
        ), $config);
    }

    public function testConfig()
    {
        return;
        $this->rootNode
                ->fixXmlConfig('chat')
                ->children()
                    ->arrayNode('chats')
                        ->children()
                            ->scalarNode('name')->end()
                        ->end()
                    ->end()
                ->end();

        $yaml = '';

        $xml = <<<EVBUFFER_EOF
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <chat>
        <name>perle</name>
    </chat>
    <chat name="ptirouis"/>
</root>
EVBUFFER_EOF;

        $config = $this->process(
            $this->treeBuilder->buildTree(),
            $yaml,
            $xml
        );

        $this->assertEquals(array(
            'chats' => array(
                array('name' => 'perle'),
                array('name' => 'ptirouis')
            )
        ), $config);
    }

}