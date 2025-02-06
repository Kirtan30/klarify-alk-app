<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClinicWeekDayResource extends JsonResource
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
            'index' => $this->weekDay->index,
            'name' => $this->weekDay->name,
            'translations' => $this->weekDay->translations,
            'opening_hours' => new ClinicWeekDayOpeningHourCollection($this->whenLoaded('clinicWeekDayOpeningHours')),
        ];
    }
}
