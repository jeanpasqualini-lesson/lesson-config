<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/10/15
 * Time: 3:31 AM
 */

namespace Test;

use Interfaces\TestInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class YamlConfig implements TestInterface {

    public function runTest()
    {
        $configDirectories = array(
            ROOT_DIR.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."yml"
        );

        $locator = new FileLocator($configDirectories);

        $yamlUserFiles = $locator->locate("config.yml", null, false);

        $yamlUserFiles[] = $configDirectories[0].DIRECTORY_SEPARATOR."config2.yml";

        $configs = array();

        foreach($yamlUserFiles as $yamlUserFile)
        {
            $configs[] = Yaml::parse(file_get_contents($yamlUserFile));
        }

        return $configs;
    }

}