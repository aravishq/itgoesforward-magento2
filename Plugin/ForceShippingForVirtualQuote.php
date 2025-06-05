<?php
namespace Aravis\ItGoesForward\Plugin;

use Magento\Quote\Model\Quote;

class ForceShippingForVirtualQuote
{
    public function afterIsVirtual(Quote $subject, $result)
    {
        foreach ($subject->getAllItems() as $item) {
            if ($item->getSku() === 'it-goes-forward') {
                return false;
            }
        }

        return $result;
    }
}
