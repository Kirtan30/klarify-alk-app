<?php

namespace App\Http\Controllers;

use App\Models\FadRegion;
use App\Rules\LatitudeRule;
use App\Rules\LongitudeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FadRegionController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');
        $enabled = (bool) data_get($request, 'enabled');
        $languageId = data_get($request, 'language');

        $fadRegions = FadRegion::where('user_id', $shop->id);

        if (!empty($languageId)) {
            $fadRegions =  $fadRegions->where('language_id', $languageId);
        }

        if (!empty($search)) {
            $fadRegions = $fadRegions->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('handle', 'like', "%$search%");
            });
        }

        if (!empty($enabled)) {
            $fadRegions = $fadRegions->where('enabled', $enabled);
        }

        if ($perPage == -1) {
            $fadRegions = $fadRegions->get();
            $fadRegions = [
                'data' => $fadRegions,
                'total' => $fadRegions->count()
            ];
        } else {
            $fadRegions = $fadRegions->paginate($perPage);
        }

        $shopLanguages = $shop->languages->mapWithKeys(function ($item, $key) {
            return [$item['id'] => $item['name']];
        });

        return response(['fadRegions' => $fadRegions, 'shopLanguages' => $shopLanguages], 200);
    }

    public function show(Request $request, FadRegion $fadRegion)
    {
        $fadRegion->load('fadPageContent');
        return response(['fadRegion' => $fadRegion], 200);
    }
//
//    public function store(Request $request)
//    {
//        $shop = $request->user();
//
//        $request->validate([
//            'state_id' => 'required',
//            'name' => 'required',
//            'handle' => 'required|unique:fad_regions',
//            'latitude' => ['required', new LatitudeRule],
//            'longitude' => ['required', new LongitudeRule],
//        ]);
//
//        $fadRegion = DB::transaction(function () use ($request, $shop) {
//            return FadRegion::create([
//                'user_id' => $shop->id,
//                'state_id' => data_get($request, 'state_id'),
//                'name' => $request->input('name'),
//                'handle' => str($request->input('handle'))->slug(),
//                'latitude' => $request->input('latitude') ?: null,
//                'longitude' => $request->input('longitude') ?: null,
//                'has_static_content' => $request->input('has_static_content') ?: false,
//                'is_popular' => $request->input('is_popular') ?: false,
//                'content' => $request->input('content') ?: null
//            ]);
//        });
//
//        return response(['fadRegion' => $fadRegion, 'message' => 'Fad region saved successfully'], 200);
//    }

    public function update(Request $request, FadRegion $fadRegion)
    {
        $shop = $request->user();
        $languageId = data_get($fadRegion, 'language_id');

        $request->validate([
            'name' => 'required',
            'handle' => [
                'required',
                Rule::unique('fad_regions')->where('user_id', $shop->id)->where('language_id', $languageId)->ignore($fadRegion)
            ],
            'fad_page_content_id' => 'required_if:has_static_content,true',
        ]);

        $fadRegion = DB::transaction(function () use ($request, $shop, $fadRegion) {
            $fadRegion->update([
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'has_static_content' => $request->input('has_static_content') ?: false,
                'is_popular' => $request->input('is_popular') ?: false,
                'fad_page_content_id' => data_get($request, 'has_static_content') ? $request->input('fad_page_content_id') : $fadRegion->fad_page_content_id,
                'enabled' => $request->input('enabled') ?: false,
                'variables' => $request->input('variables') ?: null,
            ]);

            return $fadRegion;
        });

        return response(['fadRegion' => $fadRegion, 'message' => 'Fad region udpated successfully'], 200);
    }

//    public function delete(FadRegion $fadRegion) {
//
//        $fadRegion->delete();
//        return response(['message' => 'deleted successfully'], 200);
//    }
}
