<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/10/15
 * Time: 3:44 AM
 */

namespace Test;

use Symfony\Component\Config\FileLocator;
use Interfaces\TestInterface;
use Symfony\Component\Config\Util\XmlUtils;

class XmlConfig implements TestInterface {
    public function runTest()
    {
        $configDirectories = array(
            ROOT_DIR.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."xml"
        );

        $locator = new FileLocator($configDirectories);

        $yamlUserFiles = $locator->locate("config.xml", null, false);

        $yamlUserFiles[] = $configDirectories[0].DIRECTORY_SEPARATOR."config2.xml";

        $configs = array();

        foreach($yamlUserFiles as $yamlUserFile)
        {
            $configs[] = XmlUtils::convertDomElementToArray(XmlUtils::loadFile($yamlUserFile)->getElementsByTagName("root")->item(0));
        }

        return $configs;
    }
}