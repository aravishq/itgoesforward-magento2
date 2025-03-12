<?php
declare(strict_types=1);

namespace Aravis\ItGoesForward\Model;

use Aravis\ItGoesForward\Api\ListingsApiInterface;
use Aravis\ItGoesForward\Service\ApiService;

class ListingsApi implements ListingsApiInterface
{
    /**
     * @param \Aravis\ItGoesForward\Service\ApiService $apiService
     */
    public function __construct(
        protected ApiService $apiService
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getListingsBySku(string $sku): array
    {
        $listings = $this->apiService->getListingBySku($sku);

        return array_filter($listings, function (array $listing) {
            return mb_strtolower($listing['status']) === 'available';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getListingsByProductIds(string $ids): array
    {
        // Convert comma separated string to array
        $ids = array_filter(array_map('trim', explode(",", $ids)));

        if(!empty($_SERVER['HTTP_CLIENT_IP'])){
            //ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            //ip pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        if (count($ids) === 1) {
            $listings = $this->apiService->getListingByProductId($ids[0], $ip);
        } else {
            $listings = $this->apiService->getListingsByProductIds($ids, $ip);
        }

        return array_filter($listings, function (array $listing) {
            return mb_strtolower($listing['status']) === 'available';
        });
    }
}
