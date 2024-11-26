<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Wishlist extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'course_id'];
    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }


    public static function getOrCreateForUser($user)
    {
        return self::firstOrCreate(['user_id' => $user->id]);
    }

    // Lấy danh sách khóa học được định dạng cho response
    public function getFormattedItems()
    {
        return self::where('user_id', Auth::id())
            ->with('course')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->course->id,
                    'thumbnail' => $item->course->thumbnail,
                    'title' => $item->course->title,
                    'category_name' => $item->course->category->name ?? null,
                    'creator' => $item->course->creator
                        ? trim($item->course->creator->last_name . ' ' . $item->course->creator->first_name)
                        : null,
                    'old_price' => round($item->course->price),
                    'current_price' => $item->course->price, // Giá hiện tại
                    'average_rating' => round($item->course->reviews->avg('rating'), 1),
                    'reviews_count' => $item->course->reviews->count(),
                    'total_duration' => $item->course->total_duration,
                    'lectures_count' => $item->course->lectures_count,
                    'level' => $item->course->level->name ?? null,
                ];
            });
    }
    // Xóa tất cả các mục trong giỏ hàng
    public function clearWishlist()
    {
        $this->where('user_id', Auth::id())->delete();
        $this->cartItems()->delete();
    }
}
