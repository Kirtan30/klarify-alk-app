<?php

namespace App\Http\Controllers;

use App\Models\PollenRegion;
use App\Rules\LatitudeRule;
use App\Rules\LongitudeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PollenRegionController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');
        $languageId = data_get($request, 'languageId');

        $pollenRegions = PollenRegion::where('user_id', $shop->id);

        if (!empty($languageId)) {
            $pollenRegions =  $pollenRegions->where('language_id', $languageId);
        }

        if (!empty($search)) {
            $pollenRegions = $pollenRegions->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('handle', 'like', "%$search%");
            });
        }

        if ($perPage == -1) {
            $pollenRegions = $pollenRegions->get();
            $pollenRegions = [
                'data' => $pollenRegions,
                'total' => $pollenRegions->count()
            ];
        } else {
            $pollenRegions = $pollenRegions->paginate($perPage);
        }

        $shopLanguages = $shop->languages->mapWithKeys(function ($item, $key) {
            return [$item['id'] => $item['name']];
        });

        return response(['pollenRegions' => $pollenRegions, 'shopLanguages' => $shopLanguages], 200);
    }

    public function show(Request $request, PollenRegion $pollenRegion)
    {
        $pollenRegion->load('pollenPageContent', 'pollenLanguage', 'pollenParent');
        return response(['pollenRegion' => $pollenRegion], 200);
    }

    public function store(Request $request)
    {
        $shop = $request->user();
        $languageId = data_get($request, 'language_id');
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        $defaultLanguageId = data_get($defaultLanguage, 'id');
        $parentId = data_get($request, 'parent_id');

        $request->validate([
            'language_id' => [
                'required',
                Rule::exists('user_language', 'language_id')->where('user_id', $shop->id)
            ],
            'name' => 'required',
            'handle' => [Rule::unique('pollen_regions')->where('user_id', $shop->id), 'required'],
            'pollen_page_content_id' => 'required_if:has_static_content,true',
            'parent_id' => array_filter([
                'sometimes',
                $parentId ? Rule::exists('pollen_regions', 'id')->where('user_id', $shop->id) : null,
                $parentId ? Rule::prohibitedIf($defaultLanguageId === $languageId) : null
            ]),
            'latitude' => ['required', new LatitudeRule],
            'longitude' => ['required', new LongitudeRule],
        ],
        [
            'language_id.required' => 'Language field is required',
            'parent_id.prohibited' => 'Parent id cannot be added to default language'
        ]);

        $pollenRegion = DB::transaction(function () use ($request, $shop, $languageId) {
            return PollenRegion::create([
                'user_id' => $shop->id,
                'language_id' => $languageId,
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'latitude' => $request->input('latitude') ?: null,
                'longitude' => $request->input('longitude') ?: null,
                'has_static_content' => $request->input('has_static_content') ?: false,
                'pollen_page_content_id' => data_get($request, 'has_static_content') ? $request->input('pollen_page_content_id') : null,
                'parent_id' => $request->input('parent_id') ?: null,
                'is_popular' => $request->input('is_popular') ?: false,
                'variables' => $request->input('variables') ?: null,
            ]);
        });

        return response(['pollenRegion' => $pollenRegion, 'message' => 'Pollen region saved successfully'], 200);
    }

    public function update(Request $request, PollenRegion $pollenRegion)
    {
        $shop = $request->user();
        $languageId = data_get($request, 'language_id');
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        $defaultLanguageId = data_get($defaultLanguage, 'id');
        $parentId = data_get($request, 'parent_id');

        $request->validate([
            'language_id' => [
                'required',
                Rule::exists('user_language', 'language_id')->where('user_id', $shop->id)
            ],
            'name' => 'required',
            'handle' => [Rule::unique('pollen_regions')->where('user_id', $shop->id)->ignore($pollenRegion), 'required'],
            'pollen_page_content_id' => 'required_if:has_static_content,true',
            'parent_id' => array_filter([
                'sometimes',
                $parentId ? Rule::exists('pollen_regions', 'id')->where('user_id', $shop->id) : null,
                $parentId ? Rule::prohibitedIf($defaultLanguageId === $languageId) : null
            ]),
        ],
        [
            'language_id.required' => 'Language field is required',
            'parent_id.prohibited' => 'Parent id cannot be added to default language'
        ]);

        $pollenRegion = DB::transaction(function () use ($request, $shop, $pollenRegion, $languageId) {
            $pollenRegion->update([
                'language_id' => $languageId,
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'latitude' => $request->input('latitude') ?: null,
                'longitude' => $request->input('longitude') ?: null,
                'has_static_content' => $request->input('has_static_content') ?: false,
                'pollen_page_content_id' => data_get($request, 'has_static_content') ? $request->input('pollen_page_content_id') : $pollenRegion->pollen_page_content_id,
                'parent_id' => $request->input('parent_id') ?: null,
                'is_popular' => $request->input('is_popular') ?: false,
                'variables' => $request->input('variables') ?: null,
            ]);

            if ($pollenRegion->wasChanged('language_id')) {
                $pollenRegion->pollenCities()->update(['pollen_region_id' => null]);
            }

            return $pollenRegion;
        });

        return response(['pollenRegion' => $pollenRegion, 'message' => 'Pollen region udpated successfully'], 200);
    }

    public function delete(PollenRegion $pollenRegion) {

        $pollenRegion->delete();
        return response(['message' => 'deleted successfully'], 200);
    }

    public function defaultRegions(Request $request) {
        $shop = $request->user();
        if (empty($shop)) return;

        $pollenRegionId = data_get($request, 'pollenRegionId');
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        $defaultLanguageId = data_get($defaultLanguage, 'id');

        $defaultRegions = PollenRegion::where('user_id', $shop->id)->where('language_id', $defaultLanguageId);
        if (!empty($pollenRegionId)) {
            $defaultRegions = $defaultRegions->where('id', '!=', $pollenRegionId);
        }

        $defaultRegions = $defaultRegions->get();
        return response(['defaultRegions' => $defaultRegions], 200);
    }
}
