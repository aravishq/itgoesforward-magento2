<?php
declare(strict_types=1);

namespace Aravis\ItGoesForward\Observer;

use Aravis\ItGoesForward\Service\ApiService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderStatusChanged implements ObserverInterface
{
    /**
     * @param \Aravis\ItGoesForward\Service\ApiService $apiService
     */
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getEvent()->getOrder();

        $newState = $order->getState();
        $previousState = $order->getOrigData('state');

        // @todo: have these status be configurable from admin
        if (($newState === Order::STATE_PROCESSING || $newState === Order::STATE_COMPLETE) &&
            ($previousState !== Order::STATE_PROCESSING && $previousState !== Order::STATE_COMPLETE)) {

            foreach ($order->getAllItems() as $item) {
                $productOptions = $item->getProductOptions();

                if (!isset($productOptions['info_buyRequest']['it_goes_forward'])) {
                    continue;
                }

                $itGoesForward = $productOptions['info_buyRequest']['it_goes_forward'];
                $this->apiService->createOrderForListing($order, $item, $itGoesForward);
            }
        }
    }
}
