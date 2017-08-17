<?php

namespace App\Http\Controllers\AuthProviders;

use App\Http\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exception\HttpResponseException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthProvidersController extends Controller {

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
        $pUser = Socialite::driver($strProvider)->stateless()->user();
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
