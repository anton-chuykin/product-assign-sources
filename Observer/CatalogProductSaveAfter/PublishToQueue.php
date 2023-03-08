<?php

declare(strict_types=1);

namespace PineappleDevelopment\ProductAssignSources\Observer\CatalogProductSaveAfter;

use PineappleDevelopment\ProductAssignSources\Service\Publisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class PublishToQueue implements ObserverInterface
{
    private Publisher $publisher;

    /**
     * @param Publisher $publisher
     */
    public function __construct(
        Publisher $publisher
    ) {
        $this->publisher = $publisher;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getData('product');

        if (!array_key_exists('sku',$product->getOrigData())) {
            $this->publisher->publishProductCreate($product);
        }
    }
}
