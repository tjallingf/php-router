<?php
    use Router\Helpers\Route;
    use Router\Helpers\Response;
    use Router\Helpers\Request;

    Route::get('/error/{code?}', function(Request $req, Response $res) {
        $status_code = $req->getParam('code') ?? 500;
        $message = $req->getQuery('message') ?? 'An error occured.';

        return $res->sendError($message, $status_code);
    });