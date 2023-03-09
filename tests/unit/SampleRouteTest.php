<?php
declare(strict_types=1);

use Phaz\Routing\Route;
use PHPUnit\Framework\TestCase;

final class SampleRouteTest extends TestCase {
    public function testCanBeGetValidResponseRoute(): void
    {
        $uri = '/home';
        Route::get($uri, function (){
            return "Hello";
        });

        $result = Route::execute();
        $this->assertSame("Hello", $result);
    }
}