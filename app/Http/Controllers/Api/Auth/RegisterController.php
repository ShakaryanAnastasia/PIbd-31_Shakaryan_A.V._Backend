<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Auth\RegisterFormRequest;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{
    //public function __invoke(RegisterFormRequest $request)
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'required' => 'Обязательное поле',
        ]);

        if ($validator->passes()) {
            $user = User::create(array_merge(
                $request->only('name', 'email'),
                ['password' => bcrypt($request->password)]
            ));

            $credentials = $request->only('email', 'password');

            Auth::attempt($credentials);

            $token = Auth::user()->createToken(config('app.name'));
            $token->token->expires_at = $request->remember_me ?
                Carbon::now()->addMonth() :
                Carbon::now()->addDay();

            $token->token->save();
            $status = '200';
            $message = 'You were successfully registered and authorized.';
            $list = [
                'token_type' => 'Bearer',
                'token' => $token->accessToken,
                'expires_at' => Carbon::parse($token->token->expires_at)->toDateTimeString()
            ];
        } else {
            $status = '422';
            $message = $validator->errors();
            $list = null;
        }

        $data = compact('message', 'list', 'status');
        return response()->json($data);
    }
}
