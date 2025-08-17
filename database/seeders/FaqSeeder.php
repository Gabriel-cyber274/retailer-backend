<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I buy a product?',
                'answer'   => 'Browse the marketplace, add products to your cart, and checkout securely using your preferred payment method.',
                'category' => 'Buying',
            ],
            [
                'question' => 'How can I create my own shop?',
                'answer'   => 'Sign up, go to your dashboard, and click "Create Shop". You’ll get a unique custom website link instantly.',
                'category' => 'Selling',
            ],
            [
                'question' => 'Do I need inventory to start selling?',
                'answer'   => 'No. You can resell products directly from our marketplace without keeping stock.',
                'category' => 'Selling',
            ],
            [
                'question' => 'How do I get paid?',
                'answer'   => 'Your earnings are added to your wallet balance. You can request withdrawals directly to your bank account.',
                'category' => 'Payments',
            ],
            [
                'question' => 'Can I customize my shop website?',
                'answer'   => 'Yes, you can upload your logo, set your brand colors, and personalize your shop’s appearance.',
                'category' => 'Shop',
            ],
            [
                'question' => 'Is there a fee for using the platform?',
                'answer'   => 'Creating a shop is free. A small transaction fee applies when customers make purchases.',
                'category' => 'General',
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}
