<?php

namespace Aravis\ItGoesForward\Plugin;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\Serialize\Serializer\Json;

class QuoteItemPlugin
{
    private $productRepo;
    private $jsonSerializer;

    /**
     * @param ProductRepositoryInterface $productRepo
     * @param Json $jsonSerializer
     */
    public function __construct(
        ProductRepositoryInterface $productRepo,
        Json $jsonSerializer
    ) {
        $this->productRepo = $productRepo;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * After plugin on getProduct() to override product data if it's the IGF SKU.
     * This is just for the Cart and Checkout.
     */
    public function afterGetProduct(QuoteItem $subject, $result)
    {
        // If there's no product or SKU isn't IGF, do nothing.
        if (!$result || $result->getSku() !== 'it-goes-forward') {
            return $result;
        }

        // Grab the buy request
        $infoBuyRequest = $subject->getOptionByCode('info_buyRequest');
        if (!$infoBuyRequest) {
            return;
        }

        $infoBuyRequestValue = $infoBuyRequest->getValue();
        $buyRequestData = [];

        try {
            $buyRequestData = $this->jsonSerializer->unserialize($infoBuyRequestValue);
        } catch (\InvalidArgumentException $e) {
            // If not valid JSON, fallback to PHP unserialize
            $buyRequestData = @unserialize($infoBuyRequestValue) ?: [];
        }

        if (empty($buyRequestData['options']['original_sku'])) {
            return;
        }

        $originalSku = $buyRequestData['options']['original_sku'];

        // Load the actual original product.
        try {
            $originalProduct = $this->productRepo->get($originalSku);

            // Overwrite image data. This will affect any calls that use $result->getData('thumbnail'), etc.
            $result->setData('small_image', $originalProduct->getData('small_image'));
            $result->setData('thumbnail', $originalProduct->getData('thumbnail'));
            $result->setData('image', $originalProduct->getData('image'));
            $result->setData('color', $originalProduct->getData('color'));

            $result->setName($originalProduct->getName());
        } catch (\Exception $e) {
            // If loading fails, do nothing.
        }

        return $result;
    }
}
