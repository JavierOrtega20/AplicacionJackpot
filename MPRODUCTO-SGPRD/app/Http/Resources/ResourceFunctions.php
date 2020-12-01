<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Carbon\Carbon;


class ResourceFunctions extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }


     public static function getDates($dates_string)
    {


        dd($dates_string);
        $var = explode(' - ', $dates_string);        

        $start_date_separated = explode('/', $var[0]);
        $due_date_separated = explode('/', $var[1]);

        dd();

  return array(
         Carbon::create($start_date_separated[2], $start_date_separated[1], $start_date_separated[0], 00, 00, 00, 'A'),
         Carbon::create($due_date_separated[2], $due_date_separated[1], $due_date_separated[0], 23, 59, 59, 'P')

     );
    }
}
