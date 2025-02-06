<?php

namespace App\Http\Controllers;

use App\Models\FadPageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FadPageContentController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');

        $fadStaticContents = FadPageContent::where('user_id', $shop->id);

        if (!empty($search)) {
            $fadStaticContents = $fadStaticContents->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($perPage == -1) {
            $fadStaticContents = $fadStaticContents->get();
            $fadStaticContents = [
                'data' => $fadStaticContents,
                'total' => $fadStaticContents->count()
            ];
        } else {
            $fadStaticContents = $fadStaticContents->paginate($perPage);
        }

        return response(['fadStaticContents' => $fadStaticContents], 200);
    }

    public function show(FadPageContent $fadPageContent)
    {
        return response(['fadStaticContent' => $fadPageContent], 200);
    }

    public function store(Request $request)
    {
        $shop = $request->user();

        $request->validate([
            'name' => 'required|unique:fad_page_contents',
            'content' => 'required',
        ]);

        $fadStaticContent = DB::transaction(function () use ($request, $shop) {
            return FadPageContent::create([
                'user_id' => $shop->id,
                'name' => $request->input('name'),
                'content' => $request->input('content') ?: null,
                'variables' => collect($request->input('variables'))->pluck('value')->unique()->values()->toArray() ?: []
            ]);
        });

        return response(['fadStaticContent' => $fadStaticContent, 'message' => 'Fad Static Page saved successfully'], 200);
    }

    public function update(Request $request, FadPageContent $fadPageContent)
    {
        $shop = $request->user();

        $request->validate([
            'name' => "required|unique:fad_page_contents,name,$fadPageContent->id",
            'content' => 'required',
        ]);

        $fadPageContent = DB::transaction(function () use ($request, $shop, $fadPageContent) {
            $fadPageContent->update([
                'user_id' => $shop->id,
                'name' => $request->input('name'),
                'content' => $request->input('content') ?: null,
                'variables' => collect($request->input('variables'))->pluck('value')->unique()->values()->toArray() ?: []
            ]);

            return $fadPageContent;
        });

        return response(['fadStaticContent' => $fadPageContent, 'message' => 'Fad Static Page saved successfully'], 200);
    }

    public function delete(FadPageContent $fadPageContent) {
        $fadPageContent->delete();
        return response(['message' => 'deleted successfully'], 200);
    }
}
