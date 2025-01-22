<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class AddCategories implements DataPatchInterface
{
    private $categoryFactory;
    private $categoryRepository;

    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
    }

    public function apply()
    {
        // Create Men category if it doesn't exist
        $menCategory = $this->createCategory('Men');
        // Create Women category if it doesn't exist
        $womenCategory = $this->createCategory('Women');
    }

    private function createCategory($name)
    {
        // Check if the category exists
        $category = $this->categoryFactory->create()->getCollection()
            ->addFieldToFilter('name', $name)
            ->getFirstItem();

        if (!$category->getId()) {
            $category = $this->categoryFactory->create();
            $category->setName($name);
            $category->setIsActive(true);
            $category->setParentId(2);
            $category->setIncludeInMenu(true);
            $this->categoryRepository->save($category);
        }

        return $category;
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
