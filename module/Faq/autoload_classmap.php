<?php
public function getAutoloaderConfig()
{
    return array(
        'Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                'faq\Controller' => __DIR__ . '/src/Controller',
            ),
        ),
    );
}