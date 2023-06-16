<?php 
    namespace Tjall\Router\Handlers;

    use Tjall\Router\Router;
    use Tjall\Router\Http\Status;
    use Tjall\Router\Http\Request;
    use Tjall\Router\Http\Response;
    use Exception;
    use Tjall\Router\RouteException;

    class ErrorHandler {
        public static function handle($e) {
            if(!is_a($e, Exception::class)) {
                return;
            }

            $status = 500;
            $message = $e->getMessage() ?? 'An unknown error occured.';
            if(is_a($e, RouteException::class)) {
                $status = $e->status;
            }
            
            if(!isset(Router::$response)) {       
                Router::$request = new Request([]);
                Router::$response = new Response(Router::$request);
            }

            Router::$response->status($status);

            if(isset(Router::$errorRoutes[$status])) {
                foreach (Router::$errorRoutes[$status] as $route) {
                    $route->call();
                }
            } else {  
                Router::$response->json([ 
                    'status' => $status, 
                    'error' => rtrim($message, '.').'.'
                ]);
            }
        }
    }