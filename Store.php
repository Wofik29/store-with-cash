<?php


namespace Store;

/**
 * Class Store
 * @package Store
 */
class Store
{
    /** @var Cash[] */
    protected $activeCashes = [];

    /** @var Cash[] */
    protected $closeCashes = [];

    /** @var Client[] */
    protected $clients = [];

    /** @var int */
    protected $timeCurrent = 0;

    /** @var int */
    protected $timeNextClient = 0;

    /** @var int */
    protected $countCashes = 0;

    /** @var int */
    protected $maxCashes;

    /** @var bool */
    protected $beforePeak = true;

    /** @var int */
    protected $maxRandClient = 2;

    /** @var int */
    protected $servedClients = 0;

    /**
     * Store constructor.
     * @param int $maxCashes
     * @throws \Exception
     */
    public function __construct($maxCashes)
    {
        if ($maxCashes < 1)
            throw new \Exception('Count cash must be one or greats!');

        $this->maxCashes = $maxCashes;
        $this->clients[] = new Client($this->timeCurrent);
        $this->timeNextClient = $this->timeCurrent + rand(0, 10);
        $this->closeCashes[++$this->countCashes] = new Cash($this->countCashes);
    }

    /**
     * Just start work Store.
     * End then time is over.
     */
    public function run()
    {
        echo '===================' . PHP_EOL;
        echo 'Store is open and start receive visitors' . PHP_EOL;
        echo '===================' . PHP_EOL;
        while ($this->isNext()) {
            $this->printStatus();

            // if exist ready client, send that to cash
            foreach ($this->clients as $client) {
                if ($client->isReady())
                    $client->goPayment($this->getBetterCash());
            }

            // updates all entity: cashes and clients
            $this->update();
            $this->checkEmptyCashes();
            $this->timeCurrent++;
        }

        $this->printStatus(true);

        echo '===================' . PHP_EOL;
        echo 'Store Closed' . PHP_EOL;
        echo '===================' . PHP_EOL;
    }

    /**
     * Just print status general info and by cashes
     * @param bool $force is must print or by time
     */
    protected function printStatus($force = false)
    {
        if ($force || $this->timeCurrent > 0 && $this->timeCurrent % env('TICKS_AS_HOUR') == 0) {
            echo '------------------------' . PHP_EOL;
            echo 'Time: ' . ($this->timeCurrent / env('TICKS_AS_HOUR')) . ' hour(s) from open Store' . PHP_EOL;
            echo 'Count Active Cash: ' . count($this->activeCashes) . PHP_EOL;
            echo 'Count All Cash: ' . (count($this->activeCashes) + count($this->closeCashes)) . PHP_EOL;
            echo 'Count All Clients: ' . count($this->clients) . PHP_EOL;
            echo 'Served Clients: ' . $this->servedClients . PHP_EOL;
            echo '------------------------' . PHP_EOL;
            echo '--- Status By Cashes ---' . PHP_EOL;
            echo '------------------------' . PHP_EOL;
            foreach ($this->activeCashes as $cash) {
                echo 'Cash Number ' . $cash->getNumber() . PHP_EOL;
                echo 'Count Clients: ' . $cash->countClients() . PHP_EOL;
                echo '====================' . PHP_EOL;
            }

            if (count($this->activeCashes) == 0)
                echo 'No active cashes' . PHP_EOL;

            echo PHP_EOL . PHP_EOL;
        }
    }

    /**
     * Check exist clients and will come more
     * @return bool
     */
    protected function isNext()
    {
        return $this->maxRandClient > 0 || ($this->maxRandClient <= 0 && count($this->clients) > 0);
    }

    /**
     * Just update all modules and entities
     */
    protected function update()
    {
        $this->generateNewClients();
        $this->updates($this->clients);
        $this->updates($this->activeCashes);
        $this->checkPayedClients();
    }

    /**
     * Just generate new clients, if time come
     * And set new time, when clients need come
     */
    protected function generateNewClients()
    {
        if ($this->timeCurrent >= $this->timeNextClient) {
            if (count($this->clients) >= env('MAX_COUNT_CLIENT'))
                $this->beforePeak = false;

            $this->maxRandClient = $this->maxRandClient + ($this->beforePeak ? 1 : -1);
            $countNew = rand(0, $this->maxRandClient);
            for ($i = 0; $i < $countNew; $i++) {
                $this->clients[] = new Client($this->timeCurrent);
            }

            $this->timeNextClient = $this->timeCurrent + rand(0, 10);
        }
    }

    /**
     * Check if Cash empty and waited time - close that
     */
    protected function checkEmptyCashes()
    {
        $canClose = [];
        foreach ($this->activeCashes as $cash) {
            if ($cash->isCanClose())
                $canClose[] = $cash->getNumber();
        }

        foreach ($canClose as $number) {
            $cash = $this->activeCashes[$number];
            $this->closeCashes[$number] = $cash;
            unset($this->activeCashes[$number]);
        }
    }

    /**
     * Check client, which already payed and drop from Store
     */
    protected function checkPayedClients()
    {
        $isSuccess = [];
        foreach ($this->clients as $key => $client) {
            if ($client->isPayments())
                $isSuccess[] = $key;
        }

        $this->servedClients += count($isSuccess);
        foreach ($isSuccess as $key) {
            unset($this->clients[$key]);
        }
    }

    /**
     * @param Entity[] $entities
     */
    protected function updates($entities)
    {
        foreach ($entities as $entity) {
            $entity->update($this->timeCurrent);
        }
    }

    /**
     * @return Cash
     */
    protected function getBetterCash()
    {
        $min = INF;
        $betterCash = null;
        foreach ($this->activeCashes as $cash) {
            if ($min > $cash->countClients()) {
                $min = $cash->countClients();
                $betterCash = $cash;
            }
        }

        // If exist cash with small queue
        if ($min < 5 && $betterCash)
            return $betterCash;

        // else check closes Cash
        if (count($this->closeCashes)) {
            $betterCash = array_pop($this->closeCashes);
            $this->activeCashes[$betterCash->getNumber()] = $betterCash;
            return $betterCash;
        }

        // otherwise create new cash, if overflow limit
        if ($this->maxCashes > $this->countCashes) {
            $betterCash = new Cash(++$this->countCashes);
            $this->activeCashes[$betterCash->getNumber()] = $betterCash;
        }

        return $betterCash;
    }
}