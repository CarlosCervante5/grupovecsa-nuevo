<?php

namespace App\Imports;

use App\Http\Requests\Strega\Opportunities\StoreOpportunityRequest;
use App\Services\OpportunityService;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class OpportunitiesImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    protected $opportunityService;
    protected $user_id;
    protected $failures = [];
    protected $rowIndex;


    public function __construct(OpportunityService $opportunityService, $user_id)
    {
        $this->opportunityService = $opportunityService;
        $this->user_id = $user_id;
        $this->rowIndex = 2;
    }

    public function model(array $row)
    {
        $request = new StoreOpportunityRequest();
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
            $this->rowIndex++;

            return null;
        }

        $this->opportunityService->createOrUpdateOpportunity($row, $this->user_id);
        $this->rowIndex++;


    }

    public function failures()
    {
        return collect($this->failures);
    }

    public function startRow(): int
    {
        return 2;
    }
}
