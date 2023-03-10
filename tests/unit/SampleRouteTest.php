<?php
declare(strict_types=1);
use Phaz\Route;
use PHPUnit\Framework\TestCase;

final class SampleRouteTest extends TestCase {
    public function testCanBeGetValidResponseRoute(): void
    {
        Route::get('/', function() {
            return "Test";
        });

        Route::get('/home', function() {
            return "Welcome to home page";
        });

        print(Route::$routes);
        $expected = [
            ['uri' => '/', 'handler' => 'Test' , 'method' => 'GET'],
            ['uri' => '/home', 'handler' => 'Welcome to home page' , 'method' => 'GET']
        ];

        $this->assertSame($expected, Route::$routes);
    }

    
}