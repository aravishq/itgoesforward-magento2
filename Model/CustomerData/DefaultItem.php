<?php
declare(strict_types=1);

namespace Aravis\ItGoesForward\Model\CustomerData;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Escaper|null $escaper
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface|null $itemResolver
     * @param Json|null $jsonSerializer
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Escaper $escaper = null,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver = null,
        Json $jsonSerializer = null
    ) {
        parent::__construct(
            $imageHelper,
            $msrpHelper,
            $urlBuilder,
            $configurationPool,
            $checkoutHelper,
            $escaper,
            $itemResolver
        );
        $this->jsonSerializer = $jsonSerializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @inheritdoc
     */
    protected function doGetItemData()
    {
        $result = parent::doGetItemData();

        $buyRequestOption = $this->item->getOptionByCode('info_buyRequest');
        
        if ($buyRequestOption) {
            $buyRequest = $buyRequestOption->getValue();
            $buyRequestData = $this->jsonSerializer->unserialize($buyRequest);

            if (!empty($buyRequestData['it_goes_forward'])) {
                $result['it_goes_forward'] = $buyRequestData['it_goes_forward'];
            }
        }

        return $result;
    }
} 