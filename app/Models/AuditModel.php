<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditModel extends Model
{
    protected $table            = 'audit';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['image', 'category_id', 'prod_name', 'stock', 'price','unit_price','size_id','UPC','barcode_image','created_at', 'updated_at', 'product_description', 'type', 'old_stock','product_id'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules =[];
    protected $foreignKeys = [
        [
            'table' => 'productlist',
            'field' => 'product_id',
            'foreign_field' => 'id'
        ]
    ];
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
