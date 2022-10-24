<?php

namespace App\Services;

use App\Repositories\SpecificationRepository;

class SpecificationService extends SpecificationRepository
{
    public function createSpecifications($request, $lotId)
    {
        $titles = $request->specificationTitles;
        $values = $request->specificationValues;
        $specifications = array_combine($titles, $values);
        foreach($specifications as $title => $value) {
            $input['lot_id'] = $lotId;
            $input['title'] = $title;
            $input['value'] = $value;
            SpecificationRepository::create($input);
        }
    }

    public function updateSpecifications($request, $lotId)
    {
        foreach($request->specificationValues as $specificationId => $specificationValue)
        {
            SpecificationRepository::find($specificationId)->update(['value'=>$specificationValue]);
        }
    }

    public function deleteSpecifications($request, $lotId)
    {

    }
}
