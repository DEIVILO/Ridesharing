<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\LoginNeedsVerification;

class LoginController extends Controller
{
    public function submit (Request $request)
    {
        //apstiprinaat nummuru
        $request->validate([
            'phone' => 'required|numeric|min:8'
        ]);

        //izveidot user model
        $user = User::firstOrCreate([
            'phone' => $request->phone
        ]);

        if (!$user) {
            return response ()->json(['message'=> 'Couldnt process a user with that phone number.'], 401);
        }

        //aizsutit lietotajam one-time kodu
        $user -> notify(new LoginNeedsVerification());


        //return success message
        return response()->json(['message' => 'Text message notification sent.']);
    }

    public function verify(Request $request)
    {
        //validate request
        $request->validate([
            'phone' => 'required|numeric|min:8',
            'login_code' => 'required|numeric|between:111111, 999999'
        ]);

        //find the user
        $user = User::where('phone', $request->phone)
            ->where('login_code', $request->login_code)
            ->first();


        // is the code provided the same one as saved??
        // if so return an auth token
        if ($user) {
            $user->update ([
                'login_code'=> null
            ]);

            return $user->createToken($request->login_code)->plainTextToken;
        }

        // if not return back a messaage
        return response()->json(['message'=>'invalid verification code.'],401);
    }
}
