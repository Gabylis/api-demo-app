<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics',   'description' => 'Gadgets and electronic devices'],
            ['name' => 'Clothing',      'description' => 'Apparel and fashion items'],
            ['name' => 'Books',         'description' => 'Physical and digital books'],
            ['name' => 'Home & Garden', 'description' => 'Home improvement and garden supplies'],
        ];

        foreach ($categories as $cat) {
            $category = Category::create([
                'name'        => $cat['name'],
                'slug'        => Str::slug($cat['name']),
                'description' => $cat['description'],
            ]);

            // 5 products per category
            for ($i = 1; $i <= 5; $i++) {
                Product::create([
                    'category_id' => $category->id,
                    'name'        => "{$cat['name']} Product {$i}",
                    'sku'         => strtoupper(Str::slug($cat['name'])) . "-{$i}",
                    'description' => "Sample product {$i} in {$cat['name']}",
                    'price'       => rand(10, 500) + (rand(0, 99) / 100),
                    'stock'       => rand(0, 100),
                    'active'      => true,
                ]);
            }
        }

        $this->command->info('Seeded 4 categories and 20 products.');
    }
}
