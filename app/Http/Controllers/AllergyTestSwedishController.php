<?php

namespace App\Http\Controllers;

use App\Jobs\SendAggregateResultsSwedish;
use App\Models\AllergyTestSwedish;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AllergyTestSwedishController extends Controller
{
    public function index(Request $request) {
        $shop = $request->user();

        $perPage = $request->query('perPage') ?: 10;
        $sortBy = $request->get('sortBy') ? $request->get('sortBy') : 'created_at';
        $sortOrder = $request->get('sortOrder');
        $sortOrder = $sortOrder === 'ascending' ? 'asc' : 'desc';

        if ($sortBy === 'date') {
            $sortBy = 'created_at';
        }

        $allergyTestsSwedish = AllergyTestSwedish::where('user_id', $shop->id)->orderBy($sortBy, $sortOrder)->paginate($perPage);

        foreach ($allergyTestsSwedish as $allergyTestSwedish) {
            $allergyTestSwedish->date = Carbon::parse($allergyTestSwedish->created_at)->format('d M Y g:i A (T)');
        }

        return response(['allergyTestsSwedish' => $allergyTestsSwedish], 200);
    }

    public function download(Request $request, $result) {

        $allergyResult = AllergyTestSwedish::where('uuid', $result)->first();
        if (!$allergyResult) {
            $allergyResult = AllergyTestSwedish::where('id', $result)->firstOrFail();
        }
        try {
            $shop = $request->user();
            $domain = $shop->name;

            $html =  View::make('pdf.allergy-test-swedish-result.index', ['result' => $allergyResult, 'domain' => $domain]);
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            $dompdf->stream('Allergy Test Result');
        } catch (\Exception $e) {

            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function exportCsv(Request $request) {

        $request->validate([
            'email' => 'required|email',
        ]);

        $shop = $request->user();

        if (empty($shop->id)) {
            return response(['message' => 'unauthenticated'],401);
        }

        $email = data_get($request, 'email');
        $startDate = data_get($request, 'startDate');
        $endDate = data_get($request, 'endDate');
        $maxCount = 50000;

        if (!empty($startDate) && !empty($endDate)) {
            $startDate = Carbon::parse($startDate)->format('y-m-d');
            $endDate = Carbon::parse($endDate)->format('y-m-d');
        }

        $count = !empty($startDate) && !empty($endDate)
            ? AllergyTestSwedish::where('user_id', $shop->id)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count()
            : AllergyTestSwedish::where('user_id', $shop->id)->count();

        if ($count > 0) {
            $data = [
                'email' => $email,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            SendAggregateResultsSwedish::dispatch($shop->id, $maxCount, $data);
            return response(['message' => 'Export process begins! You will receive a mail soon']);
        } else {
            return response(['message' => 'no records found']);
        }
    }
}
