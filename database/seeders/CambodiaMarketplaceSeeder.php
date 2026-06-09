<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\ProductImage;
use App\Models\Store;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CambodiaMarketplaceSeeder extends Seeder
{
    // ─── helpers ──────────────────────────────────────────────────────────────

    private function img(string $seed): string
    {
        return "https://picsum.photos/seed/{$seed}/600/600";
    }

    private function usd(float $dollars): int
    {
        return (int) round($dollars * 100);
    }

    private function makeUser(string $name, string $email, string $role = 'user', string $username = null): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make('password'),
                'role'              => $role,
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $isSeller = in_array($role, ['seller', 'admin']);
        UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'username'           => $username ?? Str::slug($name),
                'is_seller'          => $isSeller,
                'profile_visibility' => 'public',
                'country_code'       => 'KH',
            ]
        );

        return $user;
    }

    private function makeStore(User $seller, array $attrs): Store
    {
        $store = Store::firstOrCreate(
            ['slug' => $attrs['slug']],
            array_merge([
                'user_id'       => $seller->id,
                'country_code'  => 'KH',
                'status'        => 'active',
                'is_verified'   => true,
                'published_at'  => now(),
            ], $attrs)
        );

        $seller->profile()->update(['default_store_id' => $store->id]);

        return $store;
    }

    private function makeProduct(Store $store, Category $cat, ProductCondition $cond, Brand $brand = null, array $attrs = []): Product
    {
        $title = $attrs['title'];
        $slug  = Str::slug($title).'-'.substr(md5($title.$store->id), 0, 6);

        $priceAmount = $attrs['price_amount'] ?? 0;

        $product = Product::firstOrCreate(
            ['slug' => $slug],
            array_merge([
                'user_id'              => $store->user_id,
                'store_id'             => $store->id,
                'category_id'          => $cat->id,
                'product_condition_id' => $cond->id,
                'brand_id'             => $brand?->id,
                'slug'                 => $slug,
                'sku'                  => 'KH-'.strtoupper(Str::random(6)),
                'price'                => $priceAmount,
                'currency'             => 'USD',
                'stock_quantity'       => rand(1, 5),
                'status'               => 'published',
                'moderation_status'    => 'approved',
                'visibility'           => 'public',
                'allow_offers'         => true,
                'location_country_code' => 'KH',
                'published_at'         => now()->subDays(rand(0, 30)),
            ], $attrs)
        );

        return $product;
    }

    private function addImage(Product $product, string $seed, bool $primary = false): void
    {
        $url = $this->img($seed);
        ProductImage::firstOrCreate(
            ['product_id' => $product->id, 'path' => $url],
            [
                'disk'       => 'external',
                'is_primary' => $primary,
                'sort_order' => $primary ? 0 : 1,
                'mime_type'  => 'image/jpeg',
            ]
        );
    }

    // ─── run ──────────────────────────────────────────────────────────────────

    public function run(): void
    {
        // ── Conditions & Categories (already seeded, just fetch) ──────────────
        $cNew      = ProductCondition::where('slug', 'new')->first();
        $cLikeNew  = ProductCondition::where('slug', 'like-new')->first();
        $cGood     = ProductCondition::where('slug', 'good')->first();
        $cFair     = ProductCondition::where('slug', 'fair')->first();

        $catElec   = Category::where('slug', 'electronics')->first();
        $catMoto   = Category::where('slug', 'motorcycles-vehicles')->first();
        $catFash   = Category::where('slug', 'fashion-clothing')->first();
        $catHome   = Category::where('slug', 'home-living')->first();
        $catBooks  = Category::where('slug', 'books-education')->first();
        $catSports = Category::where('slug', 'sports-fitness')->first();
        $catCrafts = Category::where('slug', 'traditional-crafts')->first();

        // ── Brands ─────────────────────────────────────────────────────────────
        $brands = [];
        $brandData = [
            ['Samsung', 'samsung'], ['Apple', 'apple'], ['Honda', 'honda'],
            ['Yamaha', 'yamaha'], ['Toyota', 'toyota'], ['Huawei', 'huawei'],
            ['Oppo', 'oppo'], ['Xiaomi', 'xiaomi'], ['Panasonic', 'panasonic'],
            ['Giant', 'giant'], ['Adidas', 'adidas'], ['Nike', 'nike'],
            ['Hitachi', 'hitachi'], ['TCL', 'tcl'], ['JBL', 'jbl'],
        ];

        foreach ($brandData as [$name, $slug]) {
            $brands[$slug] = Brand::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'status' => 'active', 'sort_order' => 0]
            );
        }

        // ── Admin ──────────────────────────────────────────────────────────────
        $this->makeUser('Admin ReMarket', 'admin@remarket.kh', 'admin', 'admin-remarket');

        // ─────────────────────────────────────────────────────────────────────
        // SELLER 1 — Sopheap Chan · Phnom Penh Tech Hub · Electronics
        // ─────────────────────────────────────────────────────────────────────
        $sopheap = $this->makeUser('Sopheap Chan', 'sopheap@example.com', 'seller', 'sopheap-chan');
        $techHub = $this->makeStore($sopheap, [
            'name'        => 'Phnom Penh Tech Hub',
            'slug'        => 'phnom-penh-tech-hub',
            'description' => 'Quality second-hand electronics in Phnom Penh. All items tested before listing.',
            'city'        => 'Phnom Penh',
            'state'       => 'Phnom Penh',
        ]);

        $p = $this->makeProduct($techHub, $catElec, $cLikeNew, $brands['apple'], [
            'title'        => 'iPhone 13 128GB Space Grey',
            'description'  => 'Used iPhone 13 in excellent condition. Battery health 91%. Comes with original box and charger. No scratches, screen protector applied from day one. Ready for use with any carrier.',
            'price_amount' => $this->usd(380),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Storage' => '128GB', 'Color' => 'Space Grey', 'Battery Health' => '91%', 'Carrier' => 'Unlocked'],
        ]);
        $this->addImage($p, 'iphone-13-kh', true);
        $this->addImage($p, 'iphone-13-back', false);

        $p = $this->makeProduct($techHub, $catElec, $cGood, $brands['samsung'], [
            'title'        => 'Samsung Galaxy A54 5G 128GB',
            'description'  => 'Samsung Galaxy A54 with 5G support. 50MP camera, 5000mAh battery. Minor scratches on back, screen is perfect. Comes with charger.',
            'price_amount' => $this->usd(165),
            'location_city' => 'Phnom Penh',
            'specs'        => ['RAM' => '8GB', 'Storage' => '128GB', 'Camera' => '50MP', 'Battery' => '5000mAh'],
        ]);
        $this->addImage($p, 'samsung-a54-kh', true);

        $p = $this->makeProduct($techHub, $catElec, $cGood, $brands['xiaomi'], [
            'title'        => 'Xiaomi Redmi Note 12 Pro 256GB',
            'description'  => 'Redmi Note 12 Pro with 200MP camera. Fully functional, light use. Screen has no issues. Charger included.',
            'price_amount' => $this->usd(120),
            'location_city' => 'Phnom Penh',
            'specs'        => ['RAM' => '8GB', 'Storage' => '256GB', 'Camera' => '200MP', 'Battery' => '5000mAh'],
        ]);
        $this->addImage($p, 'xiaomi-note12-kh', true);

        $p = $this->makeProduct($techHub, $catElec, $cGood, null, [
            'title'        => 'Dell Inspiron 15 Laptop Intel i5',
            'description'  => 'Dell Inspiron 15 with Intel i5 11th Gen, 8GB RAM, 512GB SSD. Good condition for school or office work. Battery lasts ~4 hours.',
            'price_amount' => $this->usd(250),
            'location_city' => 'Phnom Penh',
            'specs'        => ['CPU' => 'Intel i5 11th Gen', 'RAM' => '8GB', 'Storage' => '512GB SSD', 'OS' => 'Windows 11'],
        ]);
        $this->addImage($p, 'dell-laptop-kh', true);
        $this->addImage($p, 'dell-laptop-open', false);

        $p = $this->makeProduct($techHub, $catElec, $cLikeNew, $brands['tcl'], [
            'title'        => 'TCL Smart TV 43" 4K Android',
            'description'  => '43-inch 4K Smart TV with Android. Netflix, YouTube, and streaming apps built in. Remote and original stand included. Moving sale.',
            'price_amount' => $this->usd(195),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Screen' => '43 inch', 'Resolution' => '4K UHD', 'OS' => 'Android TV', 'HDR' => 'Yes'],
        ]);
        $this->addImage($p, 'tcl-tv-kh', true);

        $p = $this->makeProduct($techHub, $catElec, $cLikeNew, $brands['jbl'], [
            'title'        => 'JBL Party Box On-The-Go Speaker',
            'description'  => 'Portable JBL party speaker with built-in lights. Used at 3 events only. All functions work perfectly. Original bag included.',
            'price_amount' => $this->usd(85),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Battery Life' => '6 hours', 'Bluetooth' => '5.1', 'IPX4' => 'Water resistant'],
        ]);
        $this->addImage($p, 'jbl-speaker-kh', true);

        $p = $this->makeProduct($techHub, $catElec, $cNew, null, [
            'title'        => 'Anker PowerCore 20000mAh Power Bank',
            'description'  => 'Brand new Anker power bank. Dual USB + USB-C. Never opened, bought as gift but already have one. Perfect for travel.',
            'price_amount' => $this->usd(28),
            'stock_quantity' => 2,
            'location_city' => 'Phnom Penh',
            'specs'        => ['Capacity' => '20000mAh', 'Output' => 'USB-C + 2x USB-A', 'Fast Charge' => 'Yes'],
        ]);
        $this->addImage($p, 'anker-powerbank-kh', true);

        // ─────────────────────────────────────────────────────────────────────
        // SELLER 2 — Dara Keo · Angkor Fashion · Clothing & Traditional
        // ─────────────────────────────────────────────────────────────────────
        $dara = $this->makeUser('Dara Keo', 'dara@example.com', 'seller', 'dara-keo');
        $angkorFashion = $this->makeStore($dara, [
            'name'        => 'Angkor Fashion',
            'slug'        => 'angkor-fashion',
            'description' => 'Authentic Khmer traditional wear and modern fashion from Siem Reap. Wholesale available.',
            'city'        => 'Siem Reap',
            'state'       => 'Siem Reap',
        ]);

        $p = $this->makeProduct($angkorFashion, $catCrafts, $cNew, null, [
            'title'        => 'Khmer Traditional Sampot Silk Skirt',
            'description'  => 'Authentic Khmer silk Sampot hand-woven in Siem Reap. 100% pure silk, intricate floral pattern in gold and red. Perfect for formal events, weddings and Khmer New Year. Size: Free size.',
            'price_amount' => $this->usd(45),
            'stock_quantity' => 3,
            'location_city' => 'Siem Reap',
            'specs'        => ['Material' => '100% Pure Silk', 'Color' => 'Gold & Red', 'Size' => 'Free size', 'Origin' => 'Siem Reap, Cambodia'],
        ]);
        $this->addImage($p, 'khmer-sampot-silk', true);

        $p = $this->makeProduct($angkorFashion, $catCrafts, $cNew, null, [
            'title'        => 'Krama Traditional Cambodian Scarf',
            'description'  => 'Traditional Cambodian Krama scarf in checkered pattern. Cotton, hand-woven. Multi-purpose: head wrap, sarong, sling. Available in red/white and blue/white.',
            'price_amount' => $this->usd(8),
            'stock_quantity' => 10,
            'location_city' => 'Siem Reap',
            'specs'        => ['Material' => '100% Cotton', 'Pattern' => 'Checkered', 'Length' => '150cm', 'Width' => '80cm'],
        ]);
        $this->addImage($p, 'krama-scarf-kh', true);

        $p = $this->makeProduct($angkorFashion, $catFash, $cNew, null, [
            'title'        => "Men's Batik Linen Shirt Short Sleeve",
            'description'  => 'Lightweight batik-print linen shirt. Perfect for hot Cambodia weather. Available in sizes M, L, XL, XXL. Machine washable.',
            'price_amount' => $this->usd(18),
            'stock_quantity' => 5,
            'location_city' => 'Siem Reap',
            'specs'        => ['Material' => 'Linen blend', 'Sizes' => 'M / L / XL / XXL', 'Sleeve' => 'Short'],
        ]);
        $this->addImage($p, 'batik-shirt-kh', true);

        $p = $this->makeProduct($angkorFashion, $catFash, $cNew, null, [
            'title'        => "Girl's School Uniform Set (Blouse + Skirt)",
            'description'  => 'Standard Cambodian primary school uniform. White blouse and navy skirt. New with tags. Available in sizes 6-14 years.',
            'price_amount' => $this->usd(12),
            'stock_quantity' => 8,
            'location_city' => 'Siem Reap',
            'specs'        => ['Gender' => 'Girl', 'Includes' => 'Blouse + Skirt', 'Sizes' => 'Ages 6-14'],
        ]);
        $this->addImage($p, 'school-uniform-kh', true);

        $p = $this->makeProduct($angkorFashion, $catFash, $cLikeNew, $brands['adidas'], [
            'title'        => 'Adidas Ultraboost 22 Running Shoes Size 42',
            'description'  => 'Adidas Ultraboost 22 worn only 3 times. Original box included. EU size 42 (fits 26cm foot). No visible wear on sole.',
            'price_amount' => $this->usd(55),
            'location_city' => 'Siem Reap',
            'specs'        => ['Brand' => 'Adidas', 'Model' => 'Ultraboost 22', 'Size' => 'EU 42', 'Color' => 'Core Black'],
        ]);
        $this->addImage($p, 'adidas-ultraboost-kh', true);

        $p = $this->makeProduct($angkorFashion, $catCrafts, $cNew, null, [
            'title'        => 'Khmer Traditional Tep Kraom Dress',
            'description'  => 'Beautiful traditional Khmer dress (Tep Kraom) with golden embroidery. Suitable for Bon Om Touk, Khmer New Year, and formal ceremonies. Handcrafted in Phnom Penh.',
            'price_amount' => $this->usd(55),
            'stock_quantity' => 2,
            'location_city' => 'Siem Reap',
            'specs'        => ['Material' => 'Brocade silk', 'Embroidery' => 'Gold thread', 'Style' => 'Traditional formal'],
        ]);
        $this->addImage($p, 'khmer-dress-tep', true);

        // ─────────────────────────────────────────────────────────────────────
        // SELLER 3 — Sreymom Pich · Mekong Home & Living
        // ─────────────────────────────────────────────────────────────────────
        $sreymom = $this->makeUser('Sreymom Pich', 'sreymom@example.com', 'seller', 'sreymom-pich');
        $mekongHome = $this->makeStore($sreymom, [
            'name'        => 'Mekong Home & Living',
            'slug'        => 'mekong-home-living',
            'description' => 'Affordable household items and appliances in Phnom Penh. Message the seller for pickup details.',
            'city'        => 'Phnom Penh',
            'state'       => 'Phnom Penh',
        ]);

        $p = $this->makeProduct($mekongHome, $catHome, $cGood, $brands['hitachi'], [
            'title'        => 'Hitachi Rice Cooker 1.8L RZ-18VFY',
            'description'  => 'Hitachi 1.8L rice cooker, can make up to 10 cups. Good working condition. Auto keep-warm function. Cleaned and tested. Good for family of 4-6.',
            'price_amount' => $this->usd(38),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Capacity' => '1.8 Litres (10 cups)', 'Brand' => 'Hitachi', 'Voltage' => '220V', 'Keep Warm' => 'Auto'],
        ]);
        $this->addImage($p, 'hitachi-rice-cooker', true);

        $p = $this->makeProduct($mekongHome, $catHome, $cGood, $brands['panasonic'], [
            'title'        => 'Panasonic Stand Fan 16" with Remote',
            'description'  => 'Panasonic 16-inch stand fan with remote control. 3 speed settings, timer function. Minor scratch on base, all functions work perfectly.',
            'price_amount' => $this->usd(22),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Size' => '16 inch', 'Speeds' => '3', 'Remote' => 'Included', 'Timer' => 'Yes'],
        ]);
        $this->addImage($p, 'panasonic-fan-kh', true);

        $p = $this->makeProduct($mekongHome, $catHome, $cGood, $brands['samsung'], [
            'title'        => 'Samsung Two-Door Refrigerator 320L',
            'description'  => 'Samsung 320L two-door refrigerator. Frost-free, good condition. Moving out of Phnom Penh so must sell quickly. Available for pickup in BKK1.',
            'price_amount' => $this->usd(145),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Capacity' => '320L', 'Doors' => '2', 'Frost Free' => 'Yes', 'Inverter' => 'Yes'],
        ]);
        $this->addImage($p, 'samsung-fridge-kh', true);

        $p = $this->makeProduct($mekongHome, $catHome, $cFair, null, [
            'title'        => 'LG Front-Load Washing Machine 8kg',
            'description'  => 'LG 8kg front-load washing machine. A few scratches on the front panel but works 100%. Good for expats or families. Must collect from Toul Kork.',
            'price_amount' => $this->usd(115),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Capacity' => '8kg', 'Type' => 'Front load', 'RPM' => '1200', 'Programs' => '12'],
        ]);
        $this->addImage($p, 'lg-washer-kh', true);

        $p = $this->makeProduct($mekongHome, $catHome, $cGood, null, [
            'title'        => 'Bamboo Sofa Set 3-Piece Living Room',
            'description'  => 'Handcrafted bamboo sofa set — 1 two-seater + 2 single chairs with cushions. Natural finish, lightly used. Pickup only in Sen Sok.',
            'price_amount' => $this->usd(95),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Material' => 'Natural bamboo', 'Pieces' => '3 (sofa + 2 chairs)', 'Cushions' => 'Included'],
        ]);
        $this->addImage($p, 'bamboo-sofa-kh', true);

        $p = $this->makeProduct($mekongHome, $catCrafts, $cNew, null, [
            'title'        => 'Khmer Ceramic Dinner Set 12 Pieces',
            'description'  => 'Handpainted Khmer ceramic dinner set. 4 plates, 4 bowls, 4 cups. Blue lotus pattern inspired by Angkor motifs. Great housewarming gift.',
            'price_amount' => $this->usd(28),
            'stock_quantity' => 3,
            'location_city' => 'Phnom Penh',
            'specs'        => ['Pieces' => '12', 'Pattern' => 'Lotus / Angkor motif', 'Material' => 'Ceramic', 'Dishwasher Safe' => 'Yes'],
        ]);
        $this->addImage($p, 'khmer-ceramic-set', true);

        // ─────────────────────────────────────────────────────────────────────
        // SELLER 4 — Vibol Noun · Bayon Motors · Motorcycles & Transport
        // ─────────────────────────────────────────────────────────────────────
        $vibol = $this->makeUser('Vibol Noun', 'vibol@example.com', 'seller', 'vibol-noun');
        $bayonMotors = $this->makeStore($vibol, [
            'name'        => 'Bayon Motors',
            'slug'        => 'bayon-motors',
            'description' => 'Trusted motorcycle and vehicle dealer in Battambang. All bikes inspected and serviced before listing.',
            'city'        => 'Battambang',
            'state'       => 'Battambang',
        ]);

        $p = $this->makeProduct($bayonMotors, $catMoto, $cLikeNew, $brands['honda'], [
            'title'        => 'Honda Wave 110i 2022 Blue',
            'description'  => 'Honda Wave 110i, year 2022. Only 8,000 km on the clock. First owner, serviced every 2,000km. Docs complete. Available in Battambang.',
            'price_amount' => $this->usd(680),
            'location_city' => 'Battambang',
            'specs'        => ['Year' => '2022', 'Engine' => '110cc', 'KM' => '8,000 km', 'Color' => 'Blue', 'Docs' => 'Complete'],
        ]);
        $this->addImage($p, 'honda-wave-kh', true);
        $this->addImage($p, 'honda-wave-side', false);

        $p = $this->makeProduct($bayonMotors, $catMoto, $cLikeNew, $brands['honda'], [
            'title'        => 'Honda Click 125i 2022 Pearl White',
            'description'  => 'Honda Click 125i scooter. 2022 model, 12,000km. Automatic transmission. Honda Smart Key, LED headlights, ABS. All docs present.',
            'price_amount' => $this->usd(980),
            'location_city' => 'Battambang',
            'specs'        => ['Year' => '2022', 'Engine' => '125cc', 'KM' => '12,000 km', 'Transmission' => 'Automatic', 'ABS' => 'Yes'],
        ]);
        $this->addImage($p, 'honda-click-kh', true);

        $p = $this->makeProduct($bayonMotors, $catMoto, $cGood, $brands['yamaha'], [
            'title'        => 'Yamaha Exciter 150 2020 Sport Red',
            'description'  => 'Yamaha Exciter 150 in sport red. 2020 model, 25,000km. Engine runs great. New rear tyre fitted. Sporty and fast, perfect for city riding.',
            'price_amount' => $this->usd(1150),
            'location_city' => 'Battambang',
            'specs'        => ['Year' => '2020', 'Engine' => '150cc', 'KM' => '25,000 km', 'Color' => 'Sport Red', 'Tyres' => 'New rear'],
        ]);
        $this->addImage($p, 'yamaha-exciter-kh', true);

        $p = $this->makeProduct($bayonMotors, $catMoto, $cGood, null, [
            'title'        => 'Electric Scooter 72V 45AH Long Range',
            'description'  => 'Chinese electric scooter, 72V motor. Range ~80km per charge. No petrol cost. Good for city commute in Phnom Penh or Battambang. Charger included.',
            'price_amount' => $this->usd(420),
            'location_city' => 'Battambang',
            'specs'        => ['Motor' => '72V', 'Battery' => '45AH Lithium', 'Range' => '~80 km', 'Top Speed' => '55 km/h', 'Charge Time' => '4-6 hours'],
        ]);
        $this->addImage($p, 'electric-scooter-kh', true);

        $p = $this->makeProduct($bayonMotors, $catMoto, $cNew, null, [
            'title'        => 'Nolan N60-6 Motorcycle Helmet Full Face',
            'description'  => 'Brand new Nolan full-face helmet. DOT certified. Size L (59-60cm). Good ventilation for Cambodian heat. Black finish.',
            'price_amount' => $this->usd(48),
            'stock_quantity' => 3,
            'location_city' => 'Battambang',
            'specs'        => ['Type' => 'Full face', 'Size' => 'L (59-60cm)', 'Certified' => 'DOT', 'Ventilation' => 'Yes'],
        ]);
        $this->addImage($p, 'motorcycle-helmet-kh', true);

        $p = $this->makeProduct($bayonMotors, $catSports, $cGood, $brands['giant'], [
            'title'        => 'Giant Talon 3 Mountain Bike 26" 2021',
            'description'  => 'Giant Talon 3 mountain bike, 2021. 21-speed Shimano gears, hydraulic disc brakes. Ridden on weekends only, in very good condition. Frame size M.',
            'price_amount' => $this->usd(195),
            'location_city' => 'Battambang',
            'specs'        => ['Brand' => 'Giant', 'Model' => 'Talon 3', 'Wheel' => '26 inch', 'Gears' => '21 Speed Shimano', 'Frame' => 'Medium'],
        ]);
        $this->addImage($p, 'giant-bike-kh', true);

        // ─────────────────────────────────────────────────────────────────────
        // SELLER 5 — Chanthy Lim · Kampuchea Books
        // ─────────────────────────────────────────────────────────────────────
        $chanthy = $this->makeUser('Chanthy Lim', 'chanthy@example.com', 'seller', 'chanthy-lim');
        $kampucheaBooks = $this->makeStore($chanthy, [
            'name'        => 'Kampuchea Books',
            'slug'        => 'kampuchea-books',
            'description' => 'New and used books in Khmer and English. Educational materials, history, culture, and children\'s books. Based in Kampong Cham.',
            'city'        => 'Kampong Cham',
            'state'       => 'Kampong Cham',
        ]);

        $p = $this->makeProduct($kampucheaBooks, $catBooks, $cNew, null, [
            'title'        => 'Khmer Language Learning Workbook Series (5 Books)',
            'description'  => 'Complete Khmer literacy series for primary school students. Grades 1-5 workbooks, aligned with Cambodia Ministry of Education curriculum. Perfect for homeschooling or catch-up study.',
            'price_amount' => $this->usd(14),
            'stock_quantity' => 6,
            'location_city' => 'Kampong Cham',
            'specs'        => ['Language' => 'Khmer', 'Grades' => '1-5', 'Books' => '5 volumes', 'Publisher' => 'Ministry of Education KH'],
        ]);
        $this->addImage($p, 'khmer-workbooks', true);

        $p = $this->makeProduct($kampucheaBooks, $catBooks, $cNew, null, [
            'title'        => 'English for Cambodian High School Students',
            'description'  => 'Comprehensive English guide tailored for Cambodian Grade 10-12 students. Grammar, reading, writing, and exam practice sections.',
            'price_amount' => $this->usd(9),
            'stock_quantity' => 8,
            'location_city' => 'Kampong Cham',
            'specs'        => ['Language' => 'English / Khmer', 'Level' => 'High School (Grade 10-12)', 'Sections' => 'Grammar, Reading, Writing'],
        ]);
        $this->addImage($p, 'english-textbook-kh', true);

        $p = $this->makeProduct($kampucheaBooks, $catBooks, $cGood, null, [
            'title'        => 'The History of the Khmer Empire (English)',
            'description'  => 'Used but well-kept academic book on the history of the Khmer Empire from Funan to the fall of Angkor. University-level reading. No highlights.',
            'price_amount' => $this->usd(11),
            'location_city' => 'Kampong Cham',
            'specs'        => ['Language' => 'English', 'Topic' => 'Cambodian History', 'Level' => 'University', 'Condition' => 'No highlights or annotations'],
        ]);
        $this->addImage($p, 'khmer-history-book', true);

        $p = $this->makeProduct($kampucheaBooks, $catBooks, $cNew, null, [
            'title'        => "Children's Khmer Folk Tales (Illustrated) Set of 3",
            'description'  => 'Three beautifully illustrated Khmer folk tales for children aged 4-8: Tum Teav, Neang Kangrei, and Lok Ta Reach. Full colour, Khmer and English bilingual.',
            'price_amount' => $this->usd(10),
            'stock_quantity' => 5,
            'location_city' => 'Kampong Cham',
            'specs'        => ['Age' => '4-8 years', 'Language' => 'Khmer + English', 'Illustrations' => 'Full colour', 'Books' => '3 titles'],
        ]);
        $this->addImage($p, 'khmer-folk-tales', true);

        $p = $this->makeProduct($kampucheaBooks, $catBooks, $cNew, null, [
            'title'        => 'Khmer-English Business Dictionary',
            'description'  => 'Essential business vocabulary dictionary, Khmer-English and English-Khmer. Over 5,000 terms covering finance, law, trade, and technology. Pocket size.',
            'price_amount' => $this->usd(16),
            'stock_quantity' => 4,
            'location_city' => 'Kampong Cham',
            'specs'        => ['Entries' => '5,000+', 'Format' => 'Bilingual (KH-EN)', 'Size' => 'Pocket', 'Topics' => 'Finance, Law, Trade, Tech'],
        ]);
        $this->addImage($p, 'khmer-dictionary', true);

        // ─────────────────────────────────────────────────────────────────────
        // SELLER 6 — Bunna Sok · ActiveKH Sports
        // ─────────────────────────────────────────────────────────────────────
        $bunna = $this->makeUser('Bunna Sok', 'bunna@example.com', 'seller', 'bunna-sok');
        $activeKH = $this->makeStore($bunna, [
            'name'        => 'ActiveKH Sports',
            'slug'        => 'activekh-sports',
            'description' => 'Sports gear and fitness equipment in Phnom Penh. New and used items at fair prices. All items are clean and in good condition.',
            'city'        => 'Phnom Penh',
            'state'       => 'Phnom Penh',
        ]);

        $p = $this->makeProduct($activeKH, $catSports, $cNew, null, [
            'title'        => 'Victor Badminton Racket Duo Set',
            'description'  => 'Set of 2 Victor badminton rackets + 3 shuttlecocks. Great for casual play. String tension 22lb. Full-length cover included.',
            'price_amount' => $this->usd(32),
            'stock_quantity' => 4,
            'location_city' => 'Phnom Penh',
            'specs'        => ['Brand' => 'Victor', 'Rackets' => '2', 'Tension' => '22lb', 'Shuttlecocks' => '3 included'],
        ]);
        $this->addImage($p, 'badminton-victor-kh', true);

        $p = $this->makeProduct($activeKH, $catSports, $cNew, $brands['nike'], [
            'title'        => 'Nike Football Size 5 Premier League',
            'description'  => 'Official Nike football, size 5. Suitable for futsal and outdoor play. Used at 2 training sessions, excellent condition.',
            'price_amount' => $this->usd(22),
            'stock_quantity' => 3,
            'location_city' => 'Phnom Penh',
            'specs'        => ['Brand' => 'Nike', 'Size' => '5', 'Type' => 'Outdoor + Futsal', 'Color' => 'White/Black'],
        ]);
        $this->addImage($p, 'nike-football-kh', true);

        $p = $this->makeProduct($activeKH, $catSports, $cNew, null, [
            'title'        => 'Speedo Futura Classic Swim Goggles',
            'description'  => 'Speedo swim goggles with UV protection and anti-fog coating. One size fits most adults. Suitable for competitive and recreational swimming.',
            'price_amount' => $this->usd(12),
            'stock_quantity' => 5,
            'location_city' => 'Phnom Penh',
            'specs'        => ['Brand' => 'Speedo', 'UV Protection' => 'Yes', 'Anti-Fog' => 'Yes', 'Size' => 'Adult Universal'],
        ]);
        $this->addImage($p, 'speedo-goggles-kh', true);

        $p = $this->makeProduct($activeKH, $catSports, $cLikeNew, null, [
            'title'        => 'Adjustable Dumbbell Set 5-25kg',
            'description'  => 'Adjustable dumbbell set with rack. 5kg to 25kg per dumbbell. Used at home gym for 6 months. Perfect condition. Compact for small apartments.',
            'price_amount' => $this->usd(88),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Weight Range' => '5-25kg each', 'Adjustable' => 'Yes', 'Material' => 'Steel + Rubber coating', 'Rack' => 'Included'],
        ]);
        $this->addImage($p, 'dumbbell-set-kh', true);

        $p = $this->makeProduct($activeKH, $catSports, $cGood, null, [
            'title'        => 'Kick Scooter for Kids Age 5-12',
            'description'  => 'Kids kick scooter, adjustable height. T-bar can be set to 3 heights. Rear foot brake. Light use. Clean and functional. My son outgrew it.',
            'price_amount' => $this->usd(26),
            'location_city' => 'Phnom Penh',
            'specs'        => ['Age' => '5-12 years', 'Max Weight' => '50kg', 'Brake' => 'Rear foot brake', 'Handle' => 'Adjustable 3 heights'],
        ]);
        $this->addImage($p, 'kids-scooter-kh', true);

        // ─────────────────────────────────────────────────────────────────────
        // BUYERS ───────────────────────────────────────────────────────────────
        $this->makeUser('Kosal Meas',  'kosal@example.com',  'user', 'kosal-meas');
        $this->makeUser('Linda Chea',  'linda@example.com',  'user', 'linda-chea');
        $this->makeUser('Vanna Khun',  'vanna@example.com',  'user', 'vanna-khun');
        $this->makeUser('Pisey Heng',  'pisey@example.com',  'user', 'pisey-heng');
        $this->makeUser('Ratha Sok',   'ratha@example.com',  'user', 'ratha-sok');
        $this->makeUser('Sreyla Nget', 'sreyla@example.com', 'user', 'sreyla-nget');

        $this->command->info('Cambodia marketplace seeded: 6 sellers, 6 stores, 35 products, 6 buyers, 1 admin.');
    }
}
