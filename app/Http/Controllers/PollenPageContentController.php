<?php

namespace App\Http\Controllers;

use App\Models\PollenPageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollenPageContentController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');

        $pollenStaticContents = PollenPageContent::where('user_id', $shop->id);

        if (!empty($search)) {
            $pollenStaticContents = $pollenStaticContents->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($perPage == -1) {
            $pollenStaticContents = $pollenStaticContents->get();
            $pollenStaticContents = [
                'data' => $pollenStaticContents,
                'total' => $pollenStaticContents->count()
            ];
        } else {
            $pollenStaticContents = $pollenStaticContents->paginate($perPage);
        }

        return response(['pollenStaticContents' => $pollenStaticContents], 200);
    }

    public function show(PollenPageContent $pollenPageContent)
    {
        return response(['pollenStaticContent' => $pollenPageContent], 200);
    }

    public function store(Request $request)
    {
        $shop = $request->user();

        $request->validate([
            'name' => 'required|unique:pollen_page_contents',
            'content' => 'required',
        ]);

        $pollenStaticContent = DB::transaction(function () use ($request, $shop) {
            return PollenPageContent::create([
                'user_id' => $shop->id,
                'name' => $request->input('name'),
                'content' => $request->input('content') ?: null,
                'variables' => collect($request->input('variables'))->pluck('value')->unique()->values()->toArray() ?: []
            ]);
        });

        return response(['pollenStaticContent' => $pollenStaticContent, 'message' => 'Pollen Static Page saved successfully'], 200);
    }

    public function update(Request $request, PollenPageContent $pollenPageContent)
    {
        $shop = $request->user();

        $request->validate([
            'name' => "required|unique:pollen_page_contents,name,$pollenPageContent->id",
            'content' => 'required',
        ]);

        $pollenPageContent = DB::transaction(function () use ($request, $shop, $pollenPageContent) {
            $pollenPageContent->update([
                'user_id' => $shop->id,
                'name' => $request->input('name'),
                'content' => $request->input('content') ?: null,
                'variables' => collect($request->input('variables'))->pluck('value')->unique()->values()->toArray() ?: []
            ]);

            return $pollenPageContent;
        });

        return response(['pollenStaticContent' => $pollenPageContent, 'message' => 'Pollen Static Page saved successfully'], 200);
    }

    public function delete(PollenPageContent $pollenPageContent) {
        $pollenPageContent->delete();
        return response(['message' => 'deleted successfully'], 200);
    }
}
