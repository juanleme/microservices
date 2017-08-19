<?php

namespace App\Http\Controllers\Auth;

use App\Http\Models\User;
use App\Http\Services\UsernameService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;
use Laravel\Socialite\Facades\Socialite;
use Validator;

class AuthController extends Controller {
    
    protected $_vecProviders;

    /**
    * Constructor gets env enabled providers.
    *
    * Available providers: @google | @facebook
    *
    */
    public function __construct() {
        $this->_vecProviders = explode(',', env('PROVIDERS'));
    }

    /**
    * Handle a login request to the application.
    *
    * @param \Illuminate\Http\Request $pRequest
    *
    * @return \Illuminate\Http\Response
    */
    public function postLogin(Request $pRequest) {
        try {
            $this->validate($pRequest, [
                'email' => 'required|email|max:255',
                'password' => 'required',
            ]);
        } catch (ValidationException $pException) {
            return $pException->getResponse();
        }

        try {
            // Attempt to verify the credentials and create a token for the user
            if (!$strToken = JWTAuth::attempt(
                $this->_getCredentials($pRequest)
            )) {
                return $this->_onUnauthorized();
            }
        } catch (JWTException $pException) {
            // Something went wrong whilst attempting to encode the token
            return $this->_onJwtGenerationError();
        }

        // All good so return the token
        return $this->_onAuthorized($strToken);
    }
    
    /**
    * Get the needed authorization credentials from the request.
    *
    * @param \Illuminate\Http\Request $pRequest
    *
    * @return array
    */
    protected function _getCredentials(Request $pRequest) {
        return $pRequest->only('email', 'password');
    }
    
    /**
    * What response should be returned on invalid credentials.
    *
    * @return JsonResponse
    */
    protected function _onUnauthorized() {
        return new JsonResponse([
            'message' => 'invalid_credentials'
        ], Response::HTTP_UNAUTHORIZED);
    }
    /**
    * Invalidate a token.
    *
    * @return \Illuminate\Http\Response
    */
    public function deleteInvalidate() {
        $strToken = JWTAuth::parseToken();

        $strToken->invalidate();

        return new JsonResponse(['message' => 'token_invalidated']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\Response
     */
    public function patchRefresh() {
        $strToken = JWTAuth::parseToken();

        $strNewToken = $strToken->refresh();

        return new JsonResponse([
                'message' => 'token_refreshed',
                'data' => [
                    'token' => $strNewToken
                ]
            ]);
    }

    /**
    * Get REDIRECTION URL to a thrid part OAtuh provider
    *
    *
    * @param String $strProvider
    *
    * @return \Illuminate\Http\Response
    */
    public function getProviderRedirectUrl($strProvider) {
        if(!in_array($strProvider, $this->_vecProviders)) {
            return $this->_onProviderNotAllowed();
        }

        // GET Url
        return Socialite::with($strProvider)
                ->stateless()->redirect()->getTargetUrl();
    }

    /**
    * Handle with the providers callback
    *
    * @param String $strProvider
    *
    * @return \Illuminate\Http\Response
    */
    public function handleCallbackProvider($strProvider) {
        $pProviderUser = Socialite::driver($strProvider)->stateless()->user();
        
        // Find user or create a new one using the Provider data 
        $vecProviderUser = $this->_findOrCreateUser($pProviderUser, $strProvider);
        
        if($vecProviderUser['status'] == 'error') {
            return $this->_onLocalErrorMessage($vecProviderUser['message']);
        }
        
        $pUser = $vecProviderUser['user'];
    
        try{
            if (!$strToken = JWTAuth::fromUser($pUser)) {
                return $this->_onUnauthorized();
            }
        } catch (JWTException $pException) {
            // Something went wrong whilst attempting to encode the token
            return $this->_onJwtGenerationError();
        }

        // All good so return the token
        return $this->_onAuthorized($strToken);

    }
    
    /**
    * If a user has registered before using social auth, return the user
    * else, create a new user object.
    *
    * @param $pProviderUser Socialite user object
    * @param $strProvider Social auth provider
    *
    * @return  User
    */
    protected function _findOrCreateUser($pProviderUser, $strProvider) {
        $pAuthUser = User::where('provider_id', $pProviderUser->id)->first();
                                    
        if ($pAuthUser) {
            // User changes the email on provider? We must change it as well
            if ($pAuthUser->email != $pProviderUser->email) {
                $pAuthUser->email = $pProviderUser->email;
                $pAuthUser->save();
            }
            return [
                'status' => 'success',
                'user' => $pAuthUser
            ];
        }
        
        $pValidator = Validator::make([
                'email' => $pProviderUser->email,
                'provider_id' => $pProviderUser->id
            ], [
                'email' => 'required|email|unique:users|max:255',
                'provider_id' => 'required',
            ]);
        
        if($pValidator->fails()) {
            return [
                'status' => 'error',
                'message' => $pValidator->messages()->first()
            ];
        }
        $vecName = explode(' ', $pProviderUser->name);

        if (count($vecName) > 1) {
            $strFirstname = array_shift($vecName);
            $strLastname = implode(' ', $vecName);
        } else {
            $strFirstname = $vecName[0];
            $strLastname = '&';
        }

        $pUser = new User([
            'firstname' => $strFirstname,
            'lastname'  => $strLastname,
            'email'     => $pProviderUser->email,
            'provider'  => $strProvider,
            'avatar'    => $pProviderUser->avatar,
            'gender'    => $pProviderUser->user['gender']
        ]);
        
        $pUser->remember_token = str_random(10);
        $pUser->provider_id = $pProviderUser->id;
        $pUser->username = UsernameService::generateUsername($pUser);

        if(!$pUser->save()) {
            return [
                'status' => 'error',
                'message' => 'user_provider_could_not_be_registered'
            ];
        }
        
        return [
            'status' => 'success',
            'user' => $pUser
        ];
    }
    
    /**
    * Could not register a new user.
    *
    * @return \Illuminate\Http\Response
    */
    protected function _onCannotRegister() {
        return new JsonResponse([
            'message' => 'user_provider_could_not_be_registered'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
    
    
    /**
    * Local error Message.
    *
    * @param $strMessage
    *
    * @return \Illuminate\Http\Response
    */
    protected function _onLocalErrorMessage($strMessage) {
        return new JsonResponse([
            'message' => $strMessage
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
    * Get authenticated user.
    *
    * @return \Illuminate\Http\Response
    */
    public function getUser() {
        return new JsonResponse([
                'message' => 'authenticated_user',
                'data' => JWTAuth::parseToken()->authenticate()
            ]);
    }

    /**
    * What response should be returned on authorized.
    *
    * @return JsonResponse
    */
    protected function _onAuthorized($strToken) {
        return new JsonResponse([
            'message' => 'token_generated',
            'data' => [
                'token' => $strToken,
            ]
        ]);
    }


    /**
    * What response should be returned on error while generate JWT.
    *
    * @return JsonResponse
    */
    protected function _onJwtGenerationError() {
        return new JsonResponse([
            'message' => 'could_not_create_token'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    
    /**
    * What response should be returned when provider is not allowed.
    *
    * @return JsonResponse
    */
    protected function _onProviderNotAllowed() {
        return new JsonResponse([
            'message' => 'provider_not_allowed'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }


}
