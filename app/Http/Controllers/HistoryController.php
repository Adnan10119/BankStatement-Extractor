<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\History;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function editHistoryRecord(Request $request){
        if(!isset($request->data['recordId'])){
            return response()->json([
                'success' => false,
                'message' => 'Record Id is required'
            ]);
        }
        if(!isset($request->data['input_name'])){
            return response()->json([
                'success' => false,
                'message' => 'Input Name is required'
            ]);
        }
        if(!isset($request->data['output_name'])){
            return response()->json([
                'success' => false,
                'message' => 'Output Name is required'
            ]);
        }
        if(!isset($request->data['t_start_date'])){
            return response()->json([
                'success' => false,
                'message' => 'Transcation start date is required'
            ]);
        }
        if(!isset($request->data['t_end_date'])){
            return response()->json([
                'success' => false,
                'message' => 'Transcation end date is required'
            ]);
        }
        $start = date('m/d/Y',strtotime($request->data['t_start_date']));
        $end = date('m/d/Y',strtotime($request->data['t_end_date']));
        $timePeriod = $start.' - '.$end;

        $id = $request->data['recordId'];
        $history = History::where('id',$id)->first();
        $data['input_name'] = $request->data['input_name'];
        $data['output_name'] = $request->data['output_name'];
        $data['time_period'] = $timePeriod;
        $data['user_name'] = $request->data['user_name'];
        $data['date'] = $request->data['date'].' '.date('H:i:s',strtotime($history->date));
        $data['case_number'] = $request->data['case_number'];
        $data['notes'] = $request->data['notes'];

        $history->update($data);

        return response()->json([
			'success' => true,
			'message' => 'History Record Successfully Updated'
		]);
    }

    public function editHistoryNotes(Request $request){
        $validate = Validator::make($request->all(),[
			'id' => 'required',
            'notes' => 'required',
		]);
		if($validate->fails()){
			return response()->json([
				'success' => false,
				'message' => $validate->errors()->all()[0]
			]);
		}
        $data['notes'] = $request->notes;
        $id = $request->id;
        History::where('id',$id)->update($data);
        return response()->json([
			'success' => true,
			'message' => 'Notes Successfully Updated'
		]);
    }

    public function shareHistoryWith(Request $request){
        $validate = Validator::make($request->all(),[
            'recordId' => 'required',
			'share_with' => 'required',
		]);
		if($validate->fails()){
			return response()->json([
				'success' => false,
				'message' => $validate->errors()->all()[0]
			]);
		}

        $shareWith = '';
        $temp = 1;

        if($request->share_with == 'me'){
            $id = Auth::user()->id;
            $shareWith = $id;
            $shareType = "me";
        }
        else if($request->share_with == 'all'){
            $shareWith = Auth::user()->org_name;
            $shareType = "all";
            // $ids = User::where('org_name',Auth::user()->org_name)->pluck('id');
            // foreach($ids as $id){
            //     if($temp == 1){
            //         $shareWith = $id;
            //         $temp++;
            //     }else{
            //         $shareWith = $shareWith.','.$id;
            //     }
            // }
        }
        else if($request->share_with == 'only'){
            $shareType = "only";
            if(!isset($request->userId)){
                return response()->json([
                    'success' => false,
                    'message' => 'Please select user'
                ]);
            }
            $shareWith = $request->userId;
        }
        $data['share_with'] = $shareWith;
        $data['share_type'] = $shareType;
        $id = $request->recordId;
        History::where('id',$id)->update($data);

        return response()->json([
			'success' => true,
			'message' => 'Share Status is Updated',
		]);
    }

    public function getUsersWithSameOrg(Request $request){

		$org_name = Auth::user()->org_name;
		$id = Auth::user()->id;
        $data = User::select('id','name')->where([['org_name',$org_name],['id', '!=' , $id]])->get();
        return response()->json([
			'success' => true,
			'data' => $data
		]);
    }

    public function searchUserforShare(Request $request){
        $org_name = Auth::user()->org_name;
		$id = Auth::user()->id;
        if(!empty($request->name)){
            $data = User::select('id','name')->where('id', '!=' , $id)->where('org_name',$org_name)->where('name','LIKE','%'.$request->name.'%')->get();
        }
        else{
            $data = User::select('id','name')->where([['org_name',$org_name],['id', '!=' , $id]])->get();
        }
        return response()->json([
			'success' => true,
			'data' => $data
		]);
    }

    public function getOrganization(){
        $organization = User::distinct('org_name')->get('org_name');
        return response()->json(['success' => true, 'data' => $organization]);
    }
}
