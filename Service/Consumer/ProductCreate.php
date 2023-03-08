<?php

declare(strict_types=1);

namespace PineappleDevelopment\ProductAssignSources\Service\Consumer;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsSave;
use Magento\Inventory\Model\SourceItem\Command\SourceItemsDelete;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

class ProductCreate
{
    private Json $serializer;

    /**
     * @var SourceRepositoryInterface
     */
    private SourceRepositoryInterface $sourceRepository;

    /**
     * @var SourceItemInterfaceFactory
     */
    private SourceItemInterfaceFactory $sourceItemFactory;

    /**
     * @var SourceItemsSave
     */
    private SourceItemsSave $sourceItemsSaver;

    /**
     * @var SourceItemsDelete
     */
    private SourceItemsDelete $sourceItemsDelete;

    /**
     * @var SourceItemRepositoryInterface
     */
    private SourceItemRepositoryInterface $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * ProductRepository constructor.
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSave $sourceItemsSaver
     * @param SourceItemsDelete $sourceItemsDelete
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Json $serializer
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SourceItemInterfaceFactory $sourceItemFactory,
        SourceItemsSave $sourceItemsSaver,
        SourceItemsDelete $sourceItemsDelete,
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Json $serializer
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaver = $sourceItemsSaver;
        $this->sourceItemsDelete = $sourceItemsDelete;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serializer = $serializer;
    }

    /**
     * @param string $request
     * @return void
     */
    public function process(string $request)
    {
        $data = $this->serializer->unserialize($request);
        $sku = $data['sku'];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', $sku, 'eq')->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $sourceCodes = [];
        foreach ($sourceItems as $existingSourceItem) {
            if ($existingSourceItem->getSourceCode()=='default') {
                $this->sourceItemsDelete->execute([$existingSourceItem]);
            } else {
                $sourceCodes[] = $existingSourceItem->getSourceCode();
            }
        }

        if (!count($sourceCodes)) {
            foreach ($this->sourceRepository->getList()->getItems() as $source) {
                if ($source->getSourceCode() === 'default') {
                    continue;
                }
                $sourceItem = $this->sourceItemFactory->create();
                $sourceItem->setSku($sku);
                $sourceItem->setSourceCode($source->getSourceCode());
                $sourceItem->setStatus(1);
                $sourceItem->setQuantity(0);
                try {
                    $this->sourceItemsSaver->execute([$sourceItem]);
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                }
            }
        }
    }
}
