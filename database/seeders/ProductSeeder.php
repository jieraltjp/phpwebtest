<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'ALIBABA_SKU_A123',
                'name' => '日本客户专用 办公椅',
                'description' => '人体工学设计，适合长时间办公使用，支持多角度调节',
                'price' => 1250.50,
                'currency' => 'CNY',
                'image_url' => 'https://cdn.alibaba.com/img/A123.jpg',
                'supplier_shop' => 'XX家具旗舰店',
                'specs' => json_encode([
                    'Color' => 'Black',
                    'Size' => 'Large',
                    'Material' => 'Mesh + Aluminum',
                    'Weight' => '15kg'
                ]),
                'stock' => 100,
                'active' => true,
            ],
            [
                'sku' => 'ALIBABA_SKU_B456',
                'name' => '无线蓝牙键盘',
                'description' => '87键机械键盘，背光设计，适合游戏和办公',
                'price' => 280.00,
                'currency' => 'CNY',
                'image_url' => 'https://cdn.alibaba.com/img/B456.jpg',
                'supplier_shop' => '数码配件专营店',
                'specs' => json_encode([
                    'Color' => 'White',
                    'Switch' => 'Blue',
                    'Connection' => 'Bluetooth 5.0',
                    'Battery' => '2000mAh'
                ]),
                'stock' => 50,
                'active' => true,
            ],
            [
                'sku' => 'ALIBABA_SKU_C789',
                'name' => 'USB-C 扩展坞',
                'description' => '7合1扩展坞，支持4K HDMI输出，100W PD充电',
                'price' => 189.99,
                'currency' => 'CNY',
                'image_url' => 'https://cdn.alibaba.com/img/C789.jpg',
                'supplier_shop' => '电脑配件商城',
                'specs' => json_encode([
                    'Ports' => '7',
                    'HDMI' => '4K@30Hz',
                    'USB 3.0' => '3 ports',
                    'SD Card' => 'UHS-I',
                    'Power Delivery' => '100W'
                ]),
                'stock' => 75,
                'active' => true,
            ],
            [
                'sku' => 'ALIBABA_SKU_D012',
                'name' => '笔记本电脑支架',
                'description' => '铝合金材质，可调节高度和角度，散热设计',
                'price' => 85.50,
                'currency' => 'CNY',
                'image_url' => 'https://cdn.alibaba.com/img/D012.jpg',
                'supplier_shop' => '办公用品直销',
                'specs' => json_encode([
                    'Material' => 'Aluminum Alloy',
                    'Adjustable' => 'Yes',
                    'Max Load' => '10kg',
                    'Color' => 'Silver'
                ]),
                'stock' => 200,
                'active' => true,
            ],
            [
                'sku' => 'ALIBABA_SKU_E345',
                'name' => '网络摄像头',
                'description' => '1080P高清摄像头，自动对焦，内置麦克风',
                'price' => 320.00,
                'currency' => 'CNY',
                'image_url' => 'https://cdn.alibaba.com/img/E345.jpg',
                'supplier_shop' => '视频设备专营',
                'specs' => json_encode([
                    'Resolution' => '1080P',
                    'Field of View' => '90°',
                    'Microphone' => 'Built-in',
                    'Connection' => 'USB 2.0'
                ]),
                'stock' => 30,
                'active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}