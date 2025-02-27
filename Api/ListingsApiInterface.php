<?php
namespace Aravis\ItGoesForward\Api;

interface ListingsApiInterface
{
    /**
     * GET listings for a given SKU
     * @param string $sku
     * @return array
     */
    public function getListingsBySku(string $sku): array;

    /**
     * GET listings for given product IDs
     * @param string $ids On or more product IDs, comma separated
     * @return array
     */
    public function getListingsByProductIds(string $ids): array;
}
