<?php

namespace App\Exports;

use App\Models\SelfTestResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SelfTestResultExport implements FromCollection, WithHeadings, WithMapping
{
    protected $result;

    public function __construct($result) {
        $this->result = $result;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->result;
    }

    public function map($result): array
    {
        return [
            $result->created_at,
            $result->score,
            $result->result,
            $result->answers
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Score',
            'Result',
            'Answers'
        ];
    }
}
