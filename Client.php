<?php

namespace Store;

class Client implements Entity
{
    const STATUS_SELECT = 1;
    const STATUS_SUCCESS_SELECT = 2;
    const STATUS_IN_CASH = 3;
    const STATUS_SUCCESS_PAYMENT = 4;

    protected $products = 0;
    protected $timeEnd = 0;
    protected $status;
    protected $cash = null;

    /**
     * Client constructor.
     * @param int $startTime
     */
    public function __construct($startTime)
    {
        $this->timeEnd = $startTime + rand(1, env('MAX_TIME_CLIENT_READY'));
        $this->products = rand(1, env('MAX_PRODUCT_IN_CLIENT'));
        $this->status = self::STATUS_SELECT;
    }

    /**
     * @inheritDoc
     */
    public function update($nowTime)
    {
        if ($this->status === self::STATUS_SELECT && $nowTime >= $this->timeEnd)
            $this->status = self::STATUS_SUCCESS_SELECT;
    }

    /**
     * @param Cash $cash
     */
    public function goPayment($cash)
    {
        $cash->pushClient($this);
        $this->cash = $cash;
        $this->status = self::STATUS_IN_CASH;
    }

    public function endPayment()
    {
        $this->status = self::STATUS_SUCCESS_PAYMENT;
    }

    public function isReady()
    {
        return $this->status === self::STATUS_SUCCESS_SELECT;
    }

    public function isInCash()
    {
        return $this->status === self::STATUS_IN_CASH;
    }

    public function isPayments()
    {
        return $this->status === self::STATUS_SUCCESS_PAYMENT;
    }

    public function countProducts()
    {
        return $this->products;
    }
}