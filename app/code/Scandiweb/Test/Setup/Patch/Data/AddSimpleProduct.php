<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\App\State;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Product;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;

class AddSimpleProduct implements DataPatchInterface
{
    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CategoryFactory
     */
    protected CategoryFactory $categoryFactory;

    /**
     * @var State
     */
    protected State $appState;

    /**
     * @var EavSetup
     */
    protected EavSetup $eavSetup;

    /**
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryFactory $categoryFactory
     * @param State $appState
     * @param EavSetup $eavSetup
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryFactory $categoryFactory,
        State $appState,
        EavSetup $eavSetup
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        $this->appState = $appState;
        $this->eavSetup = $eavSetup;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function apply() : void
    {
        // Emulate the 'adminhtml' area and call the execute function
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    public function execute() : void
    {
        $sku = 'test-simple-product';

        try {
            // Check if product exists by SKU
            $existingProduct = $this->productRepository->get($sku);
            // If the product exists, skip creating it
            if ($existingProduct->getId()) {
                return; // Skip product creation
            }
        } catch (NoSuchEntityException $e) {
            // Product does not exist, continue creating it
        }

        // Get the attribute set ID dynamically
        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        // Create the product
        $product = $this->productFactory->create();
        $product->setSku('test-simple-product');
        $product->setName('Test Simple Product');
        $product->setAttributeSetId($attributeSetId); // Default attribute set
        $product->setStatus(Status::STATUS_ENABLED);
        $product->setWeight(1);
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product->setTaxClassId(0); // None
        $product->setTypeId('simple');
        $product->setPrice(20.00); // Set price
        $product->setStockData([
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]);

        // Save the product
        $product = $this->productRepository->save($product);

        // Fetch the 'Men' category by name
        $category = $this->categoryFactory->create()->getCollection()
            ->addFieldToFilter('name', 'Men') // Replace 'Men' with the exact category name if needed
            ->getFirstItem();

        // Check if the category exists
        if ($category->getId()) {
            // Assign the product to the Men category
            $category->setPostedProducts([$product->getId() => ['position' => 0]]);
            $category->save();
        } else {
            // Handle the case if the category is not found
            throw new LocalizedException(__('Men category not found.'));
        }
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies() : array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases() : array
    {
        return [];
    }
}
