<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $attributes = [
        'category_id' => 0,
    ];

    protected $casts = [
        'allow_note' => 'boolean',
        'allow_note_image' => 'boolean',
        'purchased' => 'float',
        'offered' => 'float',
        'selling' => 'float',
        'review_count' => 'integer',
        'rating' => 'integer',
        'available_for_delivery_today' => 'boolean',
    ];


    public $timestamps = true;
    protected $fillable = [
        'id',
        'title',
        'purchased',
        'selling',
        'offered',
        'image',
        'unit',
        'video',
        'video_thumb',
        'badge',
        'status',
        'admin_id',
        'subcategory_id',
        'category_id',
        'brand_id',
        'warranty',
        'refundable',
        'available_for_delivery_today',
        'description',
        'overview',
        'tags',
        'tax_rule_id',
        'shipping_rule_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'allow_note', 'allow_note_image',
        'review_count', 'rating', 'bundle_deal_id', 'slug','external_id','item_number','created_at', 'updated_at'
    ];

//    protected static function booted(): void
//    {
//        static::created(function ($product) {
//            $product->slug = Str::slug($product->title) . '-' . $product->id;
//            $product->save();
//        });
//    }

    protected $hidden = [];


    public function flash_sale_product()
    {
        return $this->hasMany(FlashSaleProduct::class, 'product_id', 'id')
            ->with('flash_sale');
    }


    public function tax_rules()
    {
        return $this->hasOne(TaxRules::class, 'id', 'tax_rule_id');
    }


    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->select(['id', 'title', 'slug']);
    }


    public function product_categories()
    {
        return $this->hasMany(ProductCategory::class, 'product_id', 'id')
            ->orderBy('primary', 'DESC');
    }



    public function shipping_rule()
    {
        return $this->hasOne(ShippingRule::class, 'id', 'shipping_rule_id')
            ->select(['id', 'title', 'single_price']);
    }


    public function product_collections()
    {
        return $this->hasMany(CollectionWithProduct::class, 'product_id', 'id')
            ->select(['id', 'product_id', 'product_collection_id']);
    }


    public function product_inventories()
    {
        return $this->hasMany(UpdatedInventory::class, 'product_id', 'id');
    }


    public function product_images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }


    public function product_image_names()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function store()
    {
        return $this->hasOne(Store::class, 'admin_id', 'admin_id');
    }


    public function bundle_deal()
    {
        return $this->hasOne(BundleDeal::class, 'id', 'bundle_deal_id')
            ->select(['id', 'buy', 'free', 'title']);
    }


    public function brand()
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id')
            ->select(['title', 'id']);
    }


    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }

}
