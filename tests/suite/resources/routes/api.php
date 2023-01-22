<?php
    use Router\Route;
    use Router\Response;
    use Router\Request;
    use Router\Tests\Data\Pictures;

    Route::get('/api/pictures', function(Request $req, Response $res) {
        $is_authenticated = str_starts_with($req->getHeaderLine('authorization'), 'Bearer');
        
        return $res->sendJson(Pictures::get($is_authenticated));
    });

    Route::get('/api/pictures/{id}', function(Request $req, Response $res) {
        $picture = Pictures::getOne($req->getParam('id'));

        return $res->sendJson($picture);
    });

    Route::patch('/api/pictures/{id}', function(Request $req, Response $res) {
        $picture = Pictures::getOne($req->getParam('id'));

        return $res->sendJson(array_replace($picture, $req->body));
    });