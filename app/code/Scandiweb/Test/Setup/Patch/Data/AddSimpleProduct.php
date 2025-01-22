<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\App\State;
use Magento\Catalog\Model\CategoryFactory;

class AddSimpleProduct implements DataPatchInterface
{
    private $productFactory;
    private $productRepository;
    private $categoryFactory;
    private $appState;

    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryFactory $categoryFactory,
        State $appState
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        $this->appState = $appState;

        // Set app area to avoid 'Area code not set' errors
        $this->appState->setAreaCode('adminhtml');
    }

    public function apply()
    {
        // Create the product
        $product = $this->productFactory->create();
        $product->setSku('test-simple-product');
        $product->setName('Test Simple Product');
        $product->setAttributeSetId(4); // Default attribute set
        $product->setStatus(1); // Enabled
        $product->setWeight(1);
        $product->setVisibility(4); // Catalog & Search
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
            throw new \Magento\Framework\Exception\LocalizedException(__('Men category not found.'));
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
