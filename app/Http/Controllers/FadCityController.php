<?php

namespace App\Http\Controllers;

use App\Jobs\SyncMetafields;
use App\Models\FadCity;
use App\Services\GoogleApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FadCityController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');
        $languageId = data_get($request, 'language');

        $fadCities = FadCity::where('user_id', $shop->id)->with(['fadRegion', 'fadState']);

        if (!empty($languageId)) {
            $fadCities =  $fadCities->where('language_id', $languageId);
        }

        if (!empty($search)) {
            $fadCities = $fadCities->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('handle', 'like', "%$search%");
            });
        }
        $fadCities = $fadCities->paginate($perPage);

        $shopLanguages = $shop->languages->mapWithKeys(function ($item, $key) {
            return [$item['id'] => $item['name']];
        });

        return response(['fadCities' => $fadCities, 'shopLanguages' => $shopLanguages], 200);
    }

    public function show(FadCity $fadCity)
    {
        $fadCity->load(['fadState', 'fadRegion', 'fadPageContent']);
        return response(['fadCity' => $fadCity], 200);
    }

    /*public function store(Request $request)
    {
        $shop = $request->user();

        $request->validate([
            'state_id' => 'required',
            'name' => 'required',
            'handle' => 'required|unique:fad_cities',
            'latitude' => ['required', new LatitudeRule],
            'longitude' => ['required', new LongitudeRule],
        ]);

        $fadCity = DB::transaction(function () use ($request, $shop) {
            return FadCity::create([
                'user_id' => $shop->id,
                'state_id' => data_get($request, 'state_id'),
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'latitude' => $request->input('latitude') ?: null,
                'longitude' => $request->input('longitude') ?: null,
                'has_static_content' => $request->input('has_static_content') ?: false,
                'content' => $request->input('content') ?: null
            ]);
        });

        return response(['fadCity' => $fadCity, 'message' => 'Fad city saved successfully'], 200);
    }*/

    public function update(Request $request, FadCity $fadCity)
    {
        $shop = $request->user();
        $languageId = data_get($fadCity, 'language_id');

        $request->validate([
            'fad_state_id' => 'sometimes|exists:fad_states,id',
            'fad_region_id' => 'sometimes|exists:fad_regions,id',
            'fad_page_content_id' => 'required_if:has_static_content,true',
            'name' => 'required',
            'handle' => [
                    'required',
                    Rule::unique('fad_cities')->where('user_id', $shop->id)->where('language_id', $languageId)->ignore($fadCity)
                ]
        ]);

        $fadCity = DB::transaction(function () use ($request, $shop, $fadCity) {
            $fadCity->update([
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'has_static_content' => $request->input('has_static_content') ?: false,
                'is_popular' => $request->input('is_popular') ?: false,
                'fad_page_content_id' => data_get($request, 'has_static_content') ? $request->input('fad_page_content_id') : $fadCity->fad_page_content_id,
                'enabled' => $request->input('enabled') ?: false,
                'fad_state_id' => $request->input('fad_state_id') ?: null,
                'fad_region_id' => $request->input('fad_region_id') ?: null,
                'variables' => $request->input('variables') ?: null,
            ]);

            return $fadCity;
        });

        return response(['fadCity' => $fadCity, 'message' => 'Fad city updated successfully'], 200);
    }

    /*public function delete(FadCity $fadCity) {
        $fadCity->delete();
        return response(['message' => 'deleted successfully'], 200);
    }*/

    public function sync(Request $request)
    {

        $shop = $request->user();

        SyncMetafields::dispatch($shop);
        return response(['message' => 'Metafields will be synced soon!']);
    }
}
