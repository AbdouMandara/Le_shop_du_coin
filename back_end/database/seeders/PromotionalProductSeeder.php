<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Promotion;
use App\Models\Category;
use App\Enums\PromotionType;
use App\Enums\PromotionStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PromotionalProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a main active promotion
        $promotion = Promotion::create([
            'name' => 'Offre Spéciale Ramadan',
            'type' => PromotionType::PERCENTAGE,
            'value' => 20,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'status' => PromotionStatus::ACTIVE,
        ]);

        $categories = Category::all();
        if ($categories->isEmpty()) {
            return;
        }

        $productsData = [
            [
                'name' => 'Smartphone Ultra Pro X',
                'description' => 'Le nec plus ultra de la technologie mobile avec un écran AMOLED 120Hz.',
                'price' => 450000,
                'quantity' => 15,
                'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&q=80',
            ],
            [
                'name' => 'Panneau Solaire 400W Monocristallin',
                'description' => 'Haute efficacité pour une autonomie énergétique totale au Cameroun.',
                'price' => 125000,
                'quantity' => 50,
                'image' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?w=800&q=80',
            ],
            [
                'name' => 'Réfrigérateur Solaire Dual Tech',
                'description' => 'Conservez vos aliments frais même en cas de coupure de courant.',
                'price' => 320000,
                'quantity' => 8,
                'image' => 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=800&q=80',
            ],
            [
                'name' => 'Smart Watch Elite Series',
                'description' => 'Suivi santé, notifications et autonomie de 14 jours.',
                'price' => 65000,
                'quantity' => 25,
                'image' => 'https://images.unsplash.com/photo-1544117518-33dd0ffb1dc3?w=800&q=80',
            ],
            [
                'name' => 'Kit Éclairage Solaire Rural',
                'description' => 'Solution idéale pour les zones reculées avec 3 lampes LED et port USB.',
                'price' => 45000,
                'quantity' => 100,
                'image' => 'https://images.unsplash.com/photo-1555664424-778a1e5e1b48?w=800&q=80',
            ],
            [
                'name' => 'Ordinateur Ultra-Slim i7',
                'description' => 'Puissance et élégance pour les professionnels exigeants.',
                'price' => 580000,
                'quantity' => 10,
                'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&q=80',
            ],
            [
                'name' => 'Batterie Lithium 100Ah Deep Cycle',
                'description' => 'Doublez la durée de vie de votre installation solaire.',
                'price' => 210000,
                'quantity' => 20,
                'image' => 'https://images.unsplash.com/photo-1620641788421-7a1c342ea42e?w=800&q=80',
            ],
            [
                'name' => 'Téléviseur 4K Smart TV 55"',
                'description' => 'Une expérience cinéma immersive directement dans votre salon.',
                'price' => 285000,
                'quantity' => 12,
                'image' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&q=80',
            ],
            [
                'name' => 'Modem 4G Turbo WiFi',
                'description' => 'Internet haut débit partout où vous allez.',
                'price' => 35000,
                'quantity' => 40,
                'image' => 'https://images.unsplash.com/photo-1563770660941-20978e870e26?w=800&q=80',
            ],
            [
                'name' => 'Générateur Solaire Portable 500Wh',
                'description' => 'Votre centrale électrique de poche pour tous vos appareils.',
                'price' => 175000,
                'quantity' => 15,
                'image' => 'https://images.unsplash.com/photo-1594819047050-99def0f34123?w=800&q=80',
            ],
        ];

        foreach ($productsData as $data) {
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'quantity' => $data['quantity'],
                'image' => $data['image'],
                'category_id' => $categories->random()->id,
            ]);

            // Link to promotion
            $product->promotions()->attach($promotion->id);
        }
    }
}
