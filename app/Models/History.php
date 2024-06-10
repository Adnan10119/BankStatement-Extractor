<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','input_name','output_name','input_file_id','output_file_id','time_period','user_name','date',
            'case_number','notes','input_url','output_url','page_size','status','flag','share_with','share_type'];
}
