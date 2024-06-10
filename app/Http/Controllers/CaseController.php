<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CaseNumber;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CaseController extends Controller
{
    public function getUserCaseNumbers(Request $request){

        $org_name = Auth::user()->org_name;

        $data = CaseNumber::where('org_name',$org_name)->get();

        return response()->json([
			'success' => true,
			'data' => $data
		]);
    }

}
