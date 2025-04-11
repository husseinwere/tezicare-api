<?php

namespace App\Services;

use App\Models\User;

class HospitalService
{
    public function getHospitalIdByUser($user_id)
    {
        $user = User::find($user_id);

        if ($user) {
            return $user->hospital_id;
        }

        return null;
    }
}