<?php

namespace Aravis\ItGoesForward\Api;

use Magento\Sales\Api\Data\OrderInterface;

interface OrderByIncrementIdInterface
{
    /**
     * Get order by increment id
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    public function getOrderByIncrementId(string $incrementId): OrderInterface;
}
