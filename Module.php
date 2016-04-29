<?php
namespace SwissEngine\Tools\ErrorHandler;

use ErrorException;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\DefaultRenderingStrategy;
use Zend\Mvc\View\Http\ViewManager;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $callback = function () use ($e) {
            $error = error_get_last() ?: func_get_args();
            $error = array_values($error);

            if (empty($error) || php_sapi_name() === 'cli') {
                return;
            }

            // Create exception
            $exception = new ErrorException($error[1], 0, $error[0], $error[2], $error[3], isset($error[4]) && $error[4] instanceof \Exception ? $error[4] : null);

            // Non-fatal error
            if (!in_array($error[0], [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_CORE_WARNING,
                E_COMPILE_ERROR,
                E_COMPILE_WARNING
            ])
            ) {
                throw $exception;
            }

            // Fatal error : clear all output
            if (ob_get_level() >= 1) {
                ob_end_clean();
            }

            $events = $e->getApplication()->getEventManager();

            $e->setError(Application::ERROR_EXCEPTION);
            $e->setParam('exception', $exception);
            $e->setName(MvcEvent::EVENT_RENDER_ERROR);

            $events->triggerEvent($e);

            $e->getApplication()->run();
        };

        set_error_handler($callback, E_ALL);
        register_shutdown_function($callback);
    }
}
