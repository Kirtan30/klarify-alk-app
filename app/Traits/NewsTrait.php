<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait NewsTrait
{
    public function manageImage($request, $shop)
    {

        $imageLink = null;
        $basePath = "news/images/$shop->name";

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $fileName = rand(111111, 999999) . '_' . $image->getClientOriginalName();
            Storage::disk('public')->putFileAs($basePath, $image, $fileName);
            $imageLink = url("storage/$basePath/$fileName");
        }

        return $imageLink;
    }

    public function removeImage($shop, $news)
    {
        $basePath = "news/images/$shop->name";

        if ($news && $news->image) {
            $path = parse_url($news->image, PHP_URL_PATH);
            $path = "$basePath/" . basename($path);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
