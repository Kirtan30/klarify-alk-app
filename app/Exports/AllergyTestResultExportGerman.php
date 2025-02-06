<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AllergyTestResultExportGerman implements FromCollection,  WithHeadings, WithMapping
{
    protected $results;
    protected $headers;

    public function __construct($results, $headers) {
        $this->results = $results;
        $this->headers = $headers;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->results;
    }

    public function map($result): array
    {
        $data = [
            $result->created_at,
            $result->result_type,
        ];

        $newAnswer = collect($result->answers ?: []);
        foreach ($this->headers as $header) {
            $value = $newAnswer->where('name', $header)->whereNotNull('value')->first();
            $data[] =  !empty($value) ? data_get($value, 'value') : '';
        }

        return $data;
    }

    public function headings(): array
    {
        return array_merge([
            'Date',
            'Result Type'
        ], $this->headers);
    }
}
