<?php
    use Router\Tests\Extensions\TestCase;
    use Router\Controllers\RouteController;
    use Router\Tests\Data\Pictures;
    use Router\Helpers\Response;

    final class RouteTest extends TestCase {
        public function testApiGet() {
            $res = RouteController::handleRequest('get', '/api/pictures');
            $expected = json_encode(Pictures::get());

            TestCase::assertEquals($expected, $res->getBody(), 'Response body does not match.');
        }

        public function testApiGetOne() {
            $id = Pictures::get()[0]['id'];
            $res = RouteController::handleRequest('get', "/api/pictures/$id");
            $expected = json_encode(Pictures::getOne($id));

            TestCase::assertEquals($expected, $res->getBody(), 'Response body does not match.');
        }

        public function testApiPatchOne() {
            $id = Pictures::get()[0]['id'];
            $res = RouteController::handleRequest('patch', "/api/pictures/$id");
            $expected = json_encode(Pictures::getOne($id));

            TestCase::assertEquals($expected, $res->getBody(), 'Response body does not match.');
        }

        public function testErrorResponseNoStatusCode() {
            $res = null;
            $res = RouteController::handleRequest('get', "/error");

            TestCase::assertEquals(500, $res->getStatus());
        }
    }