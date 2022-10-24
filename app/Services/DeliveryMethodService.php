<?php

namespace App\Services;

use App\Repositories\DeliveryMethodRepository;
use App\Models\DeliveryMethod;

class DeliveryMethodService extends DeliveryMethodRepository
{
    public function createDeliveryMethods($request, $lotId)
    {
        if(isset($request->faceToFace)) {
            $input['lot_id'] = $lotId;
            $input['code'] = 0;
            $input['cost'] = 0;
            DeliveryMethodRepository::create($input);
        }

        if(isset($request->homeDelivery)) {
            $input['lot_id'] = $lotId;
            $input['code'] = 1;
            $input['cost'] = $request->homeDeliveryCost;
            DeliveryMethodRepository::create($input);
        }

        if(isset($request->crossBorderDelivery)) {
            $input['lot_id'] = $lotId;
            $input['code'] = 2;
            $input['cost'] = $request->crossBorderDeliveryCost;
            DeliveryMethodRepository::create($input);
        }
    }

    public function syncDeliveryMethods($request, $lot)
    {
        if(isset($request->faceToFace)) {
            DeliveryMethod::updateOrCreate(
                ['lot_id'=>$lot->id, 'code'=>0], ['lot_id'=>$lot->id,'code'=>0, 'cost'=>0]
            );
        } else {
            if ($lot->face_to_face != null)
            {
                $lot->face_to_face->delete();
            }
        }

        if(isset($request->homeDelivery)) {
            DeliveryMethod::updateOrCreate(
                ['lot_id'=>$lot->id, 'code'=>1], ['lot_id'=>$lot->id,'code'=>1, 'cost'=>$request->homeDeliveryCost]
            );
        } else {
            if ($lot->home_delivery != null)
            {
                $lot->home_delivery->delete();
            }
        }

        if(isset($request->crossBorderDelivery)) {
            DeliveryMethod::updateOrCreate(
                ['lot_id'=>$lot->id, 'code'=>2], ['lot_id'=>$lot->id,'code'=>2, 'cost'=>$request->crossBorderDeliveryCost]
            );
        } else {
            if ($lot->cross_border_delivery != null)
            {
                $lot->cross_border_delivery->delete();
            }
        }


    }
}
