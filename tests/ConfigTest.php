<?php
    use Router\Tests\Extensions\TestCase;
    use Router\Controllers\ConfigController;
    use Router\Config;

    final class ConfigTest extends TestCase {
        public function testCustomConfigFromController() {
            TestCase::assertEquals('Test Suite', ConfigController::find('name'), "Config field 'name' does not return the correct value.");
        }

        public function testCustomConfigFromHelper() {
            TestCase::assertEquals('Test Suite', Config::get('name'), "Config field 'name' does not return the correct value.");
        }
    }