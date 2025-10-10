<?php

namespace App\Imports;

use App\Http\Requests\Vehicles\StoreVehicleRequest;
use App\Services\VehicleService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class VehiclesImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    protected $vehicleService;
    protected $user_id;
    protected $failures = [];
    protected $rowIndex = 1;

    public function __construct(VehicleService $vehicleService, $user_id)
    {
        $this->vehicleService = $vehicleService;
        $this->user_id = $user_id;
    }

    public function model(array $row)
    {
        $request = new StoreVehicleRequest();
        $rules = $request->rules();

        $validator = Validator::make($row, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->toArray() as $attribute => $messages) {
                $this->failures[] = new Failure(
                    $this->rowIndex,
                    $attribute,
                    $messages,
                    $row
                );
            }

            return null;
        }

        $this->vehicleService->createOrUpdateVehicle($row, $this->user_id);

        $this->rowIndex++;
    }

    public function failures()
    {
        return collect($this->failures);
    }
}
