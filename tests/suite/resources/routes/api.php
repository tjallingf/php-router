<?php
    use Router\Helpers\Route;
    use Router\Helpers\Response;
    use Router\Helpers\Request;
    use Router\Tests\Data\Pictures;

    Route::get('/api/pictures', function(Request $req, Response $res) {
        return $res->json(Pictures::get());
    });

    Route::get('/api/pictures/{id}', function(Request $req, Response $res) {
        $picture = $res->catchNotFound(Pictures::getOne($req->getParam('id')), 
            "Picture not found");

        return $res->json($picture);
    });

    Route::patch('/api/pictures/{id}', function(Request $req, Response $res) {
        $picture = $res->catchNotFound(Pictures::getOne($req->getParam('id')), 
            "Picture not found");

        return $res->json(array_replace($picture), $req->body);
    });