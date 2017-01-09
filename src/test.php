<?php

include __DIR__."/../vendor/autoload.php";

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;

$tests = array(
   new \Test\YamlConfig(),
   new \Test\XmlConfig()
);

define("ROOT_DIR", __DIR__);


$processor = new \Symfony\Component\Config\Definition\Processor();

$configuration = new CustomConfiguration();

$dumper = new YamlReferenceDumper();
echo $dumper->dump($configuration).PHP_EOL;

foreach($tests as $test)
{
    echo "===".get_class($test)."===".PHP_EOL;
    try {

        $processedConfiguration = $processor->processConfiguration(
            $configuration,
            $test->runTest()
        );

        echo print_r($processedConfiguration, true);
    }
    catch(InvalidConfigurationException $e)
    {
        echo "[ERROR] ".$e->getMessage().PHP_EOL;
    }
}



