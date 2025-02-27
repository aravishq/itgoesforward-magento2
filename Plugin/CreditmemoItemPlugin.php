<?php

declare(strict_types=1);

namespace Aravis\ItGoesForward\Plugin;

use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;

class CreditmemoItemPlugin
{
    /**
     * @param CreditmemoItem $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundCalcRowTotal(
        CreditmemoItem $subject,
        callable $proceed
    ) {
        $item = $subject->getOrderItem();

        if (!$item->getItGoesForward()) {
            return $proceed();
        }

        $creditmemo = $subject->getCreditmemo();
        $orderItem = $subject->getOrderItem();
        $orderItemQtyInvoiced = $orderItem->getQtyInvoiced();

        $rowTotal = $orderItem->getRowInvoiced() - $orderItem->getAmountRefunded();
        $baseRowTotal = $orderItem->getBaseRowInvoiced() - $orderItem->getBaseAmountRefunded();
        $rowTotalInclTax = $orderItem->getRowTotalInclTax();
        $baseRowTotalInclTax = $orderItem->getBaseRowTotalInclTax();

        $qty = $orderItem->getQtyOrdered();
        if (!$subject->isLast() && $orderItemQtyInvoiced > 0 && $qty >= 0) {
            $availableQty = $orderItemQtyInvoiced - $orderItem->getQtyRefunded();
            $rowTotal = $creditmemo->roundPrice($rowTotal / $availableQty * $qty);
            $baseRowTotal = $creditmemo->roundPrice($baseRowTotal / $availableQty * $qty, 'base');
        }

        $subject->setRowTotal($rowTotal);
        $subject->setBaseRowTotal($baseRowTotal);

        if ($rowTotalInclTax && $baseRowTotalInclTax) {
            $subject->setRowTotalInclTax(
                $creditmemo->roundPrice($rowTotalInclTax * $qty, 'including')
            );
            $subject->setBaseRowTotalInclTax(
                $creditmemo->roundPrice($baseRowTotalInclTax * $qty, 'including_base')
            );
        }

        return $this;
    }
}
