<?php
declare(strict_types=1);

namespace Aravis\ItGoesForward\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderItemInterface;

use function is_array;
use function json_decode;

class ApiService
{
    protected const API_KEY = 'it_goes_forward/api/api_key';
    protected const API_URL = 'it_goes_forward/api/base_uri';
    protected const API_STATUS = 'it_goes_forward/api/status';

    public function __construct(
        protected ScopeConfigInterface $scopeConfig
    ) {}

    public function getListingBySku(string $sku): array
    {
        try {
            $result = $this->getClient()->get("listings/sku/{$sku}");

            return json_decode($result->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return [];
        }
    }

    public function getListingByProductId(string $productId, string $ip): array
    {
        try {
            $result = $this->getClient()->get("listings/product/{$productId}?ip=$ip");

            return json_decode($result->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return [];
        }
    }

    public function getListingsByProductIds(array $productIds, string $ip): array
    {
        try {
            // API currently doesn't support URL encoded query string, this is a temporary workaround
            $query = implode(',', $productIds);
            $result = $this->getClient()->get("listings/product?ids=$query&ip=$ip");

            return json_decode($result->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return [];
        }
    }

    public function setListingStatus(string $listingId, string $status): void
    {
        try {
            $result = $this->getClient()->put("listings/{$listingId}", [
                RequestOptions::JSON => [
                    'status' => $status,
                ],
            ]);
        } catch (GuzzleException $e) {
            // @todo: Logging
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order
     * @param \Magento\Sales\Api\Data\OrderItemInterface                        $orderItem
     * @param string                                                            $listingId
     *
     * @return bool
     */
    public function createOrderForListing(
        OrderInterface|Order $order,
        OrderItemInterface $orderItem,
        string $listingId,
    ): bool {
        $shippingAddress = $order->getShippingAddress();

        $data = [
            'listingId' => (int) $listingId,
            'webshopOrderId' => $order->getId(),
            'webshopOrderItemId' => $orderItem->getItemId(),
            'customer' => [
                'language' => $order->getStore()->getCode(),
                'firstName' => $shippingAddress->getFirstname(),
                'lastName' => $shippingAddress->getLastname(),
                'phone' => $shippingAddress->getTelephone(),
                'email' => $order->getCustomerEmail(),
                'customerStatus' => 1,
                'address' => [
                    'street' => $shippingAddress->getStreetLine(1),
                    'houseNumber' => $shippingAddress->getStreetLine(2) . ' ' . $shippingAddress->getStreetLine(3),
                    'city' => $shippingAddress->getCity(),
                    'postalCode' => $shippingAddress->getPostcode(),
                    'country' => $shippingAddress->getCountryId()
                ],
            ],
        ];

        try {
            $result = $this->getClient()->post("orders", [
                RequestOptions::JSON => $data,
            ]);

            return true;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function getClient(): Client
    {
        return new Client([
            'base_uri' => $this->scopeConfig->getValue(self::API_URL),
            RequestOptions::HEADERS => [
                'api-key' => $this->scopeConfig->getValue(self::API_KEY),
                'User-Agent' => 'Aravis-ItGoesForward/0.1.0',
            ],
        ]);
    }
}
