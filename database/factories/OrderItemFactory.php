<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'sku' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                return $product ? $product->sku : 'PROD-' . $this->faker->bothify('???-###');
            },
            'product_name' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                return $product ? $product->name : $this->faker->sentence(3);
            },
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                return $product ? $product->price : $this->faker->randomFloat(2, 10, 500);
            },
            'total' => 0, // Will be calculated
            'currency' => function (array $attributes) {
                $order = Order::find($attributes['order_id']);
                return $order ? $order->currency : 'CNY';
            },
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'dimensions' => json_encode([
                'length' => $this->faker->randomFloat(2, 1, 50),
                'width' => $this->faker->randomFloat(2, 1, 50),
                'height' => $this->faker->randomFloat(2, 1, 50)
            ]),
            'product_options' => json_encode([
                'color' => $this->faker->colorName(),
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool'])
            ]),
            'customization' => $this->faker->optional()->sentence(),
            'gift_wrap' => $this->faker->boolean(20),
            'gift_message' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['ordered', 'processing', 'shipped', 'delivered', 'cancelled']),
            'tracking_number' => $this->faker->optional()->bothify('????-########'),
            'supplier' => $this->faker->company(),
            'cost_price' => $this->faker->randomFloat(2, 5, 200),
            'profit_margin' => $this->faker->randomFloat(2, 10, 50),
            'discount_percentage' => $this->faker->randomFloat(2, 0, 30),
            'discount_amount' => 0, // Will be calculated
            'tax_rate' => $this->faker->randomFloat(2, 0, 25),
            'tax_amount' => 0, // Will be calculated
            'notes' => $this->faker->optional()->sentence(),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (OrderItem $orderItem) {
            // Calculate total amount
            $orderItem->total = $orderItem->quantity * $orderItem->unit_price;
            
            // Calculate discount amount
            $orderItem->discount_amount = $orderItem->total * ($orderItem->discount_percentage / 100);
            
            // Calculate tax amount
            $orderItem->tax_amount = ($orderItem->total - $orderItem->discount_amount) * ($orderItem->tax_rate / 100);
        });
    }

    /**
     * Create an order item for a specific order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'currency' => $order->currency,
        ]);
    }

    /**
     * Create an order item for a specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'product_name' => $product->name,
            'unit_price' => $product->price,
        ]);
    }

    /**
     * Create a high-quantity order item.
     */
    public function highQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(10, 100),
        ]);
    }

    /**
     * Create a low-quantity order item.
     */
    public function lowQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Create a discounted order item.
     */
    public function discounted(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => $this->faker->randomFloat(2, 10, 30),
        ]);
    }

    /**
     * Create a gift-wrapped order item.
     */
    public function giftWrapped(): static
    {
        return $this->state(fn (array $attributes) => [
            'gift_wrap' => true,
            'gift_message' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create an order item with customization.
     */
    public function customized(): static
    {
        return $this->state(fn (array $attributes) => [
            'customization' => $this->faker->sentence(),
            'product_options' => json_encode([
                'color' => $this->faker->colorName(),
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool']),
                'engraving' => $this->faker->word(),
                'monogram' => $this->faker->bothify('??')
            ]),
        ]);
    }

    /**
     * Create a shipped order item.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'tracking_number' => $this->faker->bothify('????-########'),
        ]);
    }

    /**
     * Create a delivered order item.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'tracking_number' => $this->faker->bothify('????-########'),
        ]);
    }

    /**
     * Create a cancelled order item.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Create an electronic product order item.
     */
    public function electronic(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_options' => json_encode([
                'warranty' => $this->faker->randomElement(['1 year', '2 years', '3 years']),
                'color' => $this->faker->randomElement(['Black', 'White', 'Silver', 'Gold']),
                'storage' => $this->faker->randomElement(['64GB', '128GB', '256GB', '512GB']),
                'accessories' => $this->faker->randomElements(['Charger', 'Case', 'Screen protector'], 2)
            ]),
        ]);
    }

    /**
     * Create a clothing order item.
     */
    public function clothing(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_options' => json_encode([
                'size' => $this->faker->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
                'color' => $this->faker->colorName(),
                'material' => $this->faker->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk']),
                'fit' => $this->faker->randomElement(['Slim', 'Regular', 'Relaxed'])
            ]),
        ]);
    }

    /**
     * Create an order item with specific quantity.
     */
    public function quantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }

    /**
     * Create an order item with specific unit price.
     */
    public function unitPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_price' => $price,
        ]);
    }

    /**
     * Create an order item with specific discount.
     */
    public function discount(float $percentage): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => $percentage,
        ]);
    }
}