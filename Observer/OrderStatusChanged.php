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

        $currentState = $order->getState();
        $previousState = $order->getOrigData('state');

        // @todo: have these status be configurable from admin
        if ($currentState === Order::STATE_PROCESSING
            && $previousState != Order::STATE_PROCESSING) {

            foreach ($order->getAllItems() as $item) {
                if (!empty($item->getItGoesForward())) {
                    $this->apiService->createOrderForListing($order, $item, $item->getItGoesForward());

                    // Set quantity of item to 0, so it doesn't get exported to the ERP
                    $item->setQtyOrdered(0);
                }
            }
        }
    }
}
