<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClinicWeekDayOpeningHourResource extends JsonResource
{

//    public static $wrap = 'clinic';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'opening_second' => $this->opening_second,
            'opening_time' => $this->opening_time,
            'closing_second' => $this->closing_second,
            'closing_time' => $this->closing_time,
            'optional' => $this->optional,
        ];
    }
}
