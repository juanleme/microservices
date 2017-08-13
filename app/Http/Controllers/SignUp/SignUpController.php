<?php

namespace App\Http\Controllers\SignUp;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;

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
            $result = $this->validate($pRequest, [
                'email' => 'required|email|unique:users|max:255|min:6',
                'password' => 'required',
            ]);
        } catch (ValidationException $pException) {
            return $pException->getResponse();
        }
        try{
            //  Register a new user on the database
            $pUser = new User();

            // Request params
            $pUser->firstname = $pRequest->get('firstname');
            $pUser->lastname = $pRequest->get('lastname');
            $pUser->email = $pRequest->get('email');
            $pUser->password = $pRequest->get('password');

            $pUser->remember_token = str_random(10);

            //  Generate username
            $pUser->username = $this->_generateUsername($pUser);

            $pUser->save();

            return new JsonResponse(['message' => 'user_registered']);

        } catch (HttpException $pException) {

            throw new HttpException(422, 'could_not_register_user');

        }
    }

    /**
    * Generate a unique username.
    *
    * @param \App\User $pUser
    *
    * @return String username
    */
    protected function _generateUsername(User $pUser) {
        $strUsername = strtolower(
            str_replace(' ', '',
            $pUser->firstname . $pUser->lastname)
        );

        $nUserRows = User::where(
            'username', 'regexp', $strUsername.'.*?[0-9]*')
            ->count();
        if($nUserRows > 0) {
            return $strUsername . ($nUserRows + 1);
        }
        return $strUsername;
    }
}
