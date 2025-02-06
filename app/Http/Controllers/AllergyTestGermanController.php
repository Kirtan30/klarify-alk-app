<?php

namespace App\Http\Controllers;

use App\Jobs\SendAggregateResultsGerman;
use App\Models\AllergyTestGerman;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AllergyTestGermanController extends Controller
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

        $allergyTestsGerman = AllergyTestGerman::where('user_id', $shop->id)->orderBy($sortBy, $sortOrder)->paginate($perPage);

        foreach ($allergyTestsGerman as $allergyTestGerman) {
            $allergyTestGerman->date = Carbon::parse($allergyTestGerman->created_at)->format('d M Y g:i A (T)');
        }

        return response(['allergyTestsGerman' => $allergyTestsGerman], 200);
    }
    public function download(Request $request, AllergyTestGerman $result) {

        $shop = $request->user();
        $domain = $shop->name;

        try {
            $html =  View::make('pdf.allergy-test-german-result', ['result' => $result, 'domain' => $domain]);
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            $dompdf->stream();
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
            ? AllergyTestGerman::where('user_id', $shop->id)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count()
            : AllergyTestGerman::where('user_id', $shop->id)->count();

        if ($count > 0) {
            $data = [
                'email' => $email,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            SendAggregateResultsGerman::dispatch($shop->id, $maxCount, $data);
            return response(['message' => 'Export process begins! You will receive a mail soon']);
        } else {
            return response(['message' => 'no records found']);
        }
    }
}
