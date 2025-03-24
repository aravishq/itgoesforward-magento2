<?php
declare(strict_types=1);

namespace Aravis\ItGoesForward\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class HandleCartAddAfter implements ObserverInterface
{
    public function __construct(
        protected RequestInterface $request,
        protected SerializerInterface $serializer
    ) {}

    /**
     * This method makes sure the IGF option is added to the quote item as well
     * for easier access in the cart and checkout.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getEvent()->getQuoteItem();
        $product = $quoteItem->getProduct();
        $itGoesForward = $product->getCustomOption('it_goes_forward');

        if ($itGoesForward && $itGoesForward->getValue()) {
            $quoteItem->setQty(1);
            $quoteItem->setData('it_goes_forward', $itGoesForward->getValue());
            $price = $product->getFinalPrice() * 0.95;
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->getProduct()->setIsSuperMode(true);
        }
    }
}
