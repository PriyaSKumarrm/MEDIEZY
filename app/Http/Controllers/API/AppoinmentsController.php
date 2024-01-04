<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Banner;
use App\Models\DocterLeave;
use App\Models\DocterAvailability;
use App\Models\Patient;
use App\Models\schedule;
use App\Models\Docter;
use App\Models\Symtoms;
use App\Models\TodaySchedule;
use App\Models\TokenBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppoinmentsController extends BaseController
{


    // private function getClinics($doctorId)
    // {


    //     $docter=Docter::select('id')->where('UserId', $doctorId)->get();

    //     $clinics = DocterAvailability::where('docter_id', $docter->id)->get(['id', 'hospital_Name', 'startingTime', 'endingTime', 'address', 'location']);

    //     return $clinics;
    // }
    private function getClinics($doctorUserId)
    {
        // Assuming 'UserId' is the column name in the 'Docter' model
        $docter = Docter::select('id')->where('UserId', $doctorUserId)->first();

        if (!$docter) {
            // Handle the case where the doctor with the given user ID is not found
            return []; // or throw an exception, log an error, etc.
        }

        $clinics = DocterAvailability::where('docter_id', $docter->id)
            ->get(['id', 'hospital_Name', 'startingTime', 'endingTime', 'address', 'location']);

        return $clinics;
    }



    // public function GetUserAppointments(Request $request, $userId)
    // {
    //     try {
    //         // Get the currently authenticated patient
    //         $patient = Patient::where('UserId', $userId)->first();

    //         if (!$patient) {
    //             return response()->json(['message' => 'Patient not found.'], 404);
    //         }

    //         // Validate the date format (if needed)

    //         // Get all appointments for the doctor on the selected date
    //         $appointments = Patient::join('token_booking', 'token_booking.BookedPerson_id', '=', 'patient.UserId')
    //             ->join('docter', 'docter.UserId', '=', 'token_booking.doctor_id') // Join the doctor table
    //             ->where('patient.UserId', $patient->UserId)
    //             ->orderBy('token_booking.date', 'asc')
    //             ->where('Is_completed', 0)
    //             ->distinct()
    //             ->get(['token_booking.*', 'docter.*']);

    //         if ($appointments->isEmpty()) {

    //             return $this->sendResponse('Appointments', null, '1', 'No appointments found for the patient.');
    //         }

    //         // Initialize an array to store appointments along with doctor details
    //         $appointmentsWithDetails = [];
    //         $DocterEarly = 0;
    //         $DocterLate = 0;

    //         // Get the current ongoing token number
    //         $currentOngoingToken = $this->getCurrentOngoingToken($appointments);

    //         $firstAppointment = $appointments->first();
    //         $doctorId = $firstAppointment->doctor_id;
    //         $ClinicId = $firstAppointment->clinic_id;
    //         $currentDate = Carbon::now()->toDateString();



    //         $isDoctorOnLeave = DocterLeave::where('docter_id', $doctorId)
    //             ->where('date', $currentDate)
    //             ->exists();

    //         if ($isDoctorOnLeave) {
    //             return response()->json(['message' => 'Sorry, the doctor is on emergency leave.'], 200);
    //         }
    //         // Get the doctor's schedule for the current date
    //         $doctorSchedule = Schedule::where('docter_id', $doctorId)->where('hospital_Id', $ClinicId)
    //             ->get();
    //         $tokensJson = $doctorSchedule->first()->tokens;
    //         $tokensArray = json_decode($tokensJson, true);

    //         $today_schedule = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
    //             ->where('docter_id',  $doctorId)
    //             ->where('hospital_Id', $ClinicId)
    //             ->where('date', $firstAppointment->date)
    //             ->first();


    //         if ($today_schedule) {
    //             $tokensArray = json_decode($today_schedule->tokens, true);
    //         }
    //         $firstToken = reset($tokensArray);
    //         $firstTime = $firstToken['Time'];


    //         $doctorSchedule = Schedule::where('docter_id', $doctorId)->where('hospital_Id', $ClinicId)->get();

    //         $morningEvneningTokens = $doctorSchedule->first()->tokens;

    //         $today_schedules = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
    //             ->where('docter_id',  $doctorId)
    //             ->where('hospital_Id', $ClinicId)
    //             ->where('date', $currentDate)
    //             ->first();

    //         if ($today_schedules) {
    //             $morningEvneningTokens = $today_schedules->first()->tokens;
    //         }

    //         $tokens = json_decode($morningEvneningTokens, true);
    //         $tokenCount = count($tokens);


    //         $TokenArray = TokenBooking::where('doctor_id', $doctorId)
    //             ->whereDate('Bookingtime', '=', now()->toDateString())
    //             ->select('checkinTime', 'checkoutTime')
    //             ->selectRaw('CAST(TokenNumber AS SIGNED) AS token')
    //             ->orderby('token', 'asc')
    //             ->get()->toArray();

    //         if (!empty($TokenArray)) {

    //             $existingTokens = array_column($TokenArray, "token");

    //             for ($i = 1; $i <= $tokenCount; $i++) {
    //                 if (!in_array($i, $existingTokens)) {
    //                     $TokenArray[] = [
    //                         "checkinTime" => null,
    //                         "checkoutTime" => null,
    //                         "token" => $i
    //                     ];
    //                 }
    //             }
    //             // Sort the array based on the "token" value in ascending order
    //             usort($TokenArray, function ($a, $b) {
    //                 return $a['token'] - $b['token'];
    //             });



    //             $foundKey = 0;

    //             foreach ($TokenArray as $key => $TokenArrays) {
    //                 if ($TokenArrays["checkinTime"] === null && $TokenArrays["checkoutTime"] === null) {
    //                     $foundKey = $key;
    //                     break;  // Stop searching after the first occurrence
    //                 }
    //             }

    //             $dateNow = date('Y-m-d');
    //             $newkey = $foundKey - 1;

    //             if ($newkey >= 0) {

    //                 $tokenStart = $tokens[$newkey]['Time'];
    //                 $tokenStartDateString = $dateNow . ' ' . $tokenStart;
    //                 $tokenEnd = $tokens[$newkey]['Tokens'];
    //                 $tokenEndDateString = $dateNow . ' ' . $tokenEnd;
    //                 $checkInString = $TokenArray[$newkey]['checkinTime'];
    //                 $checkOutString = $TokenArray[$newkey]['checkoutTime'];
    //                 $checkIn = Carbon::parse($checkInString);
    //                 $checkOut = Carbon::parse($checkOutString);
    //                 $tokenStartDate = Carbon::parse($tokenStartDateString);
    //                 $tokenEndDate = Carbon::parse($tokenEndDateString);



    //                 if ($checkInString !== null && $checkOutString == null) {

    //                     if ($tokenStartDate->greaterThan($checkIn)) {
    //                         $difference = -$tokenStartDate->diffInSeconds($checkIn);
    //                     } elseif ($tokenStartDate->lessThan($checkIn)) {
    //                         $difference = $checkIn->diffInSeconds($tokenStartDate);
    //                     } else {
    //                         $difference = 0;
    //                     }

    //                     $patientsDelay = $difference;
    //                 } elseif ($checkInString !== null && $checkOutString !== null) {
    //                     if ($tokenEndDate->greaterThan($checkOut)) {
    //                         $endDifference = -$tokenEndDate->diffInSeconds($checkOut);
    //                     } elseif ($tokenEndDate->lessThan($checkOut)) {
    //                         $endDifference = $checkOut->diffInSeconds($tokenEndDate);
    //                     } else {
    //                         $endDifference = 0;
    //                     }
    //                     //$patientsDelay += $endDifference;
    //                     $patientsDelay = $endDifference;
    //                 }
    //             } else {
    //                 $patientsDelay = 0;
    //             }
    //         } else {
    //             $patientsDelay = 0;
    //         }



    //         $appointmentsWithDetails = [];



    //         $DelayTime = 20 * 60;
    //         // Iterate through each appointment and add symptoms information
    //         foreach ($appointments as $key => $appointment) {
    //             $symptoms = json_decode($appointment->Appoinmentfor_id, true);

    //             $today_scheduleforlateanderaly = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
    //                 ->where('docter_id',  $appointment->doctor_id)
    //                 ->where('hospital_Id', $appointment->clinic_id)
    //                 ->where('date', $appointment->date)
    //                 ->get();



    //             $DocterEarly = null;
    //             $DocterLate = null;

    //             foreach ($today_scheduleforlateanderaly as $schedule) {
    //                 if ($schedule->delay_type === 1) {
    //                     $DocterEarly = $schedule->delay_time;
    //                 } elseif ($schedule->delay_type === 2) {
    //                     $DocterLate = $schedule->delay_time;
    //                 }
    //             }


    //             // Extract appointment details
    //             $appointmentDetails = [
    //                 'TokenNumber' => $appointment->TokenNumber,
    //                 'Date' => $appointment->date,
    //                 'Startingtime' => Carbon::parse($appointment->TokenTime)->format('g:i'),
    //                 'PatientName' => $appointment->PatientName,
    //                 'main_symptoms' => Symtoms::select('id', 'symtoms')->whereIn('id', $symptoms['Appoinmentfor1'])->get()->toArray(),
    //                 'other_symptoms' => Symtoms::select('id', 'symtoms')->whereIn('id', $symptoms['Appoinmentfor2'])->get()->toArray(),
    //                 'TokenBookingDate' => Carbon::parse($appointment->Bookingtime)->toDateString(),
    //                 'TokenBookingTime' => Carbon::parse($appointment->Bookingtime)->toTimeString(),
    //                 'ConsultationStartsfrom' => Carbon::parse($firstTime)->format('g:i'),
    //                 'DoctorEarlyFor' => intval($DocterEarly),
    //                 'DoctorLateFor' => intval($DocterLate),
    //                 'estimateTime' =>  Carbon::parse($appointment->TokenTime)->addSeconds($patientsDelay)->subSeconds($DelayTime)->format('g:i A')
    //             ];

    //             // Extract doctor details from the first appointment (assuming all appointments have the same doctor details)
    //             $doctorDetails = [
    //                 'firstname' => $appointment->firstname,
    //                 'secondname' => $appointment->lastname,
    //                 'Specialization' => $appointment->specialization,
    //                 'DocterImage' => asset("DocterImages/images/{$appointment->docter_image}"),
    //                 'Mobile Number' => $appointment->mobileNo,
    //                 'MainHospital' => $appointment->Services_at,
    //                 'subspecification_id' => $appointment->subspecification_id,
    //                 'specification_id' => $appointment->specification_id,
    //                 'specifications' => explode(',', $appointment->specifications),
    //                 'subspecifications' => explode(',', $appointment->subspecifications),
    //                 'clincs' => $this->getClinics($appointment->doctor_id),
    //             ];

    //             // Combine appointment and doctor details
    //             $combinedDetails = array_merge($appointmentDetails, $doctorDetails);

    //             // Add to the array
    //             $appointmentsWithDetails[] = $combinedDetails;
    //         }


    //         // Return a success response with the appointments, doctor details, and current ongoing token
    //         return $this->sendResponse('Appointments', ['appointmentsDetails' => $appointmentsWithDetails, 'currentOngoingToken' => $currentOngoingToken], '1', 'Appointments retrieved successfully.');
    //     } catch (\Exception $e) {
    //         // Handle unexpected errors
    //         return $this->sendError('Error', $e->getMessage(), 500);
    //     }
    // }
    // public function GetUserAppointments(Request $request, $userId)
    // {
    //     try {
    //         // Get the currently authenticated patient
    //         $patient = Patient::where('UserId', $userId)->first();

    //         if (!$patient) {
    //             return response()->json(['message' => 'Patient not found.'], 404);
    //         }

    //         // Validate the date format (if needed)

    //         // Get all appointments for the doctor on the selected date
    //         $appointments = Patient::join('token_booking', 'token_booking.BookedPerson_id', '=', 'patient.UserId')
    //             ->join('docter', 'docter.UserId', '=', 'token_booking.doctor_id') // Join the doctor table
    //             ->where('patient.UserId', $patient->UserId)
    //             ->orderBy('token_booking.date', 'asc')
    //             ->where('Is_completed', 0)
    //             ->distinct()
    //             ->get(['token_booking.*', 'docter.*']);

    //         if ($appointments->isEmpty()) {

    //             return $this->sendResponse('Appointments', null, '1', 'No appointments found for the patient.');
    //         }

    //         // Initialize an array to store appointments along with doctor details
    //         $appointmentsWithDetails = [];
    //         $DocterEarly = 0;
    //         $DocterLate = 0;

    //         // Get the current ongoing token number
    //         $currentOngoingToken = $this->getCurrentOngoingToken($appointments);

    //         $firstAppointment = $appointments->first();
    //         $doctorId = $firstAppointment->doctor_id;
    //         $ClinicId = $firstAppointment->clinic_id;
    //         $currentDate = Carbon::now()->toDateString();

    //         $morningTokens = [];
    //         $eveningTokens = [];

    //         $isDoctorOnLeave = DocterLeave::where('docter_id', $doctorId)
    //             ->where('date', $currentDate)
    //             ->exists();

    //         if ($isDoctorOnLeave) {
    //             return response()->json(['message' => 'Sorry, the doctor is on emergency leave.'], 200);
    //         }
    //         // Get the doctor's schedule for the current date
    //         $doctorSchedule = Schedule::where('docter_id', $doctorId)->where('hospital_Id', $ClinicId)
    //             ->get();
    //         $tokensJson = $doctorSchedule->first()->tokens;
    //         $tokensArray = json_decode($tokensJson, true);

    //         $today_schedule = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
    //             ->where('docter_id',  $doctorId)
    //             ->where('hospital_Id', $ClinicId)
    //             ->where('date', $firstAppointment->date)
    //             ->first();


    //         if ($today_schedule) {
    //             $tokensArray = json_decode($today_schedule->tokens, true);
    //         }
    //         $firstToken = reset($tokensArray);
    //         $firstTime = $firstToken['Time'];


    //         $doctorSchedule = Schedule::where('docter_id', $doctorId)->where('hospital_Id', $ClinicId)->get();

    //         $morningEvneningTokens = $doctorSchedule->first()->tokens;


    //         $today_schedules = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
    //             ->where('docter_id',  $doctorId)
    //             ->where('hospital_Id', $ClinicId)
    //             ->where('date', $currentDate)
    //             ->first();

    //         if ($today_schedules) {
    //             $morningEvneningTokens = $today_schedules->first()->tokens;
    //         }


    //         $tokens = json_decode($morningEvneningTokens, true);

    //         foreach ($tokens as $token) {
    //             if (Carbon::parse($token->Time) < Carbon::parse('13:00:00')) {
    //                 $morningTokens[] = $token;
    //             } else {
    //                 $eveningTokens[] = $token;
    //             }
    //         }
    //         $tokenCount = count($tokens);


    //         $TokenArray = TokenBooking::where('doctor_id', $doctorId)
    //             ->whereDate('Bookingtime', '=', now()->toDateString())
    //             ->select('checkinTime', 'checkoutTime')
    //             ->selectRaw('CAST(TokenNumber AS SIGNED) AS token')
    //             ->orderby('token', 'asc')
    //             ->get()->toArray();

    //         if (!empty($TokenArray)) {

    //             $existingTokens = array_column($TokenArray, "token");

    //             for ($i = 1; $i <= $tokenCount; $i++) {
    //                 if (!in_array($i, $existingTokens)) {
    //                     $TokenArray[] = [
    //                         "checkinTime" => null,
    //                         "checkoutTime" => null,
    //                         "token" => $i
    //                     ];
    //                 }
    //             }
    //             // Sort the array based on the "token" value in ascending order
    //             usort($TokenArray, function ($a, $b) {
    //                 return $a['token'] - $b['token'];
    //             });



    //             $foundKey = 0;

    //             foreach ($TokenArray as $key => $TokenArrays) {
    //                 if ($TokenArrays["checkinTime"] === null && $TokenArrays["checkoutTime"] === null) {
    //                     $foundKey = $key;
    //                     break;  // Stop searching after the first occurrence
    //                 }
    //             }

    //             $dateNow = date('Y-m-d');
    //             $newkey = $foundKey - 1;

    //             if ($newkey >= 0) {

    //                 $MorningtokenStart = $morningTokens[$newkey]['Time'];
    //                 $MorningtokenStartDateString = $dateNow . ' ' . $MorningtokenStart;
    //                 $MorningtokenEnd = $morningTokens[$newkey]['Tokens'];
    //                 $MorningtokenEndDateString = $dateNow . ' ' . $MorningtokenEnd;
    //                 $MorningcheckInString = $TokenArray[$newkey]['checkinTime'];
    //                 $MorningcheckOutString = $TokenArray[$newkey]['checkoutTime'];
    //                 $MorningcheckIn = Carbon::parse($MorningcheckInString);
    //                 $MorningcheckOut = Carbon::parse($MorningcheckOutString);
    //                 $MorningtokenStartDate = Carbon::parse($MorningtokenStartDateString);
    //                 $MorningtokenEndDate = Carbon::parse($MorningtokenEndDateString);



    //                 if ($MorningcheckInString !== null && $MorningcheckOutString == null) {

    //                     if ($MorningtokenStart->greaterThan($MorningcheckIn)) {
    //                         $difference = -$MorningtokenStartDate->diffInSeconds($MorningcheckIn);
    //                     } elseif ($MorningtokenStartDate->lessThan($MorningcheckIn)) {
    //                         $difference = $MorningcheckIn->diffInSeconds($MorningtokenStartDate);
    //                     } else {
    //                         $difference = 0;
    //                     }

    //                     $patientsDelay = $difference;
    //                 } elseif ($MorningcheckInString !== null && $MorningcheckOutString !== null) {
    //                     if ($MorningtokenEndDate->greaterThan($MorningcheckOut)) {
    //                         $endDifference = -$MorningtokenEndDate->diffInSeconds($MorningcheckOut);
    //                     } elseif ($MorningtokenEndDate->lessThan($MorningcheckOut)) {
    //                         $endDifference = $MorningcheckOut->diffInSeconds($MorningtokenEndDate);
    //                     } else {
    //                         $endDifference = 0;
    //                     }
    //                     //$patientsDelay += $endDifference;
    //                     $patientsDelay = $endDifference;
    //                 }
    //             } else {
    //                 $patientsDelay = 0;
    //             }
    //         } else {
    //             $patientsDelay = 0;
    //         }


    //         if ($newkey >= 0) {

    //             $tokenStart = $eveningTokens[$newkey]['Time'];
    //             $tokenStartDateString = $dateNow . ' ' . $tokenStart;
    //             $tokenEnd = $tokens[$newkey]['Tokens'];
    //             $tokenEndDateString = $dateNow . ' ' . $tokenEnd;
    //             $checkInString = $TokenArray[$newkey]['checkinTime'];
    //             $checkOutString = $TokenArray[$newkey]['checkoutTime'];
    //             $checkIn = Carbon::parse($checkInString);
    //             $checkOut = Carbon::parse($checkOutString);
    //             $tokenStartDate = Carbon::parse($tokenStartDateString);
    //             $tokenEndDate = Carbon::parse($tokenEndDateString);



    //             if ($checkInString !== null && $checkOutString == null) {

    //                 if ($tokenStartDate->greaterThan($checkIn)) {
    //                     $difference = -$tokenStartDate->diffInSeconds($checkIn);
    //                 } elseif ($tokenStartDate->lessThan($checkIn)) {
    //                     $difference = $checkIn->diffInSeconds($tokenStartDate);
    //                 } else {
    //                     $difference = 0;
    //                 }

    //                 $patientsDelay = $difference;
    //             } elseif ($checkInString !== null && $checkOutString !== null) {
    //                 if ($tokenEndDate->greaterThan($checkOut)) {
    //                     $endDifference = -$tokenEndDate->diffInSeconds($checkOut);
    //                 } elseif ($tokenEndDate->lessThan($checkOut)) {
    //                     $endDifference = $checkOut->diffInSeconds($tokenEndDate);
    //                 } else {
    //                     $endDifference = 0;
    //                 }
    //                 //$patientsDelay += $endDifference;
    //                 $patientsDelay = $endDifference;
    //             }
    //         } else {
    //             $patientsDelay = 0;
    //         }








    //         $appointmentsWithDetails = [];



    //         $DelayTime = 20 * 60;
    //         // Iterate through each appointment and add symptoms information
    //         foreach ($appointments as $key => $appointment) {
    //             $symptoms = json_decode($appointment->Appoinmentfor_id, true);

    //             $today_scheduleforlateanderaly = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
    //                 ->where('docter_id',  $appointment->doctor_id)
    //                 ->where('hospital_Id', $appointment->clinic_id)
    //                 ->where('date', $appointment->date)
    //                 ->get();



    //             $DocterEarly = null;
    //             $DocterLate = null;

    //             foreach ($today_scheduleforlateanderaly as $schedule) {
    //                 if ($schedule->delay_type === 1) {
    //                     $DocterEarly = $schedule->delay_time;
    //                 } elseif ($schedule->delay_type === 2) {
    //                     $DocterLate = $schedule->delay_time;
    //                 }
    //             }


    //             // Extract appointment details
    //             $appointmentDetails = [
    //                 'TokenNumber' => $appointment->TokenNumber,
    //                 'Date' => $appointment->date,
    //                 'Startingtime' => Carbon::parse($appointment->TokenTime)->format('g:i'),
    //                 'PatientName' => $appointment->PatientName,
    //                 'main_symptoms' => Symtoms::select('id', 'symtoms')->whereIn('id', $symptoms['Appoinmentfor1'])->get()->toArray(),
    //                 'other_symptoms' => Symtoms::select('id', 'symtoms')->whereIn('id', $symptoms['Appoinmentfor2'])->get()->toArray(),
    //                 'TokenBookingDate' => Carbon::parse($appointment->Bookingtime)->toDateString(),
    //                 'TokenBookingTime' => Carbon::parse($appointment->Bookingtime)->toTimeString(),
    //                 'ConsultationStartsfrom' => Carbon::parse($firstTime)->format('g:i'),
    //                 'DoctorEarlyFor' => intval($DocterEarly),
    //                 'DoctorLateFor' => intval($DocterLate),
    //                 'estimateTime' =>  Carbon::parse($appointment->TokenTime)->addSeconds($patientsDelay)->subSeconds($DelayTime)->format('g:i A')
    //             ];

    //             // Extract doctor details from the first appointment (assuming all appointments have the same doctor details)
    //             $doctorDetails = [
    //                 'firstname' => $appointment->firstname,
    //                 'secondname' => $appointment->lastname,
    //                 'Specialization' => $appointment->specialization,
    //                 'DocterImage' => asset("DocterImages/images/{$appointment->docter_image}"),
    //                 'Mobile Number' => $appointment->mobileNo,
    //                 'MainHospital' => $appointment->Services_at,
    //                 'subspecification_id' => $appointment->subspecification_id,
    //                 'specification_id' => $appointment->specification_id,
    //                 'specifications' => explode(',', $appointment->specifications),
    //                 'subspecifications' => explode(',', $appointment->subspecifications),
    //                 'clincs' => $this->getClinics($appointment->doctor_id),
    //             ];

    //             // Combine appointment and doctor details
    //             $combinedDetails = array_merge($appointmentDetails, $doctorDetails);

    //             // Add to the array
    //             $appointmentsWithDetails[] = $combinedDetails;
    //         }


    //         // Return a success response with the appointments, doctor details, and current ongoing token
    //         return $this->sendResponse('Appointments', ['appointmentsDetails' => $appointmentsWithDetails, 'currentOngoingToken' => $currentOngoingToken], '1', 'Appointments retrieved successfully.');
    //     } catch (\Exception $e) {
    //         // Handle unexpected errors
    //         return $this->sendError('Error', $e->getMessage(), 500);
    //     }
    // }



    public function GetUserAppointments(Request $request, $userId)
    {
        try {
            // Get the currently authenticated patient
            $patient = Patient::where('UserId', $userId)->first();

            if (!$patient) {
                return response()->json(['message' => 'Patient not found.'], 404);
            }

            // Validate the date format (if needed)

            // Get all appointments for the doctor on the selected date
            $appointments = Patient::join('token_booking', 'token_booking.BookedPerson_id', '=', 'patient.UserId')
                ->join('docter', 'docter.UserId', '=', 'token_booking.doctor_id') // Join the doctor table
                ->where('patient.UserId', $patient->UserId)
                ->orderBy('token_booking.date', 'asc')
                ->where('Is_completed', 0)
                ->distinct()
                ->get(['token_booking.*', 'docter.*']);

            if ($appointments->isEmpty()) {

                return $this->sendResponse('Appointments', null, '1', 'No appointments found for the patient.');
            }

            // Initialize an array to store appointments along with doctor details
            $appointmentsWithDetails = [];
            $DocterEarly = 0;
            $DocterLate = 0;



            $firstAppointment = $appointments->first();
            $doctorId = $firstAppointment->doctor_id;
            $ClinicId = $firstAppointment->clinic_id;
            $currentDate = Carbon::now()->toDateString();





            // Get the doctor's schedule for the current date
            $doctorSchedule = Schedule::where('docter_id', $doctorId)->where('hospital_Id', $ClinicId)
                ->get();
            $tokensJson = $doctorSchedule->first()->tokens;
            $tokensArray = json_decode($tokensJson, true);

            $today_schedule = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
                ->where('docter_id',  $doctorId)
                ->where('hospital_Id', $ClinicId)
                ->where('date', $firstAppointment->date)
                ->first();


            if ($today_schedule) {
                $tokensArray = json_decode($today_schedule->tokens, true);
            }
            $firstToken = reset($tokensArray);
            $firstTime = $firstToken['Time'];


            $doctorSchedule = Schedule::where('docter_id', $doctorId)->where('hospital_Id', $ClinicId)->get();

            $morningEvneningTokens = $doctorSchedule->first()->tokens;


            $today_schedules = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
                ->where('docter_id',  $doctorId)
                ->where('hospital_Id', $ClinicId)
                ->where('date', $currentDate)
                ->first();

            if ($today_schedules) {
                $morningEvneningTokens = $today_schedules->first()->tokens;
            }


            $tokens = json_decode($morningEvneningTokens, true);

            $morningTokens = [];
            $eveningTokens = [];

            foreach ($tokens as $token) {
                $appointmentTime = strtotime($token['Time']);

                if ($appointmentTime < strtotime('13:00:00')) {
                    // Morning appointment
                    $morningTokens[] = $token;
                } else {
                    // Evening appointment
                    $eveningTokens[] = $token;
                }
            }


            $tokenCount = count($tokens);


            $TokenArray = TokenBooking::where('doctor_id', $doctorId)
                ->whereDate('Bookingtime', '=', now()->toDateString())
                ->select('checkinTime', 'checkoutTime')
                ->selectRaw('CAST(TokenNumber AS SIGNED) AS token')
                ->orderby('token', 'asc')
                ->get()->toArray();

            if (!empty($TokenArray)) {

                $existingTokens = array_column($TokenArray, "token");

                for ($i = 1; $i <= $tokenCount; $i++) {
                    if (!in_array($i, $existingTokens)) {
                        $TokenArray[] = [
                            "checkinTime" => null,
                            "checkoutTime" => null,
                            "token" => $i
                        ];
                    }
                }
                // Sort the array based on the "token" value in ascending order
                usort($TokenArray, function ($a, $b) {
                    return $a['token'] - $b['token'];
                });



                $foundKey = 0;

                foreach ($TokenArray as $key => $TokenArrays) {
                    if ($TokenArrays["checkinTime"] === null && $TokenArrays["checkoutTime"] === null) {
                        $foundKey = $key;
                        break;  // Stop searching after the first occurrence
                    }
                }

                $dateNow = date('Y-m-d');
                $newkey = $foundKey - 1;

                if ($newkey >= 0) {

                    $MorningtokenStart = $morningTokens[$newkey]['Time'];

                    $MorningtokenStartDateString = $dateNow . ' ' . $MorningtokenStart;
                    $MorningtokenStart = Carbon::parse($MorningtokenStartDateString);
                    $MorningtokenEnd = $morningTokens[$newkey]['Tokens'];
                    $MorningtokenEndDateString = $dateNow . ' ' . $MorningtokenEnd;
                    $MorningcheckInString = $TokenArray[$newkey]['checkinTime'];
                    $MorningcheckOutString = $TokenArray[$newkey]['checkoutTime'];
                    $MorningcheckIn = Carbon::parse($MorningcheckInString);
                    $MorningcheckOut = Carbon::parse($MorningcheckOutString);
                    $MorningtokenStartDate = Carbon::parse($MorningtokenStartDateString);
                    $MorningtokenEndDate = Carbon::parse($MorningtokenEndDateString);

                    //dd($MorningtokenStart,$MorningcheckIn);

                    if ($MorningcheckInString !== null && $MorningcheckOutString == null) {

                        if ($MorningtokenStart->greaterThan($MorningcheckIn)) {
                            $difference = -$MorningtokenStartDate->diffInSeconds($MorningcheckIn);
                        } elseif ($MorningtokenStartDate->lessThan($MorningcheckIn)) {
                            $difference = $MorningcheckIn->diffInSeconds($MorningtokenStartDate);
                        } else {
                            $difference = 0;
                        }

                        $patientsDelay = $difference;
                    } elseif ($MorningcheckInString !== null && $MorningcheckOutString !== null) {
                        if ($MorningtokenEndDate->greaterThan($MorningcheckOut)) {
                            $endDifference = -$MorningtokenEndDate->diffInSeconds($MorningcheckOut);
                        } elseif ($MorningtokenEndDate->lessThan($MorningcheckOut)) {
                            $endDifference = $MorningcheckOut->diffInSeconds($MorningtokenEndDate);
                        } else {
                            $endDifference = 0;
                        }
                        //$patientsDelay += $endDifference;
                        $patientsDelay = $endDifference;
                    }
                } else {
                    $patientsDelay = 0;
                }
            } else {
                $patientsDelay = 0;
            }











            $appointmentsWithDetails = [];



            $DelayTime = 20 * 60;
            // Iterate through each appointment and add symptoms information
            foreach ($appointments as $key => $appointment) {

                $isDoctorOnLeave = DocterLeave::where('docter_id', $appointment->doctor_id)
                    ->where('date', $appointment->date)
                    ->exists();
                if ($isDoctorOnLeave) {
                    // Doctor is on leave
                    $leaveMessage = 'Doctor is on emergency leave.';
                } else {
                    $leaveMessage = ''; // No leave message if doctor is not on leave
                }

                $doctorId = $appointment->doctor_id;

                $currentOngoingToken = TokenBooking::where('doctor_id', $doctorId)
                    ->where('Is_checkIn', 1)
                    ->where('date', $currentDate)
                    ->orderBy('TokenNumber', 'ASC')
                    ->pluck('TokenNumber');

                if ($currentOngoingToken) {
                    $CurrentToken = $currentOngoingToken->max();
                } else {
                    $CurrentToken = 0;
                }
                $symptoms = json_decode($appointment->Appoinmentfor_id, true);

                $today_scheduleforlateanderaly = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id', 'delay_time', 'delay_type')
                    ->where('docter_id',  $appointment->doctor_id)
                    ->where('hospital_Id', $appointment->clinic_id)
                    ->where('date', $appointment->date)
                    ->get();



                $DocterEarly = null;
                $DocterLate = null;

                foreach ($today_scheduleforlateanderaly as $schedule) {
                    if ($schedule->delay_type === 1) {
                        $DocterEarly = $schedule->delay_time;
                    } elseif ($schedule->delay_type === 2) {
                        $DocterLate = $schedule->delay_time;
                    }
                }


                // Extract appointment details
                $appointmentDetails = [
                    'TokenNumber' => $appointment->TokenNumber,
                    'Date' => $appointment->date,
                    'Startingtime' => Carbon::parse($appointment->TokenTime)->format('g:i'),
                    'PatientName' => $appointment->PatientName,
                    'main_symptoms' => Symtoms::select('id', 'symtoms')->whereIn('id', $symptoms['Appoinmentfor1'])->get()->toArray(),
                    'other_symptoms' => Symtoms::select('id', 'symtoms')->whereIn('id', $symptoms['Appoinmentfor2'])->get()->toArray(),
                    'TokenBookingDate' => Carbon::parse($appointment->Bookingtime)->toDateString(),
                    'TokenBookingTime' => Carbon::parse($appointment->Bookingtime)->toTimeString(),
                    'ConsultationStartsfrom' => Carbon::parse($firstTime)->format('g:i'),
                    'DoctorEarlyFor' => intval($DocterEarly),
                    'DoctorLateFor' => intval($DocterLate),
                    'estimateTime' =>  Carbon::parse($appointment->TokenTime)->addSeconds($patientsDelay)->subSeconds($DelayTime)->format('g:i A'),
                    'currentOngoingToken' => $CurrentToken,
                    'LeaveMessage' => $leaveMessage,
                ];



                // Extract doctor details from the first appointment (assuming all appointments have the same doctor details)
                $doctorDetails = [
                    'firstname' => $appointment->firstname,
                    'secondname' => $appointment->lastname,
                    'Specialization' => $appointment->specialization,
                    'DocterImage' => asset("DocterImages/images/{$appointment->docter_image}"),
                    'Mobile Number' => $appointment->mobileNo,
                    'MainHospital' => $appointment->Services_at,
                    'subspecification_id' => $appointment->subspecification_id,
                    'specification_id' => $appointment->specification_id,
                    'specifications' => explode(',', $appointment->specifications),
                    'subspecifications' => explode(',', $appointment->subspecifications),
                    'clincs' => $this->getClinics($appointment->doctor_id),

                ];

                // Combine appointment and doctor details
                $combinedDetails = array_merge($appointmentDetails, $doctorDetails);

                // Add to the array
                $appointmentsWithDetails[] = $combinedDetails;
            }


            // Return a success response with the appointments, doctor details, and current ongoing token
            return $this->sendResponse('Appointments', ['appointmentsDetails' => $appointmentsWithDetails], '1', 'Appointments retrieved successfully.');
        } catch (\Exception $e) {
            // Handle unexpected errors
            return $this->sendError('Error', $e->getMessage(), 500);
        }
    }
}
