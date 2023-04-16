<?php 
    namespace Tjall\Router\Handlers;

    use Tjall\Router\Router;
    use Tjall\Router\Http\Status;
    use Tjall\Router\Http\Request;
    use Tjall\Router\Http\Response;

    class ErrorHandler {
        public static function handle(\Exception $e) {
            $status = ($e->getCode() > 0 ? $e->getCode() : Status::INTERNAL_SERVER_ERROR);
            $message = $e->getMessage() ?? 'An unknown error occured.';

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