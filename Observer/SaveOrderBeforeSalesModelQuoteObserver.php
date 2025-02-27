<?php
declare(strict_types=1);

namespace Aravis\ItGoesForward\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SaveOrderBeforeSalesModelQuoteObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     *
     * @return \Aravis\ItGoesForward\Observer\SaveOrderBeforeSalesModelQuoteObserver
     */
    public function execute(Observer $observer)
    {
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');

        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        foreach ($order->getAllItems() as $orderItem) {
            $quoteItem = $quote->getItemById($orderItem->getQuoteItemId());
            $orderItem->setItGoesForward($quoteItem->getItGoesForward());
        }

        return $this;
    }
}
