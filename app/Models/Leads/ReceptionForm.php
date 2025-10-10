<?php

namespace App\Models\Leads;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceptionForm extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = env('DB_TABLE_PREFIX', '') . 'reception_forms';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'hour',
        'salesAdvisor',
        'brand',
        'departureTime',
        'visitType',
        'visitFirsTime',
        'department',
        'howFindOut',
        'contactSub',
        'clientName',
        'clientAge',
        'clientPhone',
        'clientEmail',
        'preferredMedium',
        'model',
        'year',
        'version',
        'color',
        'accessories',
        'brandSecondOption',
        'modelSecondOption',
        'versionSecondOption',
        'colorSecondOption',
        'testDrive', 
        'receivedQuote',
        'FAndI',
        'leaveCarOnAccount',
        'customersCurrentCar',
        'hostes',
        'wasClientProfile',
        'wasApplicationTaken',
        'commentaryFandI',
        'financingType',
        'initialInvestment',
        'monthlyPayment',
        'termHowManyMonths',
        'commentaryFinancing',
        'segment',
        'idCRM',
        'honorific',
        'contactSalesplace',
        'saleType',
    ];

    protected $hidden = [
        'id',
        'updated_at',
        'deleted_at'
    ];
}
