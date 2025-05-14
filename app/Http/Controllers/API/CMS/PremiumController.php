<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\StaticDataTrait;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PremiumController extends Controller
{
    use CommonTrait, StaticDataTrait;

    public function makePremium($id)
    {
        $school = School::find($this->convertSubClassId($id));

        if (!$school) {
            return $this->sendError('School not found.', [], 404);
        }

        $school->is_premium = true;
        $school->premium_expired_date = Carbon::now('Asia/Jakarta')->addMonth();
        $school->save();

        return $this->sendResponse($school, 'Berhasil menetapkan premium.');
    }

    public function revokePremium($id)
    {
        $school = School::find($this->convertSubClassId($id));

        if (!$school) {
            return $this->sendError('School not found.', [], 404);
        }

        $school->is_premium = false;
        $school->premium_expired_date = null;
        $school->save();

        return $this->sendResponse($school, 'Berhasil membatalkan premium.');
    }
}
