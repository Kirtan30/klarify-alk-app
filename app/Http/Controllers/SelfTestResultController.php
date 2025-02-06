<?php

namespace App\Http\Controllers;

use App\Jobs\SendAggregateResults;
use App\Models\SelfTestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Dompdf\Dompdf;

class SelfTestResultController extends Controller
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

        $results = SelfTestResult::where('user_id', $shop->id)->orderBy($sortBy, $sortOrder)->paginate($perPage);

        foreach ($results as $result) {
            $result->date = Carbon::parse($result->created_at)->format('d M Y g:i A (T)');
        }

        return response(['results' => $results], 200);
    }

    public function download(Request $request, SelfTestResult $result) {

        try {
            $answers = collect($result->answers)->pluck('value', 'name' )->toArray();
            $score = $result->score;
            $resultText =$result->result;

            $html =  View::make('pdf.self-test-result', ['answers' => $answers, 'score' => $score, 'results' => $resultText]);
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
            ? SelfTestResult::where('user_id', $shop->id)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->count()
            : SelfTestResult::where('user_id', $shop->id)->count();

        if ($count > 0) {
            $data = [
                'email' => $email,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            SendAggregateResults::dispatch($shop->id, $maxCount, $data);
            return response(['message' => 'Export process begins! You will receive a mail soon']);
        } else {
            return response(['message' => 'no records found']);
        }
    }
}
