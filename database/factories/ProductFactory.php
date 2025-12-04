<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'electronics', 'furniture', 'clothing', 'books', 'toys',
            'sports', 'home', 'garden', 'automotive', 'health'
        ];

        $brands = [
            'Sony', 'Samsung', 'Apple', 'LG', 'Panasonic', 'Toshiba',
            'Nike', 'Adidas', 'Puma', 'Reebok', 'ASUS', 'Dell'
        ];

        return [
            'sku' => 'PROD-' . $this->faker->unique()->bothify('???-###-???'),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'short_description' => $this->faker->sentence(2),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'currency' => $this->faker->randomElement(['CNY', 'JPY', 'USD']),
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'min_stock_level' => $this->faker->numberBetween(5, 50),
            'category' => $this->faker->randomElement($categories),
            'subcategory' => $this->faker->words(2, true),
            'brand' => $this->faker->randomElement($brands),
            'manufacturer' => $this->faker->company(),
            'model' => $this->faker->bothify('Model-###'),
            'weight' => $this->faker->randomFloat(2, 0.1, 50),
            'height' => $this->faker->randomFloat(2, 1, 100),
            'width' => $this->faker->randomFloat(2, 1, 100),
            'length' => $this->faker->randomFloat(2, 1, 100),
            'dimensions' => json_encode([
                'length' => $this->faker->randomFloat(2, 1, 100),
                'width' => $this->faker->randomFloat(2, 1, 100),
                'height' => $this->faker->randomFloat(2, 1, 100)
            ]),
            'color' => $this->faker->colorName(),
            'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'material' => $this->faker->randomElement(['Plastic', 'Metal', 'Wood', 'Glass', 'Fabric']),
            'warranty_period' => $this->faker->numberBetween(6, 36), // months
            'origin_country' => $this->faker->country(),
            'supplier' => $this->faker->company(),
            'cost_price' => $this->faker->randomFloat(2, 5, 500),
            'sale_price' => $this->faker->randomFloat(2, 10, 1000),
            'discount_percentage' => $this->faker->randomFloat(2, 0, 50),
            'tax_rate' => $this->faker->randomFloat(2, 0, 25),
            'shipping_weight' => $this->faker->randomFloat(2, 0.1, 50),
            'shipping_class' => $this->faker->randomElement(['standard', 'express', 'overnight']),
            'tags' => json_encode($this->faker->words(5)),
            'meta_title' => $this->faker->sentence(2),
            'meta_description' => $this->faker->sentence(1),
            'meta_keywords' => implode(', ', $this->faker->words(5)),
            'images' => json_encode([
                $this->faker->imageUrl(400, 400, 'products'),
                $this->faker->imageUrl(400, 400, 'products'),
                $this->faker->imageUrl(400, 400, 'products')
            ]),
            'thumbnail' => $this->faker->imageUrl(200, 200, 'products'),
            'featured' => $this->faker->boolean(20), // 20% chance of being featured
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'is_digital' => $this->faker->boolean(10), // 10% chance of being digital
            'requires_shipping' => $this->faker->boolean(90), // 90% chance of requiring shipping
            'track_quantity' => true,
            'allow_backorder' => $this->faker->boolean(30), // 30% chance of allowing backorder
            'sold_individually' => $this->faker->boolean(10), // 10% chance of being sold individually
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'review_count' => $this->faker->numberBetween(0, 500),
            'view_count' => $this->faker->numberBetween(0, 10000),
            'purchase_count' => $this->faker->numberBetween(0, 1000),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Create an active product.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'stock_quantity' => $this->faker->numberBetween(10, 1000),
        ]);
    }

    /**
     * Create an inactive product.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create an out of stock product.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Create a low stock product.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Create a featured product.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    /**
     * Create an electronics product.
     */
    public function electronics(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'electronics',
            'subcategory' => $this->faker->randomElement(['smartphones', 'laptops', 'tablets', 'accessories']),
            'brand' => $this->faker->randomElement(['Sony', 'Samsung', 'Apple', 'LG', 'Panasonic']),
        ]);
    }

    /**
     * Create a furniture product.
     */
    public function furniture(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'furniture',
            'subcategory' => $this->faker->randomElement(['chairs', 'tables', 'sofas', 'beds']),
            'material' => $this->faker->randomElement(['Wood', 'Metal', 'Leather', 'Fabric']),
        ]);
    }

    /**
     * Create a clothing product.
     */
    public function clothing(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'clothing',
            'subcategory' => $this->faker->randomElement(['shirts', 'pants', 'dresses', 'shoes']),
            'size' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk']),
        ]);
    }

    /**
     * Create a product with specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Create a product with specific brand.
     */
    public function brand(string $brand): static
    {
        return $this->state(fn (array $attributes) => [
            'brand' => $brand,
        ]);
    }

    /**
     * Create a product in specific price range.
     */
    public function priceRange(float $min, float $max): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, $min, $max),
        ]);
    }

    /**
     * Create a product with specific stock quantity.
     */
    public function stock(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $quantity,
        ]);
    }

    /**
     * Create a high-rated product.
     */
    public function highRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->randomFloat(1, 4.0, 5.0),
            'review_count' => $this->faker->numberBetween(50, 500),
        ]);
    }

    /**
     * Create a popular product.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'purchase_count' => $this->faker->numberBetween(100, 1000),
            'view_count' => $this->faker->numberBetween(1000, 10000),
        ]);
    }

    /**
     * Create a discounted product.
     */
    public function discounted(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => $this->faker->randomFloat(2, 10, 50),
        ]);
    }

    /**
     * Create a Japanese market product.
     */
    public function japanese(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'JPY',
            'origin_country' => 'Japan',
            'brand' => $this->faker->randomElement(['Sony', 'Panasonic', 'Toshiba', 'Canon', 'Nintendo']),
        ]);
    }

    /**
     * Create a Chinese market product.
     */
    public function chinese(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'CNY',
            'origin_country' => 'China',
            'brand' => $this->faker->randomElement(['Xiaomi', 'Huawei', 'Lenovo', 'Haier', 'Hisense']),
        ]);
    }
}