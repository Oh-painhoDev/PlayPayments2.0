<?php 

namespace App\Helpers;

use App\Models\Acquirer;

class AcquirerHelper
{
    public static function active()
    {
        return Acquirer::where('active', 1)->first();
    }
}
