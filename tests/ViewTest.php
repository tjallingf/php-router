<?php declare(strict_types=1);
    use Router\Tests\Extensions\TestCase;
    use Router\Controllers\ViewController;
    use Router\Models\ViewModel;
    use Router\Helpers\Dom;
    use PHPHtmlParser\Dom\Node\HtmlNode;

    final class ViewTest extends TestCase {
        private static ViewModel $view;
        private static Dom $view_dom;

        public static function setUpBeforeClass(): void {
            self::$view = ViewController::find('test', [
                'name' => 'John Doe'
            ]);

            self::$view_dom = new Dom(self::$view->render());
        }

        public function testStyledComponents(): void {
            $h1 = self::$view_dom->find('h1')[0];
            TestCase::assertInstanceOf(HtmlNode::class, $h1, 
                "Dom does not contain <h1>.");

            TestCase::assertTrue($h1->hasAttribute('style'), 
                "<h1> does not have attribute 'style'.");

            TestCase::assertMatchesRegularExpression('/color\:\s*red;/', $h1->getAttribute('style'), 
                "Style property 'color' of <h1> is not 'red'.");
        }

        public function testComponentsWithChildren(): void {
            $h1 = self::$view_dom->find('h1')[0];
            $span = $h1->find('span#name')[0];

            TestCase::assertInstanceOf(HtmlNode::class, $span, 
                '<h1> does not contain <span id="name">.');

            TestCase::assertEquals("John Doe", trim($span->text), 
                '<span id="name"> does not have the correct content.');
        }
    }