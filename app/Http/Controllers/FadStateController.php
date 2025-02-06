<?php

namespace App\Http\Controllers;

use App\Models\FadState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FadStateController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');
        $enabled = data_get($request, 'enabled');
        $languageId = data_get($request, 'language');

        $fadStates = FadState::where('user_id', $shop->id);

        if (!empty($languageId)) {
            $fadStates =  $fadStates->where('language_id', $languageId);
        }

        if (!empty($search)) {
            $fadStates = $fadStates->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('handle', 'like', "%$search%");
            });
        }

        if (!empty($enabled)) {
            $fadStates = $fadStates->where('enabled', $enabled);
        }

        if ($perPage == -1) {
            $fadStates = $fadStates->get();
            $fadStates = [
                'data' => $fadStates,
                'total' => $fadStates->count()
            ];
        } else {
            $fadStates = $fadStates->paginate($perPage);
        }

        $shopLanguages = $shop->languages->mapWithKeys(function ($item, $key) {
            return [$item['id'] => $item['name']];
        });

        return response(['fadStates' => $fadStates, 'shopLanguages' => $shopLanguages], 200);
    }

    public function show(FadState $fadState)
    {
        $fadState->load('fadPageContent');
        return response(['fadState' => $fadState], 200);
    }

//    public function store(Request $request)
//    {
//        $shop = $request->user();
//
//        $request->validate([
//            'name' => 'required',
//            'handle' => 'required|unique:fad_states',
//        ]);
//
//        $fadState = DB::transaction(function () use ($shop, $request) {
//            return FadState::create([
//                'user_id' => $shop->id,
//                'name' => $request->input('name'),
//                'handle' => str($request->input('handle'))->slug(),
//            ]);
//        });
//
//        return response(['$fadState' => $fadState, 'message' => 'Fad state saved successfully'], 200);
//    }

    public function update(Request $request, FadState $fadState) {
        $shop = $request->user();
        $languageId = data_get($fadState, 'language_id');

        $request->validate([
            'name' => "required",
            'handle' => [
                'required',
                Rule::unique('fad_states')->where('user_id', $shop->id)->where('language_id', $languageId)->ignore($fadState)
            ],
            'fad_page_content_id' => 'required_if:has_static_content,true',
        ]);

        $fadState = DB::transaction(function () use ($shop, $request, $fadState) {
            $fadState->update([
                'user_id' => $shop->id,
                'has_static_content' => $request->input('has_static_content') ?: false,
                'is_popular' => $request->input('is_popular') ?: false,
                'fad_page_content_id' => data_get($request, 'has_static_content') ? $request->input('fad_page_content_id') : $fadState->fad_page_content_id,
                'enabled' => $request->input('enabled') ?: false,
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'variables' => $request->input('variables') ?: null,
            ]);

            return $fadState;
        });

        return response(['$fadState' => $fadState, 'message' => 'Fad state update successfully'], 200);
    }

//    public function delete(FadState $fadState) {
//        $fadState->delete();
//        return response(['message' => 'deleted successfully'], 200);
//    }
}
