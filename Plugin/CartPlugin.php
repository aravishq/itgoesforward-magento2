<?php

namespace Aravis\ItGoesForward\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Exception\LocalizedException;

class CartPlugin
{
    private $productRepo;
    private $eavConfig;
    private $jsonSerializer;

    public function __construct(
        ProductRepositoryInterface $productRepo,
        EavConfig $eavConfig,
        JsonSerializer $jsonSerializer
    ) {
        $this->productRepo    = $productRepo;
        $this->eavConfig      = $eavConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null)
    {
        if (!is_array($requestInfo) || empty($requestInfo['it_goes_forward'])) {
            return [$productInfo, $requestInfo];
        }

        $igf = $this->productRepo->get('it-goes-forward');
        $itGoesForwardId = (string) $requestInfo['it_goes_forward'];

        if (empty($requestInfo['qty'])) {
            $requestInfo['qty'] = 1;
        }

        if (!isset($requestInfo['options'])) {
            $requestInfo['options'] = [];
        }

        // Add de original SKU to the request info options
        $originalSku = is_object($productInfo) ? $productInfo->getSku() : (string)$productInfo;
        $requestInfo['options']['original_sku'] = $originalSku;

        // Check if the product is already in the cart
        $quote = $subject->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($item->getProduct()->getSku() === 'it-goes-forward' &&
                $item->getOptionByCode('info_buyRequest')
            ) {
                $buyRequest = $item->getOptionByCode('info_buyRequest')->getValue();
                $buyRequestData = $this->jsonSerializer->unserialize($buyRequest);

                if (!empty($buyRequestData['it_goes_forward']) &&
                    (string) $buyRequestData['it_goes_forward'] === $itGoesForwardId
                ) {
                    throw new LocalizedException(__('This forwarded product is already in your cart.'));
                }
            }
        }

        return [$igf, $requestInfo];
    }

    public function afterAddProduct(Cart $subject, $result, $productInfo, $requestInfo = null)
    {
        if (!$result || empty($requestInfo['it_goes_forward']) || empty($requestInfo['options']['original_sku'])) {
            return $result;
        }

        $items = $subject->getQuote()->getAllVisibleItems();
        $item  = end($items);

        if (!$item || $item->getProduct()->getSku() !== 'it-goes-forward') {
            return $result;
        }

        $original = $this->productRepo->get($requestInfo['options']['original_sku']);
        $originalPrice = $original->getPriceInfo()->getPrice('final_price')->getValue();

        $item->getProduct()->setIsSuperMode(true);
        $item->setRedirectUrl($original->getProductUrl());
        $item->setPrice($originalPrice * 0.95);
        $item->setCustomPrice($originalPrice * 0.95);
        $item->setOriginalCustomPrice($originalPrice * 0.95);

        $additionalOptions = [];

        if (!empty($requestInfo['super_attribute'])) {
            foreach ($requestInfo['super_attribute'] as $attrId => $optionId) {
                $attribute = $this->eavConfig->getAttribute('catalog_product', $attrId);
                $label     = $attribute->getStoreLabel();
                $value     = $attribute->getSource()->getOptionText($optionId);
                $additionalOptions[] = [
                    'label' => $label ?: ('Attribute ' . $attrId),
                    'value' => is_array($value) ? implode('/', $value) : $value
                ];
            }
        }

        $item->addOption([
            'code'  => 'additional_options',
            'value' => $this->jsonSerializer->serialize($additionalOptions)
        ]);

        return $result;
    }
}
