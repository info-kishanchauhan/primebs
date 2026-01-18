<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        // start session very early
        if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $app = $e->getApplication();
        $em  = $app->getEventManager();
        (new ModuleRouteListener())->attach($em);

        /**
         * 1) HARD WHITELIST for /feedback/submit
         * Runs with MAX priority so any other auth listener can’t redirect it.
         */
        $whitelist = function (MvcEvent $e) {
            $req  = $e->getRequest();
            if (!method_exists($req, 'getUri')) return;
            $path = $req->getUri()->getPath();

            // allow literal + index.php form + optional trailing slash
            $isFeedbackSubmit = (bool) preg_match('#^/(index\.php/)?feedback/submit/?$#i', $path);
            if (!$isFeedbackSubmit) return;

            // if some earlier listener already set a redirect, clear it
            $resp = $e->getResponse();
            if (method_exists($resp, 'getStatusCode') && $resp->getStatusCode() === 302) {
                $resp->setStatusCode(200);
                $hdrs = $resp->getHeaders();
                if ($hdrs->has('Location')) {
                    $hdrs->removeHeader($hdrs->get('Location'));
                }
            }

            // stop other ROUTE/DISPATCH listeners from acting on this request
            $e->stopPropagation(true);
        };

        // run before everyone else
        $em->attach(MvcEvent::EVENT_ROUTE,    $whitelist, PHP_INT_MAX);
        $em->attach(MvcEvent::EVENT_DISPATCH, $whitelist, PHP_INT_MAX);

        /**
         * 2) Protect ONLY /tickets* routes (login required)
         */
        $ticketsGuard = function (MvcEvent $e) {
            $match = $e->getRouteMatch();
            if (!$match) return;

            $route = (string) $match->getMatchedRouteName();

            // never guard feedback submit
            $req  = $e->getRequest();
            $path = method_exists($req, 'getUri') ? $req->getUri()->getPath() : '';
            if (preg_match('#^/(index\.php/)?feedback/submit/?$#i', $path)) return;

            if (strpos($route, 'tickets') === 0) {
                if (empty($_SESSION['user_id'])) {
                    $resp = $e->getResponse();
                    $resp->getHeaders()->addHeaderLine('Location', '/login');
                    $resp->setStatusCode(302);
                    $e->stopPropagation(true);
                    return $resp;
                }
            }
        };

        // also very high, but after the whitelist
        $em->attach(MvcEvent::EVENT_ROUTE, $ticketsGuard, PHP_INT_MAX - 1);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\\Loader\\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getControllerPluginConfig()
    {
        return [
            'invokables' => [
                'CustomPlugin' => 'Application\Controller\Plugin\CustomPlugin',
            ],
        ];
    }
}
