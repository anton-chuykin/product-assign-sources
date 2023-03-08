<?php

declare(strict_types=1);

namespace PineappleDevelopment\ProductAssignSources\Service;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

class Publisher
{
    const TOPIC_PRODUCT_CREATE = 'pineapple_development.assign.sources.product.create';

    private Json $serializer;
    private PublisherInterface $publisher;

    /**
     * @param Json $serializer
     * @param PublisherInterface $publisher
     */
    public function __construct(
        Json $serializer,
        PublisherInterface $publisher
    ) {
        $this->serializer = $serializer;
        $this->publisher = $publisher;
    }

    /**
     * @param Order $order
     * @return void
     */
    public function publishProductCreate($product)
    {
        $data = [
            'sku' => $product->getSku()
        ];

        $this->publisher->publish(
            static::TOPIC_PRODUCT_CREATE,
            $this->serializer->serialize($data)
        );
    }
}
