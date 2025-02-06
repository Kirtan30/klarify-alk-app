<?php

namespace App\Http\Controllers;

use App\Models\Lexicon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LexiconController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');

        $lexicons = Lexicon::where('user_id', $shop->id);

        if (!empty($search)) {
            $lexicons = $lexicons->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('handle', 'like', "%$search%");
            });
        }
        $lexicons = $lexicons->paginate($perPage);

        return response(['lexicons' => $lexicons], 200);
    }

    public function show(Lexicon $lexicon)
    {
        return response(['lexicon' => $lexicon], 200);
    }

    public function store(Request $request) {
        $shop = $request->user();

        $request->validate([
            'name' => 'required',
            'handle' => [Rule::unique('pollen_cities')->where('user_id', $shop->id), 'required'],
        ]);

        try {
            $lexicon = DB::transaction(function () use ($shop, $request) {
                return Lexicon::create([
                    'user_id' => $shop->id,
                    'name' => $request->input('name'),
                    'handle' => $request->input('handle'),
                    'content' => $request->input('content'),
                    'date' => $request->input('date'),
                ]);
            });

            return response(['lexicon' => $lexicon, 'message' => 'Lexicon saved successfully'], 200);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Lexicon $lexicon) {
        $shop = $request->user();

        $request->validate([
            'name' => 'required',
            'handle' => [Rule::unique('lexicons')->where('user_id', $shop->id)->ignore($lexicon), 'required'],
        ]);

        $lexicon = DB::transaction(function () use ($shop, $request, $lexicon) {
            $lexicon->update([
                'name' => $request->input('name'),
                'handle' => $request->input('handle'),
                'content' => $request->input('content'),
                'date' => $request->input('date'),
            ]);

            return $lexicon;
        });

        return response(['lexicon' => $lexicon, 'Lexicon updated successfully'], 200);
    }

    public function delete(Lexicon $lexicon) {
        $lexicon->delete();
        return response(['message' => 'Lexicon deleted successfully'], 200);
    }

    public function sync(Request $request)
    {

        $shop = $request->user();

        $country = $shop->country;

        if (empty($country)) {
            return response(['message' => 'Please select country for this store'], 422);
        }

        $errors = [];

        foreach ($shop->languages as $language) {
            try {
                $namespace = "lexicon-$language->code";

                $lexicons = $shop->lexicons()->orderBy('name')->get()->groupBy(function ($item) {
                    return $item->name ? $item->name[0] : '';
                });
                $preparedLexicons = [];

                foreach ($lexicons as $key => $lexiconGroup) {
                    foreach ($lexiconGroup as $lexicon) {
                        $preparedLexicons[$key][] = [
                            'name' => data_get($lexicon, 'name'),
                            'handle' => data_get($lexicon, 'handle'),
                        ];
                    }
                }

                $data = [
                    [
                        'key' => 'lexicons',
                        'value' => json_encode($preparedLexicons),
                    ]
                ];

                foreach ($data as $datum) {
                    $metafieldData = [
                        "metafield" => [
                            "namespace" => data_get($datum, 'namespace') ?: $namespace,
                            "key" => data_get($datum, 'key'),
                            "value" => data_get($datum, 'value', []),
                            "type" => "json"
                        ]
                    ];

                    $response = $shop->api()->rest('POST', '/admin/metafields.json', $metafieldData);

                    if (!empty(data_get($response, 'errors'))) {
                        $errors[] = [
                            'message' => data_get($response, 'body'),
                            'status' => data_get($response, 'status') ?: 500,
                        ];
                    }
                }
            }
            catch (\Exception $e) {
                $errors[] = [
                    'message' => $e->getMessage(),
                    'status' =>  500,
                ];
                continue;
            }
        }

        $status = !empty($errors) ? data_get($errors, '0.status') : 200;
        $message = !empty($errors) ? data_get($errors, '0.message') : 'Metafields synced successfully';
        return response(['message' => $message, 'errors' => $errors], $status);
    }
}
