<?php

namespace App\Http\Controllers\Register\RegisterController;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;

class RegisterController extends Controller {

    /**
    * Handle a register request to the application.
    *
    * @param \Illuminate\Http\Request $request
    *
    * @return \Illuminate\Http\Response
    */

    public function postRegister(Request $request) {
        try {
            $this->validate($request, [
                'email' => 'required|email|max:255',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return $e->getResponse();
        }
        //  Register a new user on the database
        try {

        } catch(HttpResponseException $e) {

        }
    }
}
