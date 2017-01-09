<?php
namespace tests;

use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor as ConfigProcessor;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\FloatNode;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * AbstractConfigurationTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
abstract class AbstractConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /** @var TreeBuilder */
    protected $treeBuilder;
    /** @var ArrayNodeDefinition */
    protected $rootNode;

    public function setUp()
    {
        $this->treeBuilder = new TreeBuilder();
        $this->rootNode = $this->treeBuilder->root('root');
    }

    protected function parseYamlContent($yamlContent)
    {
        $configsContent = explode('-----', $yamlContent);

        $configs = array();

        foreach($configsContent as $configContent)
        {
            $configs[] = Yaml::parse($configContent);
        }

        return $configs;
    }

    protected function parseXmlContent($xmlContent)
    {
        if (empty($xmlContent)) return array();

        $configsContent = explode('-----', $xmlContent);
        $configsContent  = array_map(function($content)
        {
            return trim($content);
        }, $configsContent);

        $configs = array();

        foreach($configsContent as $configContent)
        {
            $xmlDocument = new \DOMDocument();
            $xmlDocument->loadXML($configContent);

            $configs[] = XmlUtils::convertDomElementToArray($xmlDocument->getElementsByTagName("root")->item(0));
        }

        return $configs;
    }

    protected function process(NodeInterface $node, $yamlContent, $xmlContent = '')
    {
        $configs = array(
            'yaml' => $this->parseYamlContent($yamlContent),
            'xml' => $this->parseXmlContent($xmlContent)
        );

        $config = array(
            'yaml' => array(),
            'xml' => array()
        );

        foreach ($configs as $type => $configsItem) {
            if (!empty($configsItem)) {
                $process = new ConfigProcessor();
                $config[$type] = $process->process($node, $configsItem);
            }
        }

        if (!empty($config['xml'])) {
            $this->assertEquals($config['xml'], $config['yaml'], 'xml != yaml');
        }

        return $config['yaml'];
    }
}