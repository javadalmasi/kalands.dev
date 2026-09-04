<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookmarkRequest;
use App\Models\Bookmark;
use App\Models\BookmarkCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $this->syncUserCategoriesFromBookmarks((int) $user->id);

        $selectedCategory = trim((string) $request->query('category'));
        $activeCategory = $selectedCategory !== '' ? $this->normalizeCategoryName($selectedCategory) : null;

        $bookmarks = Bookmark::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->when($activeCategory !== null, fn ($query) => $query->where('category_name', $activeCategory))
            ->latest()
            ->get();

        $categories = BookmarkCategory::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->pluck('name');

        return view('profile.bookmarks', compact('bookmarks', 'categories', 'activeCategory'));
    }

    public function store(StoreBookmarkRequest $request)
    {
        $userId = (int) auth()->id();
        $bookmark = Bookmark::query()
            ->where('product_id', request('product_id'))
            ->where('user_id', $userId)
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return back()->with('message', 'از بوک مارک ها حذف شد.');
        }

        $categoryName = $this->normalizeCategoryName($request->input('category_name'));
        $this->ensureCategoryExists($userId, $categoryName);

        Bookmark::query()->create([
            'product_id' => request('product_id'),
            'user_id' => $userId,
            'category_name' => $categoryName,
        ]);

        return back()->with('message', 'باموفقیت به بوکمارک ها افزوده شد.');
    }

    public function delete(string $authkey, Bookmark $bookmark)
    {
        if ($bookmark->user_id != auth()->id()) {
            abort(403);
        }

        $bookmark->delete();

        return back()->with('message', 'محصول از بوکمارک ها حذف شد.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        $userId = (int) auth()->id();
        $normalizedName = $this->normalizeCategoryName($data['name']);

        BookmarkCategory::query()->firstOrCreate([
            'user_id' => $userId,
            'name' => $normalizedName,
        ]);

        return back()->with('message', 'دسته‌بندی جدید ذخیره شد.');
    }

    public function updateCategory(string $authkey, Request $request, Bookmark $bookmark): RedirectResponse|JsonResponse
    {
        if ($bookmark->user_id !== auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'category_name' => ['required', 'string', 'max:50'],
        ]);

        $categoryName = $this->normalizeCategoryName($data['category_name']);
        $this->ensureCategoryExists((int) auth()->id(), $categoryName);

        $bookmark->update([
            'category_name' => $categoryName,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => 'دسته‌بندی بوکمارک بروزرسانی شد.',
                'category_name' => $categoryName,
            ]);
        }

        return back()->with('message', 'دسته‌بندی بوکمارک بروزرسانی شد.');
    }

    private function normalizeCategoryName(?string $name): string
    {
        $normalized = trim((string) $name);

        return $normalized !== '' ? $normalized : 'عمومی';
    }

    private function ensureCategoryExists(int $userId, string $name): void
    {
        BookmarkCategory::query()->firstOrCreate([
            'user_id' => $userId,
            'name' => $name,
        ]);
    }

    private function syncUserCategoriesFromBookmarks(int $userId): void
    {
        $bookmarkCategoryNames = Bookmark::query()
            ->where('user_id', $userId)
            ->whereNotNull('category_name')
            ->pluck('category_name')
            ->filter()
            ->map(static fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        if ($bookmarkCategoryNames->isEmpty()) {
            return;
        }

        $existingNames = BookmarkCategory::query()
            ->where('user_id', $userId)
            ->pluck('name');

        $missingNames = $bookmarkCategoryNames->diff($existingNames)->values();
        if ($missingNames->isEmpty()) {
            return;
        }

        $now = now();
        $records = $missingNames->map(static fn ($name) => [
            'user_id' => $userId,
            'name' => $name,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        BookmarkCategory::query()->insert($records);
    }
}
