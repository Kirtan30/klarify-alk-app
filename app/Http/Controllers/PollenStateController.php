<?php

namespace App\Http\Controllers;

use App\Models\PollenState;
use App\Rules\LatitudeRule;
use App\Rules\LongitudeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PollenStateController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');
        $languageId = data_get($request, 'languageId');

        $pollenStates = PollenState::where('user_id', $shop->id);

        if (!empty($languageId)) {
            $pollenStates =  $pollenStates->where('language_id', $languageId);
        }

        if (!empty($search)) {
            $pollenStates = $pollenStates->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('handle', 'like', "%$search%");
            });
        }

        if ($perPage == -1) {
            $pollenStates = $pollenStates->get();
            $pollenStates = [
                'data' => $pollenStates,
                'total' => $pollenStates->count()
            ];
        } else {
            $pollenStates = $pollenStates->paginate($perPage);
        }

        $shopLanguages = $shop->languages->mapWithKeys(function ($item, $key) {
            return [$item['id'] => $item['name']];
        });

        return response(['pollenStates' => $pollenStates, 'shopLanguages' => $shopLanguages], 200);
    }

    public function show(Request $request, PollenState $pollenState)
    {
        $pollenState->load(['pollenPageContent', 'pollenLanguage', 'pollenParent']);
        return response(['pollenState' => $pollenState], 200);
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
            'handle' => [Rule::unique('pollen_states')->where('user_id', $shop->id), 'required'],
            'pollen_page_content_id' => 'required_if:has_static_content,true',
            'parent_id' => array_filter([
                'sometimes',
                $parentId ? Rule::exists('pollen_states', 'id')->where('user_id', $shop->id) : null,
                $parentId ? Rule::prohibitedIf($defaultLanguageId === $languageId) : null
            ]),
            'latitude' => ['required', new LatitudeRule],
            'longitude' => ['required', new LongitudeRule],
        ],
        [
            'language_id.required' => 'Language field is required',
            'parent_id.prohibited' => 'Parent id cannot be added to default language'
        ]);

        $pollenState = DB::transaction(function () use ($request, $shop, $languageId) {
            return PollenState::create([
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

        return response(['pollenState' => $pollenState, 'message' => 'Pollen state saved successfully'], 200);
    }

    public function update(Request $request, PollenState $pollenState)
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
            'handle' => [Rule::unique('pollen_states')->where('user_id', $shop->id)->ignore($pollenState), 'required'],
            'pollen_page_content_id' => 'required_if:has_static_content,true',
            'parent_id' => array_filter([
                'sometimes',
                $parentId ? Rule::exists('pollen_states', 'id')->where('user_id', $shop->id) : null,
                $parentId ? Rule::prohibitedIf($defaultLanguageId === $languageId) : null
            ]),
        ],
        [
            'language_id.required' => 'Language field is required',
            'parent_id.prohibited' => 'Parent id cannot be added to default language'
        ]);

        $pollenState = DB::transaction(function () use ($request, $shop, $pollenState, $languageId) {
            $pollenState->update([
                'language_id' => $languageId,
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'latitude' => $request->input('latitude') ?: null,
                'longitude' => $request->input('longitude') ?: null,
                'has_static_content' => $request->input('has_static_content') ?: false,
                'pollen_page_content_id' => data_get($request, 'has_static_content') ? $request->input('pollen_page_content_id') : $pollenState->pollen_page_content_id,
                'parent_id' => $request->input('parent_id') ?: null,
                'is_popular' => $request->input('is_popular') ?: false,
                'variables' => $request->input('variables') ?: null,
            ]);

            if ($pollenState->wasChanged('language_id')) {
                $pollenState->pollenCities()->update(['pollen_state_id' => null]);
            }

            return $pollenState;
        });

        return response(['pollenState' => $pollenState, 'message' => 'Pollen state udpated successfully'], 200);
    }

    public function delete(PollenState $pollenState) {

        $pollenState->delete();
        return response(['message' => 'deleted successfully'], 200);
    }

    public function defaultStates(Request $request) {
        $shop = $request->user();
        if (empty($shop)) return;

        $pollenStateId = data_get($request, 'pollenStateId');
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        $defaultLanguageId = data_get($defaultLanguage, 'id');

        $defaultStates = PollenState::where('user_id', $shop->id)->where('language_id', $defaultLanguageId);
        if (!empty($pollenStateId)) {
            $defaultStates = $defaultStates->where('id', '!=', $pollenStateId);
        }

        $defaultStates = $defaultStates->get();
        return response(['defaultStates' => $defaultStates], 200);
    }
}
