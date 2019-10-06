<?php
namespace Store\tests;

use Store\Client;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    public function testIsReady()
    {
        $time = 100;
        $client = new Client($time);
        $time += env('MAX_TIME_CLIENT_READY');

        $client->update($time);
        $this->assertTrue($client->isReady());
    }
}