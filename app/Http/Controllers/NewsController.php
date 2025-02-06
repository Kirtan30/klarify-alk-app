<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Traits\NewsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    use NewsTrait;

    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');

        $news = News::where('user_id', $shop->id);

        if (!empty($search)) {
            $news = $news->where(function ($query) use ($search) {
                $query->where('title', 'like', "%$search%")
                      ->orWhere('date', 'like', "%$search%");
            });
        }
        $news = $news->paginate($perPage);

        $news->map(function ($newsItem) {
            $newsItem->news_date = $newsItem->date ? Carbon::parse($newsItem->date)->format('d M Y g:i A (T)') : null;
        });

        return response(['news' => $news], 200);
    }

    public function show(Request $request, News $news)
    {
        return response(['news' => $news], 200);
    }

    public function store(Request $request)
    {
        $shop = $request->user();

        $request->validate([
            'title' => 'required',
            'cta' => 'nullable|array'
        ]);

        $cta = $request->input('cta') ?: [];
        $preparedCta = [];

        foreach ($cta as $ctaItem) {
            $label = data_get($ctaItem, 'label');
            $link = data_get($ctaItem, 'link');

            if (!empty($label) || !empty($link)) {
                if (empty($link)) {
                    return response(['message' => 'Cta Link is required with label'], 422);
                }
                if (empty($label)) {
                    return response(['message' => 'Cta Label is required with link'], 422);
                }
                $preparedCta[] = $ctaItem;
            }
        }

        $news = DB::transaction(function () use ($request, $shop, $preparedCta) {
            return News::create([
                'uuid' => str()->uuid(),
                'user_id' => $shop->id,
                'title' => $request->input('title'),
                'description' => $request->input('description') ?: null,
                'date' => $request->input('date'),
                'cta' => $preparedCta,
            ]);
        });

        return response(['news' => $news, 'message' => 'Quiz saved successfully'], 200);
    }

    public function update(Request $request, News $news)
    {
        $shop = $request->user();

        $request->validate([
            'title' => 'required',
            'cta' => 'nullable|array|max:2'
        ]);

        if ($news->image !== $request->input('image')) {
            $this->removeImage($shop, $news);
        }

        $cta = $request->input('cta') ?: [];
        $preparedCta = [];

        foreach ($cta as $ctaItem) {
            $label = data_get($ctaItem, 'label');
            $link = data_get($ctaItem, 'link');

            if (!empty($label) || !empty($link)) {
                if (empty($link)) {
                    return response(['message' => 'Cta Link is required with label'], 422);
                }
                if (empty($label)) {
                    return response(['message' => 'Cta Label is required with link'], 422);
                }
                $preparedCta[] = $ctaItem;
            }
        }

        $news = DB::transaction(function () use ($request, $news, $preparedCta) {
            $news->update([
                'title' => $request->input('title'),
                'description' => $request->input('description') ?: null,
                'date' => $request->input('date'),
                'cta' => $preparedCta,
            ]);

            return $news;
        });

        return response(['news' => $news, 'message' => 'Quiz saved successfully'], 200);
    }

    public function upload(Request $request, News $news)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        try {

            $shop = $request->user();
            $imageLink = $this->manageImage($request, $shop);

            if ($imageLink) {
                $news->update(['image' => $imageLink]);
            }

            return response(['image' => $imageLink]);

        } catch (\Exception $e) {

            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request, News $news)
    {
        $news->delete();
        return response([]);
    }
}
