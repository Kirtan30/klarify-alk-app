<?php

namespace App\Jobs;

use App\Exports\AllergyTestResultExportGerman;
use App\Mail\AllergyTestResultsGermanExportMail;
use App\Models\AllergyTestGerman;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SendAggregateResultsGerman implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shopId;
    protected $data;
    protected $maxcount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shopId, $maxcount, $data)
    {
        $this->shopId = $shopId;
        $this->data = $data;
        $this->maxcount = $maxcount;
//        $this->startDate = $startDate;
//        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!Storage::disk('public')->exists('allergy_tests_german')) Storage::disk('public')->makeDirectory( '/exports/allergy_tests_german');

        $results = !empty($this->data['startDate']) && !empty($this->data['endDate'])
            ? AllergyTestGerman::where('user_id', $this->shopId)->whereDate('created_at', '>=', $this->data['startDate'])->whereDate('created_at', '<=', $this->data['endDate'])->get()
            : AllergyTestGerman::where('user_id', $this->shopId)->get();

        $filePaths = [];

        $date = Carbon::now()->timestamp;

        $headers = [];
        foreach ($results as $result) {
            $headers = array_merge($headers, array_filter(collect($result->answers ?: [])->pluck('name')->toArray(), 'trim'));
        }
        $headers = array_values(array_unique($headers));

        foreach ($results->chunk($this->maxcount) as $index => $result) {
            $newIndex = $index + 1;
            Excel::store(new AllergyTestResultExportGerman($result, $headers), "exports/allergy_tests_german/$date/result_$newIndex.xlsx", 'public');
            $filePaths[] = url("storage/exports/allergy_tests_german/$date/result_$newIndex.xlsx");
        }

        Mail::to($this->data['email'])->send(new AllergyTestResultsGermanExportMail($filePaths));

    }
}
