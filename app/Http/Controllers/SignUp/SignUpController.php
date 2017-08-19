<?php

namespace App\Http\Controllers\SignUp;

use App\Http\Models\User;
use App\Http\Services\UsernameService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;
use Laravel\Socialite\Facades\Socialite;

class SignUpController extends Controller {


    /**
    * Handle a register request to the application.
    *
    * @param \Illuminate\Http\Request $pRequest
    *
    * @return \Illuminate\Http\Response
    */
    public function signUp(Request $pRequest) {
        try {
            $this->validate($pRequest, [
                'email' => 'required|email|unique:users|max:255|min:6',
                'password' => 'required',
            ]);
        } catch (ValidationException $pException) {
            return $pException->getResponse();
        }
<<<<<<< HEAD
        //  Register a new user on the database
        $pUser = new User($pRequest->all());
        $pUser->password = $pRequest->get('password');
        $pUser->remember_token = str_random(10);

        //  Generate username
        $pUser->username = UsernameService::generateUsername($pUser);

        if(!$pUser->save()){
=======
        try{
            //  Register a new user on the database
            $pUser = new User($pRequest->all());
            $pUser->remember_token = str_random(10);

            //  Generate username
            $pUser->username = UsernameService::generateUsername($pUser);

            $pUser->save();

            return $this->_onRegister();
        } catch (HttpException $pException) {
>>>>>>> b46847b5779e5cde20be068c153e813f4cd0f025
            return $this->_onCannotRegister();
        }
        
        return $this->_onRegister();
    }

    /**
    * What response should be returned when cannot register an user.
    *
    * @return JsonResponse
    */
    protected function _onCannotRegister() {
        return new JsonResponse([
            'message' => 'user_could_not_be_registered'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
    * What response should be returned when cannot register an user.
    *
    * @return JsonResponse
    */
    protected function _onRegister() {
        return new JsonResponse([
                'message' => 'user_registered',
            ]);
    }

}
