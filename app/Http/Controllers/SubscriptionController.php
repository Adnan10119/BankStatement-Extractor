<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function addSubscriber(Request $request){
        $validate = Validator::make($request->all(),[
            'plan_id' => 'required',
            'token' => 'required',
            'email' => 'required',
		]);
		if($validate->fails()){
			return response()->json([
				'success' => false,
				'message' => $validate->errors()->all()[0]
			]);
		}
        $username = config('app.chargify_token');
        $url = 'https://efraud-services.chargify.com/subscriptions.json';
        $user = User::where('email',$request->email)->first();
        $planHandle = Plan::where('id',$request->plan_id)->pluck('product_handle')->first();

        $customerAttributes['first_name'] = $user->first_name;
        $customerAttributes['last_name'] = $user->last_name;
        $customerAttributes['email'] = $user->email;
        $customerAttributes['zip'] = $user->zip_code;
        $customerAttributes['state'] = $user->state;
        $customerAttributes['phone'] = $user->phone_number;
        $customerAttributes['organization'] = $user->org_name;
        $customerAttributes['city'] = $user->city;
        $customerAttributes['address'] = $user->add_line_1;

        $creditCardAttributes['chargify_token'] = $request->token;
        $creditCardAttributes['payment_type'] = 'credit_card';

        $client = new Client();
        $credentials = base64_encode($username.':x');
        try {
            $response = $client->post($url,
                [
                    'headers' => [
                        'Authorization' => 'Basic ' .$credentials,
                    ],
                    'json' => [
                        'subscription'=>[
                            'product_handle'=>$planHandle,
                            "customer_attributes"=>$customerAttributes,
                            "credit_card_attributes"=>$creditCardAttributes
                        ]
                    ]
                ]
            );

            if($response->getStatusCode() == 201){
                $decodeResponse = json_decode($response->getBody(), true);
                $subscriptionID = $decodeResponse['subscription']['id'];

                $data = new Subscription();
                $data->user_id = $user->id;
                $data->plan_id = $request->plan_id;
                $data->subscription_id = $subscriptionID;
                $data->start_period = date('m/d/Y');
                $data->end_period = date('m/d/Y', strtotime('+1 years'));
                $data->save();

                User::where('id',$user->id)->update(['is_subscribed'=>1]);

                return response()->json([
                    'success' => true,
                    'message' => 'Customer Subscribed Succesfully'
                ]);
            }
            if($response->getStatusCode() == 422){
                return response()->json([
                    'success' => false,
                    'message' => $response
                ]);
            }


        } catch (\Exception $e) {
            $data = $e->getMessage();
            if (str_contains($data, '{"errors":["')) { 
                $data = explode('{"errors":["',$data);
                $data = $data[1];
                $data = explode('.',$data);
                $data = $data[0];
                return response()->json([
                    'success' => false,
                    'message' => $data
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $data
            ]);
        }
        

    }

    public function pageRequest(Request $request){
        $validate = Validator::make($request->all(),[
            'pages' => 'required'
		]);
		if($validate->fails()){
			return response()->json([
				'success' => false,
				'message' => $validate->errors()->all()[0]
			]);
		}
        $pages = $request->pages;
        $userId = Auth::user()->id;
        $subscriptionDetail = Subscription::where('user_id',$userId)->first();
        $plan = Plan::where('id',$subscriptionDetail['plan_id'])->first();
        if($plan['name'] == 'Pay As You Go'){
            $username = config('app.chargify_token');
            $url = 'https://efraud-services.chargify.com/subscriptions/'.$subscriptionDetail['subscription_id'].'/components'.'/'.$plan['component_id'].'/allocations.json';
            $client = new Client();
            $credentials = base64_encode($username.':x');
            try {
                $response = $client->post($url,
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' .$credentials,
                        ],
                        'json' => [
                            'allocation'=>[
                                'quantity'=>$pages,
                                "memo"=>'Page Processing'
                            ]
                        ]
                    ]
                );
                if($response->getStatusCode() == 201){
                    $transcationData = new SubscriptionTransaction();
                    $transcationData->subscription_id = $subscriptionDetail['id'];
                    $transcationData->pages = $pages;
                    $transcationData->save();
                    return response()->json([
                        'success' => true,
                        'message' => 'Pages Added to Chargify'
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'message' => $response
                    ]);
                }
            }
            catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

        if($plan['name'] == 'Month to Month'){
            $result = $this->checkPages($userId);
            if(!$result){
                return response()->json([
                    'success' => false,
                    'message' => 'More than 5000 pages processed within year',
                ]);
            }
            
            $username = config('app.chargify_token');
            $url = 'https://efraud-services.chargify.com/subscriptions/'.$subscriptionDetail['subscription_id'].'/components'.'/'.$plan['component_id'].'/usages.json';
            $client = new Client();
            $credentials = base64_encode($username.':x');
            try {
                $response = $client->post($url,
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' .$credentials,
                        ],
                        'json' => [
                            'usage'=>[
                                'quantity'=>$pages,
                                "price_point_id"=>$plan['price_point_id'],
                                "memo"=>'Page Processing'
                            ]
                        ]
                    ]
                );
                if($response->getStatusCode() == 200){
                    $transcationData = new SubscriptionTransaction();
                    $transcationData->subscription_id = $subscriptionDetail['id'];
                    $transcationData->pages = $pages;
                    $transcationData->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Usage Added to Chargify'
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'message' => $response
                    ]);
                }
            }
            catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

        if($plan['name'] == 'Prepay Annual'){
            if($subscriptionDetail['status'] == null){
                return response()->json([
                    'success' => true,
                    'message' => 'Free Trial'
                ]);
            }
            $username = config('app.chargify_token');
            $url = 'https://efraud-services.chargify.com/subscriptions/'.$subscriptionDetail['subscription_id'].'/components'.'/'.$plan['component_id'].'/usages.json';
            $client = new Client();
            $credentials = base64_encode($username.':x');
            try {
                $response = $client->post($url,
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' .$credentials,
                        ],
                        'json' => [
                            'usage'=>[
                                'quantity'=>$pages,
                                "price_point_id"=>$plan['price_point_id'],
                                "memo"=>'Page Processing'
                            ]
                        ]
                    ]
                );
                if($response->getStatusCode() == 200){
                    $transcationData = new SubscriptionTransaction();
                    $transcationData->subscription_id = $subscriptionDetail['id'];
                    $transcationData->pages = $pages;
                    $transcationData->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Usage Added to Chargify'
                    ]);
                }
                else{
                    return response()->json([
                        'success' => false,
                        'message' => $response
                    ]);
                }
            }
            catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    public function checkPages($userId){
        $subscriptionDetail = Subscription::where('user_id',$userId)->first();
        $currentDate = date('Y-m-d');
        if($currentDate>date('Y-m-d', strtotime($subscriptionDetail['end_period']))){
            // return response()->json([
            //     'success' => false,
            //     'message' => $subscriptionDetail['end_period']
            // ]);
            $newDate = date('m/d/Y', strtotime($subscriptionDetail['end_period'].'+1 years'));
            Subscription::where('user_id',$userId)->update(array('start_period'=>$subscriptionDetail['end_period'],'end_period'=>$newDate));
            $pageProcessed = SubscriptionTransaction::whereBetween('created_at', [date('Y-m-d', strtotime($subscriptionDetail['end_period'])), date('Y-m-d', strtotime($newDate))])->where('subscription_id',$subscriptionDetail['id'])->sum('pages');
            if($pageProcessed<5000){
                return true;
            }
            else{
                return false;
            }
            
        }else{
            //$pageProcessed = SubscriptionTransaction::whereBetween('created_at', [date('2020-08-05'), date('2023-08-05')])->sum('pages');
            $pageProcessed = SubscriptionTransaction::whereBetween('created_at', [date('Y-m-d', strtotime($subscriptionDetail['start_period'])), date('Y-m-d', strtotime($subscriptionDetail['end_period']))])->where('subscription_id',$subscriptionDetail['id'])->sum('pages');
            if($pageProcessed<5000){
                return true;
            }
            else{
                return false;
            }
        }
    }

    public function getUsage(){
        $subscription = Subscription::where('user_id',Auth::id())->first();
        if($subscription){
            $componentId = Plan::where('id',$subscription['plan_id'])->pluck('component_id')->first();
            $username = config('app.chargify_token');

            $url = 'https://efraud-services.chargify.com/subscriptions/'.$subscription['subscription_id'].'.json';
            $client = new Client();
            $credentials = base64_encode($username.':x');
            $nextBillingDate = '';
            $planId = Plan::where('id',$subscription['plan_id'])->pluck('id')->first();
            $pricing = 0;
            if($planId == 1){
                $pricing = 0.80;
            }elseif($planId == 2){
                $pricing = 0.72;
            }elseif($planId == 3){
                $pricing = 0.72;
            }
            try {
                $response = $client->get($url,
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' .$credentials,
                        ]
                    ]
                );
                if($response->getStatusCode() == 200){
                    $decodeResponse = json_decode($response->getBody(), true);
                    $nextBillingDate = $decodeResponse['subscription']['current_period_ends_at'];
                }
                if($response->getStatusCode() == 422){
                    return response()->json([
                        'success' => false,
                        'message' => $response
                    ]);
                }
            }
            catch (\Exception $e) {
                $data = $e->getMessage();
                if (str_contains($data, '{"errors":["')) { 
                    $data = explode('{"errors":["',$data);
                    $data = $data[1];
                    $data = explode('.',$data);
                    $data = $data[0];
                    return response()->json([
                        'success' => false,
                        'message' => $data
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => $data
                ]);
            }
            if($planId == 1){
                $usage = SubscriptionTransaction::where('subscription_id',$subscription['id'])->get();
                $todayUsage = 0;
                $monthUsage = 0;
                $yearUsage = 0;
                foreach($usage as $item){
                    if(date('d',strtotime($item['created_at'])) == date('d')){
                        $todayUsage = $todayUsage + $item['pages'];
                    }
                    if(date('m',strtotime($item['created_at'])) == date('m')){
                        $monthUsage = $monthUsage + $item['pages'];
                    }
                    if(date('Y',strtotime($item['created_at'])) == date('Y')){
                        $yearUsage = $yearUsage + $item['pages'];
                    }
                }
                $data['today'] = $todayUsage;
                $data['month_usage'] = $monthUsage;
                $data['year_usage'] = $yearUsage;
                $data['today_spent'] = round($todayUsage*$pricing,2);
                $data['month_spent'] = round($monthUsage*$pricing,2);
                $data['year_spent'] = round($yearUsage*$pricing,2);
                $data['next_billing_date'] = $nextBillingDate;
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            }
            

            $url = 'https://efraud-services.chargify.com/subscriptions/'.$subscription['subscription_id'].'/'.'components/'.$componentId.'/usages.json';
            $client = new Client();
            $credentials = base64_encode($username.':x');
            try {
                $response = $client->get($url,
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' .$credentials,
                        ]
                    ]
                );
                if($response->getStatusCode() == 200){
                    $decodeResponse = json_decode($response->getBody(), true);
                    $todayUsage = 0;
                    $monthUsage = 0;
                    $yearUsage = 0;
                    if($decodeResponse){
                        foreach($decodeResponse as $item){
                            if(date('d',strtotime($item['usage']['created_at'])) == date('d')){
                                $todayUsage = $todayUsage + $item['usage']['quantity'];
                            }
                            if(date('m',strtotime($item['usage']['created_at'])) == date('m')){
                                $monthUsage = $monthUsage + $item['usage']['quantity'];
                            }
                            if(date('Y',strtotime($item['usage']['created_at'])) == date('Y')){
                                $yearUsage = $yearUsage + $item['usage']['quantity'];
                            }
                        }
                    }
                    $data['today'] = $todayUsage;
                    $data['month_usage'] = $monthUsage;
                    $data['year_usage'] = $yearUsage;
                    $data['today_spent'] = round($todayUsage*$pricing,2);
                    $data['month_spent'] = round($monthUsage*$pricing,2);
                    $data['year_spent'] = round($yearUsage*$pricing,2);
                    $data['next_billing_date'] = $nextBillingDate;
                    return response()->json([
                        'success' => true,
                        'data' => $data
                    ]);
                }
                if($response->getStatusCode() == 422){
                    return response()->json([
                        'success' => false,
                        'message' => $response
                    ]);
                }
            }
            catch (\Exception $e) {
                $data = $e->getMessage();
                if (str_contains($data, '{"errors":["')) { 
                    $data = explode('{"errors":["',$data);
                    $data = $data[1];
                    $data = explode('.',$data);
                    $data = $data[0];
                    return response()->json([
                        'success' => false,
                        'message' => $data
                    ]);
                }
                return response()->json([
                    'success' => false,
                    'message' => $data
                ]);
            }
        }
        else{
            return response()->json([
                'success' => false,
                'message' => "Not Subscribed"
            ]);
        }

    }
}
