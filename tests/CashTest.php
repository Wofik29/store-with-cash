<?php


namespace Store\tests;


use PHPUnit\Framework\TestCase;
use Store\Cash;
use Store\Client;

class CashTest extends TestCase
{
    public function testGetNumber()
    {
        $number = 1;
        $cash = new Cash($number);

        $this->assertEquals($number, $cash->getNumber());
    }

    public function testEmpty()
    {
        $number = 1;
        $cash = new Cash($number);
        $this->assertTrue($cash->isEmpty());

        $client = new Client(100);
        $cash->pushClient($client);
        $this->assertEquals(1, $cash->countClients());
        $this->assertFalse($cash->isEmpty());
    }

    public function testIsCanClose()
    {
        $number = 1;
        $time = 100;
        $maxWait = 10;
        $cash = new Cash($number, $maxWait);

        $this->assertFalse($cash->isCanClose());
        $cash->update($time);
        $time += $maxWait + 1;
        $cash->update($time);
        $this->assertTrue($cash->isCanClose());
    }

    public function testStates()
    {
        $number = 1;
        $time = 100;
        $maxWait = 10;
        $costTimeOneProduct = 10;
        $costTimePay = 10;

        $cash = new Cash($number, $maxWait, $costTimeOneProduct, $costTimePay);
        $propertyState = new \ReflectionProperty(Cash::class, 'state');
        $propertyWaitCheck = new \ReflectionProperty(Cash::class, 'timeThenPayment');
        $propertyState->setAccessible(true);
        $propertyWaitCheck->setAccessible(true);

        $client = new Client($time);
        $cash->update($time);

        $time += 1;
        $client->goPayment($cash);
        $cash->update($time);
        $this->assertEquals(Cash::STATE_CHECK_GOODS, $propertyState->getValue($cash));

        $time += $propertyWaitCheck->getValue($cash) + 1;
        $cash->update($time);
        $this->assertEquals(Cash::STATE_PAYMENT, $propertyState->getValue($cash));

        $time += $costTimePay;
        $cash->update($time);
        $this->assertEquals( Cash::STATE_WAIT, $propertyState->getValue($cash));
    }

}