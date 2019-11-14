<?php

namespace App\Http\Controllers\Auth;


use App\User as User;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialController extends Controller
{
    /**
     * Redirect to provider for authentication
     *
     * @param $driver
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider($driver)
    {
        if( ! (config()->has("services.{$driver}" ))){
            return response()->json([
                'message' => "Драйвер {$driver} не поддерживается",
            ]);
        }
        try {
            $data = Socialite::with($driver)->redirect()->getTargetUrl();
            return response()->json($data);
            //return Socialite::driver($driver)->redirect();
        } catch (Exception $e){
            return response()->json([
                'message' => "Ошибка входа",
                'error' => $e->getMessage()
            ]);
        }
    }
    /**
     * Login social user
     *
     * @param string $driver
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
        return $this->login($socialUser, $provider);
    }
    /**
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login($user, $provider)
    {
        $authUser = User::where(['social_id' => $user->id])->first();
        if ($authUser) {
            $authUser->update([
                'name' => $user->name,
                'email' => $user->email,
                'social_id' => $user->id,
            ]);
        } else {
            $authUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'social_id' => $user->id,
            ]);
        }
        try {
            $token = $authUser->createToken(config('app.name'))->accessToken;
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ]);
        }
        $status = '200';
        $list = $this->returnAccessData($token, $authUser);
        $data = compact('list', 'status');
       session(['data' => $data]);
        //return redirect()->action('App\Http\Controllers\Auth\SocialController@getAuth');
        //return response()->json($data);
       // return redirect('http://localhost:4200/info')->with($data);
        return redirect('https://iphotelfrontend.herokuapp.com/info')->with($data);
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnAccessData($token, $user)
    {
        $data = [
            'token' => $token,
            'token_type' => 'bearer',
            'user_name' => $user->name,
        ];
        return $data;
    }
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
        auth('api')->logout();
        return response()->json([
            'message' => 'You are successfully logged out',
        ]);
    }
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getAuth()
    {
        $data = session('data');
        session()->flush();
        return response()->json($data);
    }
}
