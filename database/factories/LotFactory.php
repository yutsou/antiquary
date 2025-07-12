<?php

namespace Database\Factories;

use App\Models\Lot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lot>
 */
class LotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'estimated_price' => $this->faker->numberBetween(100, 10000),
            'starting_price' => $this->faker->numberBetween(50, 5000),
            'reserve_price' => $this->faker->numberBetween(100, 10000),
            'current_bid' => 0,
            'owner_id' => User::factory(),
            'winner_id' => null,
            'auction_id' => null,
            'auction_start_at' => null,
            'auction_end_at' => null,
            'rating' => 0,
            'status' => 0, // 待審核
            'entrust' => 1,
            'suggestion' => null,
            'inventory' => 1,
        ];
    }

    /**
     * 直賣商品狀態
     */
    public function directSale()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 61, // 上架中
                'type' => 1, // 直賣商品
                'inventory' => $this->faker->numberBetween(1, 100),
            ];
        });
    }

    /**
     * 競標商品狀態
     */
    public function auction()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 21, // 競標成功
                'type' => 0, // 競標商品
                'inventory' => 1,
            ];
        });
    }
}
