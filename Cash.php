<?php

namespace Store;

class Cash implements Entity
{
    const STATE_WAIT = 1;
    const STATE_CHECK_GOODS = 2;
    const STATE_PAYMENT = 3;

    /** @var int */
    protected $number;

    /** @var int */
    protected $maxWait;

    /** @var int */
    protected $timeWait = 0;

    /** @var int */
    protected $timeFirstWait = 0;

    /** @var int */
    protected $state;

    /** @var int */
    protected $timeThenPayment;

    /** @var int */
    protected $timeThenPop;

    /** @var int */
    protected $costTimeOneProduct;

    /** @var int */
    protected $costTimePay;

    /** @var Client[] */
    protected $clients = [];

    /** @var Client */
    protected $currentClient = null;

    public function __construct($number, $maxWait = null, $costTimeOneProduct = null, $costTimePay = null)
    {
        $this->number = $number;
        $this->state = self::STATE_WAIT;
        $this->maxWait = $maxWait ? $maxWait : env('MAX_TIME_CLIENT_WAIT');
        $this->costTimeOneProduct = $costTimeOneProduct ? $costTimeOneProduct : env('COST_TIME_CHECK_PRODUCT');
        $this->costTimePay = $costTimePay ? $costTimePay : env('COST_TIME_PAYMENT');
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    public function pushClient(Client $client)
    {
        if ($this->currentClient == null)
            $this->currentClient = $client;

        array_push($this->clients, $client);
        $this->timeWait = 0;
        $this->timeFirstWait = 0;
        $this->state = self::STATE_CHECK_GOODS;
    }

    /**
     * @inheritDoc
     */
    public function update($now)
    {
        if ($this->timeThenPop == null || $this->timeThenPayment == null)
            $this->updateClient($now);

        if ($this->state == self::STATE_WAIT) {
            if (!$this->timeFirstWait) $this->timeFirstWait = $now;
            $this->timeWait = $now - $this->timeFirstWait;
        } else if ($this->currentClient) {
            switch ($this->state) {
                case self::STATE_CHECK_GOODS:
                    if ($now >= $this->timeThenPayment)
                        $this->state = self::STATE_PAYMENT;
                    break;
                case self::STATE_PAYMENT:
                    if ($now >= $this->timeThenPop) {
                        $client = $this->popClient();
                        $client->endPayment();
                        $this->updateClient($now);
                    }
                    break;
            }
        }
    }

    public function updateClient($now)
    {
        if (count($this->clients) == 0) {
            $this->state = self::STATE_WAIT;
            if (!$this->timeFirstWait) $this->timeFirstWait = $now;
            return;
        }

        $client = $this->clients[0];
        $this->timeThenPayment = $now + $client->countProducts() * $this->costTimeOneProduct;
        $this->timeThenPop = $this->timeThenPayment + $this->costTimePay;
    }

    /**
     * @return mixed|Client
     */
    protected function popClient()
    {
        return array_shift($this->clients);
    }

    public function countClients()
    {
        return count($this->clients);
    }

    public function isEmpty()
    {
        return count($this->clients) == 0;
    }

    public function isCanClose()
    {
        return $this->isEmpty() && $this->timeWait >= $this->maxWait;
    }
}
