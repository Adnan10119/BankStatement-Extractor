<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use Hash;
use Auth;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;

class AuthController extends Controller
{
    public function signup(Request $request){
        $validator = Validator::make($request->all(), [
            'org_name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required|min:10|max:12|unique:users',
            'email' => 'required|unique:users',
            'add_line_1' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'password' => 'required',
        ], [
            'phone_number.unique' => 'The phone number belongs to an existing account',
            'email.unique' => 'The email belongs to an existing account'
        ]);
        if ($validator->fails()) {

            $response = [
                'success' => false,
                'message' => $validator->errors()->first(),
            ];

            return response()->json($response);
        }
        $address_line_2 = "";
        if(!empty($request->add_line_2)){
            $address_line_2 = $request->add_line_2;
        }
        $data = [
            'org_name' => $request->org_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'email' => strtolower($request->email),
            'add_line_1' => $request->add_line_1,
            'add_line_2' => $address_line_2,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
            'password' => Hash::make($request->password),
        ];

        $user = User::insert($data);
        if($user){
            $response = [
                'success' => true,
                'message' => 'User created successfully!',
            ];

            return response()->json($response);
        }
        else{
            $response = [
                'success' => false,
                'message' => 'Something went wrong please try again later!',
            ];

            return response()->json($response);
        }
    }

    public function login(Request $request){
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $data = Auth::user();
            $token = $data->createToken('MyApp');
            $accessToken = $token->accessToken;
            $token->token->expires_at = Carbon::now()->addWeeks(4);
            $id = $token->token->id;
            DB::table('oauth_access_tokens')->where('id', $id)->update(['expires_at' => $token->token->expires_at]);

            $data['access_token'] = $accessToken;

            return response()->json([ 'success' => true, 'message' => 'Login successfully!', 'data' => $data]);
        }
        else{
            return response()->json([ 'success' => false, 'message' => 'Invalid credentials! Please try again!']);
        }
    }
    public function lowerCaseEmails(){
        $users = User::get();
        foreach($users as $user){
            $user->email = strtolower($user->email);
            $user->update();
        }
        return response()->json([ 'success' => true, 'message' => 'Email lowercased']);
    }

    public function isSubscribed(Request $request){
        $validate = Validator::make($request->all(),[
            'email' => 'required',
		]);
		if($validate->fails()){
			return response()->json([
				'success' => false,
				'message' => $validate->errors()->all()[0]
			]);
		}
        $email = strtolower($request->email);
        $username = config('app.chargify_token');
        $url = 'https://efraud-services.chargify.com/customers.json?q='.$email;
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
                if($decodeResponse){
                    $customerId = $decodeResponse[0]["customer"]["id"];
                    $url2 = 'https://efraud-services.chargify.com/customers/'.$customerId.'/subscriptions.json';
                    try {
                        $response2 = $client->get($url2,
                            [
                                'headers' => [
                                    'Authorization' => 'Basic ' .$credentials,
                                ]
                            ]
                        );
                        if($response2->getStatusCode() == 200){
                            $decodeResponse2 = json_decode($response2->getBody(), true);
                            $subscriptionID = $decodeResponse2[0]['subscription']['id'];
                            $planName = $decodeResponse2[0]['subscription']['product']['name'];

                            $data = new Subscription();
                            $data->user_id = User::where('email',$email)->pluck('id')->first();
                            $data->plan_id = Plan::where('name',$planName)->pluck('id')->first();
                            $data->subscription_id = $subscriptionID;
                            $data->start_period = date('m/d/Y');
                            $data->end_period = date('m/d/Y', strtotime('+1 years'));
                            $data->save();

                            User::where('email',$email)->update(['is_subscribed'=>1]);
                            return response()->json([
                                'success' => true,
                                'message' => "Found"
                            ]);
                        }
                        if($response2->getStatusCode() == 422){
                            return response()->json([
                                'success' => false,
                                'message' => $response
                            ]);
                        }
                    }catch(\Exception $e){
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
                    
                }else{
                    return response()->json([
                        'success' => true,
                        'message' => "Not Found"
                    ]);
                }
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
}
