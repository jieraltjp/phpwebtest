<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . date('Y') . '-' . $this->faker->unique()->numerify('######'),
            'user_id' => User::factory(),
            'customer_name' => $this->faker->name(),
            'customer_email' => $this->faker->safeEmail(),
            'customer_phone' => $this->faker->phoneNumber(),
            'shipping_address' => $this->faker->address(),
            'shipping_city' => $this->faker->city(),
            'shipping_country' => $this->faker->country(),
            'shipping_postal_code' => $this->faker->postcode(),
            'billing_address' => $this->faker->address(),
            'billing_city' => $this->faker->city(),
            'billing_country' => $this->faker->country(),
            'billing_postal_code' => $this->faker->postcode(),
            'subtotal' => $this->faker->randomFloat(2, 50, 2000),
            'tax_amount' => $this->faker->randomFloat(2, 5, 200),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 50),
            'discount_amount' => $this->faker->randomFloat(2, 0, 100),
            'total_amount' => 0, // Will be calculated
            'currency' => $this->faker->randomElement(['CNY', 'JPY', 'USD']),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed', 'refunded']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'bank_transfer', 'paypal', 'alipay', 'wechat_pay']),
            'payment_transaction_id' => $this->faker->uuid(),
            'shipping_method' => $this->faker->randomElement(['standard', 'express', 'overnight']),
            'shipping_carrier' => $this->faker->randomElement(['FedEx', 'UPS', 'DHL', 'Japan Post', 'China Post']),
            'tracking_number' => $this->faker->bothify('????-########'),
            'estimated_delivery_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'actual_delivery_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+2 months'),
            'notes' => $this->faker->optional()->sentence(),
            'customer_notes' => $this->faker->optional()->sentence(),
            'internal_notes' => $this->faker->optional()->sentence(),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'source' => $this->faker->randomElement(['web', 'mobile', 'api', 'phone', 'email']),
            'affiliate_code' => $this->faker->optional()->word(),
            'coupon_code' => $this->faker->optional()->word(),
            'gift_message' => $this->faker->optional()->sentence(),
            'gift_wrap' => $this->faker->boolean(20),
            'insurance' => $this->faker->boolean(30),
            'signature_required' => $this->faker->boolean(40),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Order $order) {
            // Calculate total amount
            $order->total_amount = $order->subtotal + $order->tax_amount + $order->shipping_cost - $order->discount_amount;
        });
    }

    /**
     * Create a pending order.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Create a confirmed order.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);
    }

    /**
     * Create a shipped order.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'payment_status' => 'paid',
            'tracking_number' => $this->faker->bothify('????-########'),
        ]);
    }

    /**
     * Create a delivered order.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
            'actual_delivery_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Create a cancelled order.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'payment_status' => $this->faker->randomElement(['pending', 'refunded']),
        ]);
    }

    /**
     * Create a high-value order.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => $this->faker->randomFloat(2, 1000, 5000),
            'total_amount' => 0, // Will be recalculated
        ]);
    }

    /**
     * Create a low-value order.
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => $this->faker->randomFloat(2, 10, 100),
            'total_amount' => 0, // Will be recalculated
        ]);
    }

    /**
     * Create an urgent order.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
            'shipping_method' => 'overnight',
        ]);
    }

    /**
     * Create an order with specific status.
     */
    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Create an order for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'customer_name' => $user->first_name . ' ' . $user->last_name,
            'customer_email' => $user->email,
        ]);
    }

    /**
     * Create a Japanese order.
     */
    public function japanese(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'JPY',
            'shipping_country' => 'Japan',
            'shipping_city' => $this->faker->randomElement(['Tokyo', 'Osaka', 'Kyoto', 'Yokohama']),
            'shipping_carrier' => $this->faker->randomElement(['Japan Post', 'Yamato Transport', 'Sagawa Express']),
        ]);
    }

    /**
     * Create a Chinese order.
     */
    public function chinese(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'CNY',
            'shipping_country' => 'China',
            'shipping_city' => $this->faker->randomElement(['Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen']),
            'shipping_carrier' => $this->faker->randomElement(['China Post', 'SF Express', 'JD Logistics']),
        ]);
    }

    /**
     * Create an order with discount.
     */
    public function withDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_amount' => $this->faker->randomFloat(2, 10, 200),
            'coupon_code' => $this->faker->word(),
        ]);
    }

    /**
     * Create a gift order.
     */
    public function gift(): static
    {
        return $this->state(fn (array $attributes) => [
            'gift_wrap' => true,
            'gift_message' => $this->faker->sentence(),
        ]);
    }
}