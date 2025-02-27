<?php

declare(strict_types=1);

namespace Aravis\ItGoesForward\Plugin;

use Aravis\ItGoesForward\Service\ApiService;
use Magento\CatalogInventory\Model\StockManagement;
use Magento\CatalogInventory\Observer\ItemsForReindex;
use Magento\CatalogInventory\Observer\ProductQty;
use Magento\CatalogInventory\Observer\SubtractQuoteInventoryObserver;
use Magento\Framework\Event\Observer as EventObserver;

class StockDeductionPlugin
{
    public function __construct(
        protected ProductQty $productQty,
        protected StockManagement $stockManagement,
        protected ItemsForReindex $itemsForReindex,
        protected ApiService $apiService
    ) {
    }

    /**
     * @param SubtractQuoteInventoryObserver $subject
     * @param callable                       $proceed
     * @param EventObserver                  $observer
     *
     * @return mixed
     *
     * @throws \Magento\CatalogInventory\Model\StockStateException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute(
        SubtractQuoteInventoryObserver $subject,
        callable $proceed,
        EventObserver $observer
    ) {
        $quote = $observer->getEvent()->getQuote();
        $items = $quote->getAllItems();

        if ($quote->getInventoryProcessed()) {
            return $this;
        }

        foreach ($items as $index => $item) {
            if ($item->getItGoesForward()) {
                unset($items[$index]);

                // IGF attribute is only set on the child, so remove parent
                $parentId = $item->getParentItemId();

                if ($parentId) {
                    foreach ($items as $parentIndex => $parentItem) {
                        if ($parentItem->getId() == $parentId) {
                            unset($items[$parentIndex]);
                        }
                    }
                }

                $this->apiService->setListingStatus($item->getItGoesForward(), 'Pending');
            }
        }

        $qtyItems = $this->productQty->getProductQty($items);

        $itemsForReindex = $this->stockManagement->registerProductsSale(
            $qtyItems,
            $quote->getStore()->getWebsiteId()
        );
        if (count($itemsForReindex)) {
            $this->itemsForReindex->setItems($itemsForReindex);
        }
        $quote->setInventoryProcessed(true);

        return $this;
    }
}
