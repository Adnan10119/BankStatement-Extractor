<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MakeXLSX extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','index','account_name','account_number','account_type','ck','date'
    ,'description','debit','credit','value','balance','statement_balance','difference'];
}
