<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inquiry_number' => 'INQ-' . date('Y') . '-' . $this->faker->unique()->numerify('######'),
            'user_id' => User::factory(),
            'contact_name' => $this->faker->name(),
            'contact_email' => $this->faker->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_fax' => $this->faker->optional()->phoneNumber(),
            'company_name' => $this->faker->company(),
            'company_website' => $this->faker->optional()->url(),
            'industry' => $this->faker->randomElement(['retail', 'manufacturing', 'technology', 'healthcare', 'education', 'government']),
            'company_size' => $this->faker->randomElement(['small', 'medium', 'large', 'enterprise']),
            'annual_revenue' => $this->faker->randomElement(['<1M', '1M-10M', '10M-50M', '50M-100M', '>100M']),
            'product_skus' => json_encode($this->faker->randomElements(['PROD-001', 'PROD-002', 'PROD-003', 'PROD-004', 'PROD-005'], $this->faker->numberBetween(1, 5))),
            'quantity' => $this->faker->numberBetween(10, 10000),
            'target_price' => $this->faker->randomFloat(2, 10, 500),
            'currency' => $this->faker->randomElement(['CNY', 'JPY', 'USD']),
            'budget_range' => $this->faker->randomElement(['<10K', '10K-50K', '50K-100K', '100K-500K', '>500K']),
            'expected_delivery_date' => $this->faker->dateTimeBetween('+1 week', '+6 months'),
            'shipping_destination' => $this->faker->country(),
            'shipping_address' => $this->faker->address(),
            'incoterm' => $this->faker->randomElement(['FOB', 'CIF', 'EXW', 'DDP', 'FCA']),
            'payment_terms' => $this->faker->randomElement(['T/T', 'L/C', 'D/P', 'D/A', 'OA']),
            'message' => $this->faker->paragraph(3),
            'requirements' => $this->faker->optional()->paragraph(2),
            'specifications' => json_encode([
                'quality_standard' => $this->faker->randomElement(['ISO 9001', 'CE', 'FCC', 'RoHS']),
                'certification_required' => $this->faker->boolean(70),
                'customization_needed' => $this->faker->boolean(50),
                'packaging_requirements' => $this->faker->randomElement(['standard', 'custom', 'bulk', 'retail'])
            ]),
            'application' => $this->faker->randomElement(['resale', 'manufacturing', 'distribution', 'OEM', 'government']),
            'competition' => $this->faker->optional()->sentence(),
            'timeline' => $this->faker->randomElement(['urgent', 'normal', 'flexible']),
            'decision_timeline' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'status' => $this->faker->randomElement(['pending', 'quoted', 'accepted', 'rejected', 'expired', 'cancelled']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'source' => $this->faker->randomElement(['website', 'email', 'phone', 'trade_show', 'referral', 'social_media']),
            'assigned_to' => $this->faker->optional()->name(),
            'quoted_price' => $this->faker->optional()->randomFloat(2, 10, 500),
            'quoted_currency' => function (array $attributes) {
                return $attributes['currency'];
            },
            'quote_valid_until' => $this->faker->optional()->dateTimeBetween('+1 week', '+1 month'),
            'discount_offered' => $this->faker->optional()->randomFloat(2, 0, 20),
            'notes' => $this->faker->optional()->sentence(),
            'internal_notes' => $this->faker->optional()->paragraph(2),
            'follow_up_date' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'next_action' => $this->faker->optional()->randomElement(['send_quote', 'follow_up', 'schedule_call', 'send_samples']),
            'probability' => $this->faker->optional()->randomElement(['low', 'medium', 'high']),
            'estimated_value' => function (array $attributes) {
                return $attributes['quantity'] * $attributes['target_price'];
            },
            'competitor_info' => $this->faker->optional()->sentence(),
            'decision_criteria' => json_encode([
                'price' => $this->faker->numberBetween(1, 5),
                'quality' => $this->faker->numberBetween(1, 5),
                'delivery' => $this->faker->numberBetween(1, 5),
                'service' => $this->faker->numberBetween(1, 5)
            ]),
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a pending inquiry.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Create a quoted inquiry.
     */
    public function quoted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'quoted',
            'quoted_price' => $this->faker->randomFloat(2, $attributes['target_price'] * 0.8, $attributes['target_price'] * 1.2),
            'quote_valid_until' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'discount_offered' => $this->faker->randomFloat(2, 0, 15),
        ]);
    }

    /**
     * Create an accepted inquiry.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'quoted_price' => $this->faker->randomFloat(2, $attributes['target_price'] * 0.9, $attributes['target_price']),
        ]);
    }

    /**
     * Create a rejected inquiry.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'notes' => 'Customer found better price elsewhere',
        ]);
    }

    /**
     * Create an expired inquiry.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'quoted_price' => $this->faker->randomFloat(2, $attributes['target_price'] * 0.8, $attributes['target_price'] * 1.2),
            'quote_valid_until' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Create a high-priority inquiry.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'timeline' => 'urgent',
        ]);
    }

    /**
     * Create a high-value inquiry.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(1000, 50000),
            'target_price' => $this->faker->randomFloat(2, 50, 200),
            'budget_range' => $this->faker->randomElement(['100K-500K', '>500K']),
        ]);
    }

    /**
     * Create a low-value inquiry.
     */
    public function lowValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(10, 100),
            'target_price' => $this->faker->randomFloat(2, 10, 50),
            'budget_range' => '<10K',
        ]);
    }

    /**
     * Create an inquiry for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'contact_name' => $user->first_name . ' ' . $user->last_name,
            'contact_email' => $user->email,
        ]);
    }

    /**
     * Create a Japanese inquiry.
     */
    public function japanese(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'JPY',
            'shipping_destination' => 'Japan',
            'company_size' => $this->faker->randomElement(['small', 'medium', 'large']),
            'incoterm' => $this->faker->randomElement(['FOB', 'CIF']),
            'payment_terms' => $this->faker->randomElement(['T/T', 'L/C']),
        ]);
    }

    /**
     * Create a Chinese inquiry.
     */
    public function chinese(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'CNY',
            'shipping_destination' => 'China',
            'company_size' => $this->faker->randomElement(['medium', 'large', 'enterprise']),
            'incoterm' => $this->faker->randomElement(['FOB', 'EXW', 'DDP']),
            'payment_terms' => $this->faker->randomElement(['T/T', 'L/C', 'D/P']),
        ]);
    }

    /**
     * Create an inquiry with specific status.
     */
    public function status(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Create an inquiry with specific priority.
     */
    public function priority(string $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority,
        ]);
    }

    /**
     * Create an inquiry from a specific source.
     */
    public function source(string $source): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => $source,
        ]);
    }

    /**
     * Create an inquiry for specific products.
     */
    public function forProducts(array $skus): static
    {
        return $this->state(fn (array $attributes) => [
            'product_skus' => json_encode($skus),
        ]);
    }

    /**
     * Create an inquiry with specific quantity.
     */
    public function quantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }

    /**
     * Create an inquiry with specific target price.
     */
    public function targetPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'target_price' => $price,
        ]);
    }

    /**
     * Create an OEM inquiry.
     */
    public function oem(): static
    {
        return $this->state(fn (array $attributes) => [
            'application' => 'OEM',
            'requirements' => $this->faker->sentence() . ' Custom branding and specifications required.',
            'specifications' => json_encode([
                'quality_standard' => 'ISO 9001',
                'certification_required' => true,
                'customization_needed' => true,
                'packaging_requirements' => 'custom'
            ]),
        ]);
    }

    /**
     * Create a government inquiry.
     */
    public function government(): static
    {
        return $this->state(fn (array $attributes) => [
            'industry' => 'government',
            'application' => 'government',
            'requirements' => $this->faker->sentence() . ' Must meet government procurement standards.',
            'specifications' => json_encode([
                'quality_standard' => 'ISO 9001',
                'certification_required' => true,
                'customization_needed' => false,
                'packaging_requirements' => 'standard'
            ]),
        ]);
    }
}