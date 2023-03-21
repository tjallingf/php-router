<?php 
    namespace Tjall\Router\Handlers;

    use Tjall\Router\Router;
    use Tjall\Router\Http\Status;

    class ErrorHandler {
        public static function handle(\Exception $e) {
            $status = ($e->getCode() > 0 ? $e->getCode() : Status::INTERNAL_SERVER_ERROR);
            $message = $e->getMessage() ?? 'An unknown error occured.';
            
            return Router::$response->status($status)->json([ 
                'status' => $status, 
                'error' => rtrim($message, '.').'.'
            ]);
        }
    }