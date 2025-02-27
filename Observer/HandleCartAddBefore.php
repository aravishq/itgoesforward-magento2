<?php
declare(strict_types=1);

namespace Aravis\ItGoesForward\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class HandleCartAddBefore implements ObserverInterface
{
    public function __construct(
        protected RequestInterface $request,
        protected SerializerInterface $serializer
    ) {}

    /**
     * This method adds the IGF option to the product before it's added to the cart.
     * This is to ensure uniqueness of the quote item.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $itGoesForward = $this->request->getParam('it_goes_forward');

        if ($itGoesForward) {
            $product = $observer->getProduct();
            $product->addCustomOption('it_goes_forward', $itGoesForward);
        }
    }
}
