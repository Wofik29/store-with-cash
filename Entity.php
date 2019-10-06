<?php


namespace Store;

/**
 * Interface Entity
 * @package Store
 */
interface Entity
{

    /**
     * @param int $nowTime time in seconds from start day
     * @return mixed
     */
    public function update($nowTime);
}