<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\TokenBooking;
use Carbon\Carbon;
use App\Models\Symtoms;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GetTokenController extends BaseController
{




    // public function getCurrentDateTokens($clinic_id)
    // {



    //     // Get the currently authenticated user
    //     $user = Auth::user();

    //     if (!$user) {
    //         // User is not authenticated
    //         return response()->json(['message' => 'User not authenticated.', 'tokens' => null], 401);
    //     }

    //     // Get the current date
    //     $currentDate = now()->toDateString();

    //     // Retrieve tokens for the current date and the logged-in user
    //     $tokens = TokenBooking::where('doctor_id', $user->id)
    //         ->where('clinic_id', $clinic_id)
    //         ->whereDate('date', $currentDate)
    //         ->orderByRaw('CAST(TokenNumber AS SIGNED) ASC')
    //         ->get();

    //     if ($tokens->isEmpty()) {
    //         // No tokens found for the current date and the logged-in user
    //         return response()->json(['message' => 'No tokens available for the current date.', 'tokens' => null], 200);
    //     }

    //     $filteredTokens = $tokens->map(function ($token) {
    //         return [
    //             'id' => $token->id,
    //             'TokenNumber' => $token->TokenNumber,
    //             'TokenTime' => $token->TokenTime,
    //         ];
    //     });

    //     // Return the tokens as JSON
    //     return response()->json(['message' => 'Current user tokens retrieved successfully.', 'tokens' => $filteredTokens], 200);
    // }
    public function getCurrentDateTokens(Request $request)
    {

        $rules = [
            'clinic_id' => 'required',
            'Is_checkIn' => 'sometimes',
            'Is_completed' => 'sometimes',
            'TokenNumber' => 'sometimes',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['status' => false, 'response' => $validation->errors()->first()]);
        }
        try {
            // Get the currently authenticated user
            $user = Auth::user();

            if (!$user) {
                // User is not authenticated
                return response()->json(['message' => 'User not authenticated.', 'tokens' => null], 401);
            }

            // Get the current date
            $currentDate = now()->toDateString();

            // Retrieve tokens for the current date and the logged-in user
            $tokens = TokenBooking::where('doctor_id', $user->id)
                ->where('clinic_id', $request->clinic_id)
                ->whereDate('date', $currentDate)

                ->orderByRaw('CAST(TokenNumber AS SIGNED) ASC')
                ->get();

            if ($tokens->isEmpty()) {
                // No tokens found for the current date and the logged-in user
                return response()->json(['message' => 'No tokens available for the current date.', 'tokens' => null], 200);
            }


            $updatedTokens = [];

            foreach ($tokens as $appointment) {
                $tokenBooking = TokenBooking::find($appointment->id);

                if ($request->Is_checkIn) {
                    $tokenBooking->Is_checkIn = $request->Is_checkIn;
                    $tokenBooking->checkinTime = now();
                }

                if ($request->Is_completed) {
                    $tokenBooking->Is_completed = $request->Is_completed;
                    $tokenBooking->checkoutTime = now();
                }

                $tokenBooking->save();

                // Add the updated token details to the response
                $updatedTokens[] = $tokenBooking;
                $symptoms = json_decode($tokenBooking->Appoinmentfor_id, true);
                $tokenBooking['main_symptoms'] = Symtoms::select('id', 'symtoms')->whereIn('id', $symptoms['Appoinmentfor1'])->get()->toArray();
                $tokenBooking['other_symptoms'] = Symtoms::select('id', 'symtoms')->whereIn('id', $symptoms['Appoinmentfor2'])->get()->toArray();
            }


            // Return the array of updated tokens as JSON
            return response()->json(['message' => 'Current user tokens retrieved successfully.', 'tokens' => $updatedTokens], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getTokensForCheckInAndComplete(Request $request)
    {
        $rules = [
            'TokenNumber' => 'required',
            'Is_checkIn' => 'sometimes',
            'Is_completed' => 'sometimes',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['status' => false, 'response' => $validation->errors()->first()]);
        }

        try {


            // Get current date
            $currentDate = Carbon::now()->toDateString();
            $tokenNumber = $request->TokenNumber;

            // Fetch appointments for the current date and the logged-in doctor
            $appointments = DB::table('token_booking')
                ->whereDate('date', $currentDate)
                ->where('TokenNumber', $tokenNumber)
                ->get();

            if ($appointments->isEmpty()) {
                return response()->json(['message' => 'No appointments for the current date.'], 200);
            }



            $checkinCompleted = false;
            $checkoutCompleted = false;

            foreach ($appointments as $appointment) {
                $tokenBooking = TokenBooking::find($appointment->id);

                if ($request->Is_checkIn && !$tokenBooking->Is_checkIn) {
                    $tokenBooking->Is_checkIn = $request->Is_checkIn;
                    $tokenBooking->checkinTime = now();
                    $checkinCompleted = true;
                }

                if ($request->Is_completed && !$tokenBooking->Is_completed) {
                    $tokenBooking->Is_completed = $request->Is_completed;
                    $tokenBooking->checkoutTime = now();
                    $checkoutCompleted = true;
                }

                $tokenBooking->save();
            }

            $responseMessage = '';

            if ($checkinCompleted) {
                $responseMessage .= 'Check-in completed. ';
            }

            if ($checkoutCompleted) {
                $responseMessage .= 'Check-out completed. ';
            }

            if (empty($responseMessage)) {
                $responseMessage = 'No actions performed.';
            }

            // Add the updated token details to the response if needed

            return response()->json(['message' => $responseMessage, ], 200);


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
