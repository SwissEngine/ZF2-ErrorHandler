<?php
namespace SwissEngine\Tools\ErrorHandler;

use ErrorException;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        ini_set('display_errors', 'false');
        $callback = function () use ($e) {
            // Fetching the error's information
            $error = error_get_last() ?: func_get_args();
            $error = array_values($error);

            // Create exception
            $exception = new ErrorException($error[1], 0, $error[0], $error[2], $error[3], isset($error[4]) && $error[4] instanceof \Exception ? $error[4] : null);

            /** @var Application $application */
            $application = $e->getApplication();

            $e->setError(Application::ERROR_EXCEPTION);
            $e->setParam('exception', $exception);
            $e->setName(MvcEvent::EVENT_RENDER_ERROR);

            $application->getEventManager()->triggerEvent($e);

            $e->getApplication()->run();
        };

        set_error_handler($callback, \E_ALL);
        register_shutdown_function($callback);
    }
}
