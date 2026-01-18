<?php
namespace Faq;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\\Loader\\StandardAutoloader' => array(
                'namespaces' => array(
                    'Faq\\Controller' => __DIR__ . '/src/Faq/Controller',
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}