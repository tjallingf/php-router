<?php
    use Router\Route;
    use Router\Response;
    use Router\Request;
    use Router\Tests\Data\Pictures;

    Route::get('/api/pictures', function(Request $req, Response $res) {
        return $res->sendJson(Pictures::get());
    });

    Route::get('/api/pictures/{id}', function(Request $req, Response $res) {
        $picture = Pictures::getOne($req->getParam('id')) ?? $res->throw('Picture not found.', 404); 

        return $res->sendJson($picture);
    });

    Route::patch('/api/pictures/{id}', function(Request $req, Response $res) {
        $picture = Pictures::getOne($req->getParam('id')) ?? $res->throw('Picture not found.', 404);

        return $res->sendJson(array_replace($picture), $req->body);
    });