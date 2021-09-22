<?php

namespace App\Http\Controllers;

use App\Models\PlayerProgress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlayerProgressController extends Controller
{

    public function postPlayerProgress(Request $request) {

        $pp_get = PlayerProgress::where([
            ['uid', '=', $request->input('uid')],
            ['vid', '=', $request->input('vid')],
        ])->get();

        if($pp_get->count() == 0) {
            $pp = PlayerProgress::create($request->all());
        }
        else {
            $pp = $pp_get[0];
            $pp->percent = $request->input('percent');
            $pp->save();
        }

        return response()->json($pp);

    }

    public function getPlayerProgressByUidAndVid(Request $request, $uid, $vid){
 
        $pps = PlayerProgress::where([
            ['uid', '=', $uid],
            ['vid', '=', $vid],
        ])->get();

        if($pps->count() < 1)
            return response()->json('', 404);

        return response()->json(['percent'=>$pps[0]->percent]);
 
    }

    // Devuelve true si está visto, false si no
    public function getPlayerVideoStatusByUidAndVid(Request $request, $uid, $vid){
        
        $pps = PlayerProgress::where([
            ['uid', '=', $uid],
            ['vid', '=', $vid],
        ])->get();

        if($pps->count() < 1)
            return response()->json(['viewed' => false]);
        else
            return response()->json(['viewed' => true]);

    }

    public function getPlayerProgressVideosByUid(Request $request, $uid){
        
        $pps = PlayerProgress::where([
            ['uid', '=', $uid],
        ])->orderBy('updated_at', 'DESC')->get();

        if($pps->count() < 1)
            return response()->json(['error' => 'no items'], 204);

        return response()->json(['videos' => $pps, 'count'=>$pps->count()]);

    }
}
