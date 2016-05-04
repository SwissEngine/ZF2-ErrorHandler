<?php
namespace SwissEngine\Tools\ErrorHandler;

use ErrorException;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

/**
 * Class Module
 *
 * @package SwissEngine\Tools\ErrorHandler
 */
class Module
{
    /**
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        ini_set('display_errors', 'false');

        $errorCallback = function () use ($e) {
            // Fetching the error's information
            $error = error_get_last() ?: func_get_args();
            $error = array_values($error);
            
            if (empty($error)) {
                return;
            }

            // Create exception ans associated event
            $exception = new ErrorException($error[1], 0, $error[0], $error[2], $error[3], isset($error[4]) && $error[4] instanceof \Exception ? $error[4] : null);
            $e->setParam('exception', $exception);
            $e->setError(Application::ERROR_EXCEPTION);
            $e->setName(MvcEvent::EVENT_RENDER_ERROR);

            /** @var Application $application */
            $application = $e->getApplication();
            $application->getEventManager()->triggerEvent($e);
            $application->run();
        };

        // Set error handlers
        set_error_handler($errorCallback, \E_ALL);
        register_shutdown_function($errorCallback);
    }
}
