<?php

declare(strict_types=1);

namespace Aravis\ItGoesForward\Plugin;

use Magento\Weee\Model\Total\Creditmemo\Weee as CreditmemoWeee;
use Magento\Sales\Model\Order\Creditmemo;

class CreditmemoWeeePlugin
{
    /**
     * @param CreditmemoWeee $subject
     * @param callable $proceed
     * @param Creditmemo $creditmemo
     *
     * @return mixed
     */
    public function aroundCollect(
        CreditmemoWeee $subject,
        callable $proceed,
        Creditmemo $creditmemo
    ) {
        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->getItGoesForward()) {
                return $subject;
            }
        }

        return $proceed($creditmemo);
    }
}
