<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseNumber extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','case_number','org_name'];
}
