<?php

namespace App\Imports;

use App\Http\Requests\Vehicles\StoreVehicleRequest;
use App\Models\User;
use App\Services\VehicleService;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

class VehiclesImport implements SkipsOnFailure, ToModel, WithHeadingRow
{
    use SkipsFailures;

    protected $vehicleService;

    protected User $user;

    protected $failures = [];

    protected $rowIndex = 1;

    public function __construct(VehicleService $vehicleService, User $user)
    {
        $this->vehicleService = $vehicleService;
        $this->user = $user;
    }

    public function model(array $row)
    {
        $request = new StoreVehicleRequest;
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

        $this->vehicleService->createOrUpdateVehicle($row, $this->user);

        $this->rowIndex++;
    }

    public function failures()
    {
        return collect($this->failures);
    }
}
