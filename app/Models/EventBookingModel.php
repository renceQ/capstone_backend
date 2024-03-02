<?php

namespace App\Models;

use CodeIgniter\Model;

class EventBookingModel extends Model
{
    protected $table            = 'eventbookings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['event_title', 'start_date', 'end_date', 'location', 'event_description', 'name', 'email', 'phone', 'service', 'status', 'reason', 'computedTotal', 'meeting_date', 'meeting_time', 'start_time', 'end_time','first_req', 'second_req', 'third_req', 'fourth_req', 'fifth_req', 'sixth_req','inputValue', 'secondInputValue', 'thirdInputValue', 'fourthInputValue', 'fifthInputValue',  'sixthInputValue'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
