<?php

namespace Aravis\ItGoesForward\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Aravis\ItGoesForward\Api\OrderByIncrementIdInterface;

class OrderByIncrementId implements OrderByIncrementIdInterface
{
    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        protected OrderCollectionFactory $orderCollectionFactory,
    ) {}

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getOrderByIncrementId(string $incrementId): OrderInterface
    {
        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('increment_id', $incrementId)
            ->setPageSize(1);

        /** @var OrderInterface $order */
        $order = $orderCollection->getFirstItem();

        if (!$order->getEntityId()) {
            throw new NoSuchEntityException(__('Order with increment ID "%1" does not exist.', $incrementId));
        }

        return $order;
    }
}
