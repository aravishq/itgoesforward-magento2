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
            //TODO: Check with VPN if it correctly identifies the country adn throws an error
            // TODO: Inspect how IPs change in the error log - var/log/exception.log
            $objectManager = ObjectManager::getInstance();
            $remoteAddress = $objectManager->get(RemoteAddress::class);
            $ip = $remoteAddress->getRemoteAddress();
            $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));

            //Test with VPN
            if($ip_data && $ip_data->geoplugin_countryCode != "NL"){
                throw new \Exception("You are not allowed to add this product to the cart: " . $ip_data->geoplugin_countryCode . " With IP: " . $ip);
            }
            $product = $observer->getProduct();
            $product->addCustomOption('it_goes_forward', $itGoesForward);
        }
    }
}
