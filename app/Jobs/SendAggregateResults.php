<?php

namespace App\Jobs;

use App\Exports\SelfTestResultExport;
use App\Mail\SelfTestResultsExportMail;
use App\Models\SelfTestResult;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SendAggregateResults implements ShouldQueue
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
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!Storage::disk('public')->exists('self_tests')) Storage::disk('public')->makeDirectory( '/exports/self_tests');

        $results = !empty($this->data['startDate']) && !empty($this->data['endDate'])
            ? SelfTestResult::where('user_id', $this->shopId)->whereDate('created_at', '>=', $this->data['startDate'])->whereDate('created_at', '<=', $this->data['endDate'])->get()
            : SelfTestResult::where('user_id', $this->shopId)->get();

        $filePaths = [];

        $date = Carbon::now()->timestamp;

        foreach ($results->chunk($this->maxcount) as $index => $result) {
            $newIndex = $index + 1;
            Excel::store(new SelfTestResultExport($result), "exports/self_tests/$date/result_$newIndex.xlsx", 'public');
            $filePaths[] = url("storage/exports/self_tests/$date/result_$newIndex.xlsx");
        }

        Mail::to($this->data['email'])->send(new SelfTestResultsExportMail($filePaths));

    }
}
