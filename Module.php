<?php
namespace SwissEngine\ErrorHandler;

use ErrorException;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $callback = function () use ($e)
        {
            $error    = error_get_last() ?: func_get_args();
            $error    = array_values($error);

            if (!empty($error)) {
                // Create exception
                $exception = new ErrorException($error[1], 0, $error[0], $error[2], $error[3], !isset($error[4]) ? $error[4] : null);

                // Non-fatal error
                if (!in_array($error[0], [
                    E_ERROR,
                    E_PARSE,
                    E_CORE_ERROR,
                    E_CORE_WARNING,
                    E_COMPILE_ERROR,
                    E_COMPILE_WARNING
                ])) {
                    throw $exception;
                }

                // Fatal error : clear all output
                if (ob_get_level() >= 1) {
                    ob_end_clean();
                }

                // Initialize render tools
                $sm        = $e->getApplication()->getServiceManager();
                $manager   = $sm->get('viewManager');
                $config    = $sm->get('Config');
                $renderer  = $manager->getRenderer();
                $layout    = $manager->getLayoutTemplate();
                $viewType  = get_class($manager->getViewModel());

                // Config based output
                $display   = isset($config['view_manager']['display_exceptions']) ? $config['view_manager']['display_exceptions'] : null;
                $template  = isset($config['view_manager']['exception_template']) ? $config['view_manager']['exception_template'] : null;

                $model     = new $viewType();
                $model->setTemplate($layout);

                // Error page
                if (null !== $template) {
                    $content   = new $viewType(
                        array(
                            'exception'          => $exception,
                            'display_exceptions' => $display
                        )
                    );
                    $content->setTemplate($template);
                    $result = $renderer->render($content);
                    $model->setVariables([
                        'content'   => $result,
                        'exception' => $exception,
                    ]);
                }

                echo $renderer->render($model);
            }
        };

        set_error_handler($callback, E_ALL);
        register_shutdown_function($callback);
    }
}