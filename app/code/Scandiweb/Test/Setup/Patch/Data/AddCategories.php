<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Add categories data patch
 */
class AddCategories implements DataPatchInterface
{
    /**
     * @var CategoryFactory
     */
    private CategoryFactory $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * AddCategories constructor.
     * 
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Apply the data patch
     *
     * @return void
     */
    public function apply(): void
    {
        // Create Men category if it doesn't exist
        $this->createCategory('Men');
        // Create Women category if it doesn't exist
        $this->createCategory('Women');
    }

    /**
     * Create a category if it doesn't exist
     *
     * @param string $name
     * @return \Magento\Catalog\Model\Category
     */
    private function createCategory(string $name)
    {
        // Check if the category exists
        $category = $this->categoryFactory->create()->getCollection()
            ->addFieldToFilter('name', $name)
            ->getFirstItem();

        if (!$category->getId()) {
            $category = $this->categoryFactory->create();
            $category->setName($name);

            // Fetch "IsActive" from configuration if needed
            $category->setIsActive(
                (bool) $this->scopeConfig->getValue('catalog/category/active', ScopeInterface::SCOPE_STORE)
            );

            // Fetch Parent ID dynamically from configuration or default
            $parentCategoryId = $this->getParentCategoryId();

            $category->setParentId($parentCategoryId);
            $category->setIncludeInMenu(true);
            $this->categoryRepository->save($category);
        }

        return $category;
    }

    /**
     * Get Parent Category ID from configuration or use default if not set
     *
     * @return int
     */
    private function getParentCategoryId(): int
    {
        // Fetch Parent ID from configuration or default to 2 (Root Category)
        $parentCategoryId = $this->scopeConfig->getValue(
            'catalog/category/parent_category_id',
            ScopeInterface::SCOPE_STORE
        );

        // If no Parent Category is set in the configuration, use the default (2)
        return $parentCategoryId ? (int) $parentCategoryId : 2;
    }

    /**
     * Get the dependencies of the patch
     *
     * @return string[] An array of dependencies for the patch.
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases for the patch
     *
     * @return string[] An array of aliases for the patch.
     */
    public function getAliases(): array
    {
        return [];
    }
}
