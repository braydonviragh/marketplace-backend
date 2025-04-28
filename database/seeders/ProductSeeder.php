<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\LetterSize;
use App\Models\WaistSize;
use App\Models\NumberSize;
use App\Models\ShoeSize;
use App\Models\Color;
use App\Models\Style;
use App\Services\MediaService;
use App\Services\PicsumService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    protected MediaService $mediaService;
    protected PicsumService $picsumService;

    public function __construct(MediaService $mediaService, PicsumService $picsumService)
    {
        $this->mediaService = $mediaService;
        $this->picsumService = $picsumService;
    }

    public function run(): void
    {
        // Get all regular users (excluding super_admin)
        $users = User::where('role', 'user')->get();
        $brands = Brand::all();
        $categories = Category::all();
        $letterSizes = LetterSize::all();
        $waistSizes = WaistSize::all();
        $numberSizes = NumberSize::all();
        $shoeSizes = ShoeSize::all(); 
        $colors = Color::all();
        $styles = Style::all();

        // Check if we have the necessary data
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        if ($brands->isEmpty()) {
            $this->command->error('No brands found. Please run SizesAndBrandsSeeder first.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->error('No categories found. Please run CategorySeeder first.');
            return;
        }

        if ($letterSizes->isEmpty()) {
            $this->command->error('No letter sizes found. Please run SizesAndBrandsSeeder first.');
            return;
        }

        if ($styles->isEmpty()) {
            $this->command->error('No styles found. Please run StyleSeeder first.');
            return;
        }

        // Define the product attributes
        $adjectives = ['Vintage', 'Designer', 'Classic', 'Luxury', 'Premium', 'Elegant', 'Casual', 'Modern', 'Trendy', 'Chic', 'Contemporary', 'Stylish', 'Bohemian', 'Minimalist', 'Sustainable'];
        $conditions = ['Like New', 'Gently Used', 'Excellent Condition', 'New with Tags', 'Very Good Condition', 'Good Condition', 'Barely Worn'];
        
        // Category-specific item names
        $itemNames = [
            'tops' => ['Blouse', 'T-Shirt', 'Tank Top', 'Crop Top', 'Button-Down Shirt', 'Polo Shirt', 'Halter Top', 'Tunic', 'Camisole', 'Off-Shoulder Top'],
            'sweaters-knits' => ['Cardigan', 'Pullover', 'Turtleneck', 'Cashmere Sweater', 'Knit Top', 'V-Neck Sweater', 'Cable Knit', 'Sweater Vest', 'Crewneck Sweater', 'Oversized Knit'],
            'blazers' => ['Blazer', 'Suit Jacket', 'Sport Coat', 'Tailored Jacket', 'Cropped Blazer', 'Linen Blazer', 'Wool Blazer', 'Tuxedo Jacket', 'Boyfriend Blazer'],
            'bodysuits' => ['Basic Bodysuit', 'Long Sleeve Bodysuit', 'Lace Bodysuit', 'Halter Bodysuit', 'Backless Bodysuit', 'Mesh Bodysuit', 'Scoop Neck Bodysuit', 'Snap-Button Bodysuit'],
            'dresses' => ['Maxi Dress', 'Cocktail Dress', 'Summer Dress', 'Evening Gown', 'Wrap Dress', 'Slip Dress', 'Midi Dress', 'Bodycon Dress', 'A-Line Dress', 'Shift Dress'],
            'jeans' => ['Skinny Jeans', 'Mom Jeans', 'Boyfriend Jeans', 'Straight Leg Jeans', 'Bootcut Jeans', 'Wide Leg Jeans', 'High-Waisted Jeans', 'Distressed Jeans', 'Cropped Jeans', 'Flare Jeans'],
            'pants' => ['Trousers', 'Slacks', 'Palazzo Pants', 'Dress Pants', 'Chinos', 'Cargo Pants', 'Linen Pants', 'Wide Leg Pants', 'Cropped Pants', 'Pleated Pants'],
            'activewear' => ['Leggings', 'Sports Bra', 'Joggers', 'Athletic Shorts', 'Tank Top', 'Yoga Pants', 'Workout Jacket', 'Performance Tights', 'Sweatshirt', 'Tennis Skirt'],
            'skirts' => ['Pencil Skirt', 'A-Line Skirt', 'Midi Skirt', 'Mini Skirt', 'Pleated Skirt', 'Wrap Skirt', 'Denim Skirt', 'Maxi Skirt', 'Leather Skirt', 'High-Waisted Skirt'],
            'jumpsuits' => ['Wide Leg Jumpsuit', 'Utility Jumpsuit', 'Evening Jumpsuit', 'Casual Jumpsuit', 'Strapless Jumpsuit', 'Short Jumpsuit', 'Halter Jumpsuit', 'Denim Jumpsuit'],
            'sweats-hoodies' => ['Sweatshirt', 'Hoodie', 'Sweatpants', 'Zip-Up Hoodie', 'Crewneck Sweatshirt', 'Jogger Set', 'Quarter-Zip Pullover', 'Track Jacket', 'Sweat Shorts'],
            'suits' => ['Pantsuit', 'Skirt Suit', 'Three-Piece Suit', 'Wool Suit', 'Linen Suit', 'Tweed Suit', 'Business Suit', 'Formal Suit', 'Wedding Suit'],
            'shorts' => ['Denim Shorts', 'Linen Shorts', 'Chino Shorts', 'Bermuda Shorts', 'Athletic Shorts', 'High-Waisted Shorts', 'Dress Shorts', 'Cargo Shorts', 'Paper Bag Shorts'],
            'handbags' => ['Tote Bag', 'Crossbody Bag', 'Clutch', 'Shoulder Bag', 'Backpack', 'Satchel', 'Bucket Bag', 'Evening Bag', 'Hobo Bag', 'Wallet', 'Wristlet'],
            'accessories' => ['Scarf', 'Hat', 'Belt', 'Gloves', 'Sunglasses', 'Hair Accessory', 'Wallet', 'Keychain', 'Watch', 'Tie', 'Pocket Square'],
            'jewelry' => ['Necklace', 'Earrings', 'Bracelet', 'Ring', 'Anklet', 'Brooch', 'Pendant', 'Choker', 'Statement Piece', 'Cuff Links', 'Watch'],
            'other' => ['Outerwear', 'Costume', 'Vintage Piece', 'Designer Item', 'Limited Edition', 'Collector Item', 'Fashion Set', 'Couture Piece']
        ];

        $this->command->info('Starting product seeding process...');

        // --------------------- GENERATE PRODUCTS FOR EACH CATEGORY AND SIZE --------------------- //
        
        $totalProducts = 0;
        // Go through each category
        foreach ($categories as $category) {
            $this->command->info("Seeding products for category: {$category->name}");
            
            // Define the size type for this category
            $sizeType = $this->getSizeTypeForCategory($category->slug);
            
            if ($sizeType === 'letter') {
                // Generate 15 products per letter size for this category
                foreach ($letterSizes as $size) {
                    $this->generateProductsForSizeAndCategory($category, LetterSize::class, $size->id, $users, $brands, $colors, $styles, $adjectives, $conditions, $itemNames);
                }
            } 
            else if ($sizeType === 'number') {
                // Generate 15 products per number size for this category
                foreach ($numberSizes as $size) {
                    $this->generateProductsForSizeAndCategory($category, NumberSize::class, $size->id, $users, $brands, $colors, $styles, $adjectives, $conditions, $itemNames);
                }
            } 
            else if ($sizeType === 'waist') {
                // Generate 15 products per waist size for this category
                foreach ($waistSizes as $size) {
                    $this->generateProductsForSizeAndCategory($category, WaistSize::class, $size->id, $users, $brands, $colors, $styles, $adjectives, $conditions, $itemNames);
                }
            }
            else if ($sizeType === 'shoe') {
                // Generate 15 products per shoe size for this category
                foreach ($shoeSizes as $size) {
                    $this->generateProductsForSizeAndCategory($category, ShoeSize::class, $size->id, $users, $brands, $colors, $styles, $adjectives, $conditions, $itemNames);
                }
            }
            else { 
                // For categories with no size (accessories, etc.), just generate 15 products
                for ($i = 0; $i < 15; $i++) {
                    $this->createProduct(
                        $users->random(),
                        $category,
                        null,
                        null,
                        $brands->random(),
                        $colors->random(),
                        $styles->random(),
                        $adjectives,
                        $conditions,
                        $itemNames
                    );
                    $totalProducts++;
                }
            }
        }

        $this->command->info("Created {$totalProducts} products in total");

        // Add sample images for each product
        $this->command->info('Adding images to products...');
        
        foreach (Product::all() as $product) {
            // Add 1-3 images for each product
            $imageCount = rand(1, 3);
            
            for ($i = 0; $i < $imageCount; $i++) {
                if ($image = $this->picsumService->getRandomImage()) {
                    $this->mediaService->uploadMedia($product, $image, [
                        'is_primary' => $i === 0,
                        'order' => $i,
                        'metadata' => [
                            'width' => 600,
                            'height' => 800,
                            'source' => 'picsum',
                            'picsum_id' => rand(1, 1000),
                        ],
                    ]);
                    
                    // Clean up temp file
                    unlink($image->getPathname());
                }
            }
        }
        
        $this->command->info('Product seeding completed successfully!');
    }

    /**
     * Generate 15 products for a specific category and size combination
     */
    private function generateProductsForSizeAndCategory($category, $sizeableType, $sizeableId, $users, $brands, $colors, $styles, $adjectives, $conditions, $itemNames)
    {
        for ($i = 0; $i < 15; $i++) {
            $this->createProduct(
                $users->random(),
                $category,
                $sizeableType,
                $sizeableId,
                $brands->random(),
                $colors->random(),
                $styles->random(),
                $adjectives,
                $conditions,
                $itemNames
            );
        }
    }

    /**
     * Create a single product with the given attributes
     */
    private function createProduct($user, $category, $sizeableType, $sizeableId, $brand, $color, $style, $adjectives, $conditions, $itemNames)
    {
        // Generate a price that's a multiple of 25 with .00 cents
        $price = ceil(fake()->numberBetween(10, 1000) / 25) * 25;

        // Get appropriate item name for the category
        $itemName = isset($itemNames[$category->slug]) 
            ? fake()->randomElement($itemNames[$category->slug])
            : $category->name;
        
        $title = fake()->randomElement($adjectives) . ' ' . 
                 $brand->name . ' ' .
                 $itemName . ' - ' .
                 fake()->randomElement($conditions);

        $product = Product::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'style_id' => $style->id,
            'brand_id' => $brand->id,
            'title' => $title,
            'description' => fake()->paragraphs(3, true),
            'sizeable_type' => $sizeableType,
            'sizeable_id' => $sizeableId,
            'color_id' => $color->id,
            'price' => $price,
            'is_available' => true,
        ]);

        return $product;
    }

    /**
     * Determine which size type to use based on category
     */
    private function getSizeTypeForCategory($categorySlug) 
    {
        // Categories using letter sizes (XS, S, M, L, XL, XXL)
        $letterSizeCategories = [
            'tops', 'sweaters-knits', 'blazers', 'bodysuits', 'sweats-hoodies'
        ];
        
        // Categories using number sizes (0, 2, 4, 6, etc.)
        $numberSizeCategories = [
            'dresses', 'activewear', 'skirts', 'jumpsuits', 'suits'
        ];
        
        // Categories using waist sizes (24, 26, 28, etc.)
        $waistSizeCategories = [
            'jeans', 'pants', 'shorts'
        ];
        
        // Categories using shoe sizes
        $shoeSizeCategories = [
            'shoes'
        ];
        
        // Categories with no size
        $noSizeCategories = [
            'accessories', 'handbags', 'jewelry', 'other'
        ];
        
        if (in_array($categorySlug, $letterSizeCategories)) {
            return 'letter';
        } elseif (in_array($categorySlug, $numberSizeCategories)) {
            return 'number';
        } elseif (in_array($categorySlug, $waistSizeCategories)) {
            return 'waist';
        } elseif (in_array($categorySlug, $shoeSizeCategories)) {
            return 'shoe';
        } else {
            return 'none';
        }
    }
} 