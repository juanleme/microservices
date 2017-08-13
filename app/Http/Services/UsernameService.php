<?php

namespace App\Http\Services;

use App\Http\Models\User;

class UsernameService {

    /**
    * Generate a unique username.
    *
    * @param \App\Http\Models $pUser
    *
    * @return String username
    */
    public static function generateUsername(User $pUser) {
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
