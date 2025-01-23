<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\LetterSize;
use App\Models\Brand;
use App\Models\Style;
use App\Models\NumberSize;
use App\Models\WaistSize;
use App\Models\UserDetailedSize;
use App\Models\UserBrandPreference;
use App\Models\UserProfile;
use App\Services\MediaService;
use App\Services\PicsumService;
use App\Models\Country;
use App\Models\Province;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        $city = fake()->randomElement(['Toronto', 'Ottawa', 'Mississauga', 'Hamilton']);
        
        // Define city coordinates
        $coordinates = match($city) {
            'Toronto' => ['latitude' => 43.6532, 'longitude' => -79.3832],
            'Ottawa' => ['latitude' => 45.4215, 'longitude' => -75.6972],
            'Mississauga' => ['latitude' => 43.5890, 'longitude' => -79.6441],
            'Hamilton' => ['latitude' => 43.2557, 'longitude' => -79.8711],
            default => throw new \Exception('Invalid city')
        };

        // Add small random offset to coordinates (within ~5km)
        $coordinates['latitude'] += fake()->randomFloat(6, -0.05, 0.05);
        $coordinates['longitude'] += fake()->randomFloat(6, -0.05, 0.05);
        
        // Get Canada and Ontario IDs
        $canada = Country::where('abbreviation', 'CA')->first();
        $ontario = Province::where('abbreviation', 'ON')->first();
        
        return [
            'user_id' => User::factory(),
            'username' => $this->faker->unique()->userName(),
            'name' => $this->faker->name(),
            'birthday' => Carbon::now()->subYears(rand(18, 50))->format('Y-m-d'),
            'city' => $city,
            'postal_code' => match($city) {
                'Toronto' => fake()->randomElement(['M4B', 'M5A', 'M5J', 'M5V', 'M6H', 'M6J', 'M6K']),
                'Ottawa' => fake()->randomElement(['K1P', 'K1R', 'K1S', 'K1Y', 'K2P']), 
                'Mississauga' => fake()->randomElement(['L4W', 'L4X', 'L4Y', 'L4Z', 'L5A', 'L5B']),
                'Hamilton' => fake()->randomElement(['L8E', 'L8G', 'L8H', 'L8J', 'L8K', 'L8L', 'L8M', 'L8N', 'L8P', 'L8R', 'L8S', 'L8T', 'L8V', 'L8W']),
                default => throw new \Exception('Invalid city')
            } . ' ' . str_pad(fake()->numberBetween(0, 999), 3, '0', STR_PAD_LEFT),
            'country_id' => $canada->id,
            'province_id' => $ontario->id,
            'language' => 'en',
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'style_id' => fn() => Style::inRandomOrder()->first()->id ?? Style::factory()->create()->id,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (UserProfile $profile) {
            // Add single profile picture
            $picsumService = app(PicsumService::class);
            $mediaService = app(MediaService::class);
            
            if ($image = $picsumService->getRandomImage(400, 400)) {
                // Upload as profile picture
                $mediaService->uploadMedia($profile, $image, [
                    'is_primary' => true,
                    'order' => 0,
                    'metadata' => [
                        'width' => 400,
                        'height' => 400,
                        'source' => 'picsum',
                        'picsum_id' => rand(1, 1000),
                        'type' => 'profile_picture'  // Add this to identify it as profile picture
                    ],
                ]);
                
                // Clean up temp file
                unlink($image->getPathname());
            }

            // Attach random brands (2-4)
            $brands = Brand::inRandomOrder()->limit(rand(2, 4))->pluck('id');
            UserBrandPreference::create([
                'user_id' => $profile->user->id,
                'brand_id' => $brands->first(),
            ]);
            
            // Create detailed sizes (1-3 combinations)
            $sizeCombinations = rand(1, 3);
            for ($i = 0; $i < $sizeCombinations; $i++) {
                UserDetailedSize::create([
                    'user_id' => $profile->user->id,
                    'letter_size_id' => LetterSize::inRandomOrder()->first()->id,
                    'waist_size_id' => WaistSize::inRandomOrder()->first()->id,
                    'number_size_id' => NumberSize::inRandomOrder()->first()->id,
                ]);
            }
        });
    }
} 