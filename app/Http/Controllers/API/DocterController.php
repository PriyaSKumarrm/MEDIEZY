<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Log;
use App\Models\Docter;
use App\Models\DocterAvailability;
use App\Models\DocterLeave;
use App\Models\schedule;
use App\Models\Specialize;
use App\Models\Specification;
use App\Models\Subspecification;
use App\Models\Symtoms;
use App\Models\TodaySchedule;
use App\Models\TokenBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class DocterController extends BaseController
{



    public function getallDocters()
    {
        $specializeArray['specialize'] = Specialize::all();
        $specificationArray['specification'] = Specification::all();
        $subspecificationArray['subspecification'] = Subspecification::all();

        $docters = Docter::join('docteravaliblity', 'docter.id', '=', 'docteravaliblity.docter_id')
            ->select('docter.UserId', 'docter.id', 'docter.docter_image', 'docter.firstname', 'docter.lastname', 'docter.specialization_id', 'docter.subspecification_id', 'docter.specification_id', 'docter.about', 'docter.location', 'docteravaliblity.id as avaliblityId', 'docter.gender', 'docter.email', 'docter.mobileNo', 'docter.Services_at', 'docteravaliblity.hospital_Name', 'docteravaliblity.availability')
            ->get();

        $doctersWithSpecifications = [];

        foreach ($docters as $doctor) {
            $id = $doctor['id'];

            if (!isset($doctersWithSpecifications[$id])) {

                $specialize = $specializeArray['specialize']->firstWhere('id', $doctor['specialization_id']);

                $doctersWithSpecifications[$id] = [
                    'id' => $id,
                    'UserId' => $doctor['UserId'],
                    'firstname' => $doctor['firstname'],
                    'secondname' => $doctor['lastname'],
                    'Specialization' => $specialize ? $specialize['specialization'] : null,
                    'DocterImage' => asset("DocterImages/images/{$doctor['docter_image']}"),
                    'About' => $doctor['about'],
                    'Location' => $doctor['location'],
                    'Gender' => $doctor['gender'],
                    'emailID' => $doctor['email'],
                    'Mobile Number' => $doctor['mobileNo'],
                    'MainHospital' => $doctor['Services_at'],
                    'subspecification_id' => $doctor['subspecification_id'],
                    'specification_id' => $doctor['specification_id'],
                    'specifications' => [],
                    'subspecifications' => [],
                    'clincs' => [],
                ];
            }

            $specificationIds = explode(',', $doctor['specification_id']);
            $subspecificationIds = explode(',', $doctor['subspecification_id']);

            $doctersWithSpecifications[$id]['specifications'] = array_merge(
                $doctersWithSpecifications[$id]['specifications'],
                array_map(function ($id) use ($specificationArray) {
                    return $specificationArray['specification']->firstWhere('id', $id)['specification'];
                }, $specificationIds)
            );

            $doctersWithSpecifications[$id]['subspecifications'] = array_merge(
                $doctersWithSpecifications[$id]['subspecifications'],
                array_map(function ($id) use ($subspecificationArray) {
                    return $subspecificationArray['subspecification']->firstWhere('id', $id)['subspecification'];
                }, $subspecificationIds)
            );

            $doctersWithSpecifications[$id]['clincs'][] = [
                'id'  => $doctor['avaliblityId'],
                'name' => $doctor['hospital_Name'],
                'StartingTime' => $doctor['startingTime'],
                'EndingTime' => $doctor['endingTime'],
                'Address' => $doctor['address'],
                'Location' => $doctor['location'],
            ];
        }

        // Format the output to match the expected structure
        $formattedOutput = array_values($doctersWithSpecifications);

        return $this->sendResponse("Docters", $formattedOutput, '1', 'Docters retrieved successfully.');
    }
    public function index()
    {
        // Assuming you have the authenticated user available in the request
        $authenticatedUserId = auth()->user()->id;

        $specializeArray['specialize'] = Specialize::all();
        $specificationArray['specification'] = Specification::all();
        $subspecificationArray['subspecification'] = Subspecification::all();

        $docters = Docter::join('docteravaliblity', 'docter.id', '=', 'docteravaliblity.docter_id')
            ->select('docter.UserId', 'docter.id', 'docter.docter_image', 'docter.firstname', 'docter.lastname', 'docter.specialization_id', 'docter.subspecification_id', 'docter.specification_id', 'docter.about', 'docter.location', 'docteravaliblity.id as avaliblityId', 'docter.gender', 'docter.email', 'docter.mobileNo', 'docter.Services_at', 'docteravaliblity.hospital_Name', 'docteravaliblity.startingTime','docteravaliblity.endingTime','docteravaliblity.address','docteravaliblity.location')
            ->get();

        $doctersWithSpecifications = [];

        foreach ($docters as $doctor) {
            $id = $doctor['id'];

            // Check if the doctor's user ID is in the "add_favorite" table for the authenticated user
            $favoriteStatus = DB::table('addfavourite')
                ->where('UserId', $authenticatedUserId)
                ->where('doctor_id', $doctor['UserId'])
                ->exists();

            if (!isset($doctersWithSpecifications[$id])) {
                $specialize = $specializeArray['specialize']->firstWhere('id', $doctor['specialization_id']);

                $doctersWithSpecifications[$id] = [
                    'id' => $id,
                    'UserId' => $doctor['UserId'],
                    'firstname' => $doctor['firstname'],
                    'secondname' => $doctor['lastname'],
                    'Specialization' => $specialize ? $specialize['specialization'] : null,
                    'DocterImage' => asset("DocterImages/images/{$doctor['docter_image']}"),
                    'About' => $doctor['about'],
                    'Location' => $doctor['location'],
                    'Gender' => $doctor['gender'],
                    'emailID' => $doctor['email'],
                    'Mobile Number' => $doctor['mobileNo'],
                    'MainHospital' => $doctor['Services_at'],
                    'subspecification_id' => $doctor['subspecification_id'],
                    'specification_id' => $doctor['specification_id'],
                    'specifications' => [],
                    'subspecifications' => [],
                    'clincs' => [],
                    'favoriteStatus' => $favoriteStatus ? 1 : 0, // Add favorite status
                ];
            }

            $specificationIds = explode(',', $doctor['specification_id']);
            $subspecificationIds = explode(',', $doctor['subspecification_id']);

            $doctersWithSpecifications[$id]['specifications'] = array_merge(
                $doctersWithSpecifications[$id]['specifications'],
                array_map(function ($id) use ($specificationArray) {
                    return $specificationArray['specification']->firstWhere('id', $id)['specification'];
                }, $specificationIds)
            );

            $doctersWithSpecifications[$id]['subspecifications'] = array_merge(
                $doctersWithSpecifications[$id]['subspecifications'],
                array_map(function ($id) use ($subspecificationArray) {
                    return $subspecificationArray['subspecification']->firstWhere('id', $id)['subspecification'];
                }, $subspecificationIds)
            );

            $doctersWithSpecifications[$id]['clincs'][] = [
                'id'  => $doctor['avaliblityId'],
                'name' => $doctor['hospital_Name'],
                'StartingTime' => $doctor['startingTime'],
                'EndingTime' => $doctor['endingTime'],
                'Address' => $doctor['address'],
                'Location' => $doctor['location'],
            ];
        }

        // Format the output to match the expected structure
        $formattedOutput = array_values($doctersWithSpecifications);

        return $this->sendResponse("Docters", $formattedOutput, '1', 'Docters retrieved successfully.');
    }






    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $input = $request->all();

            $emailExists = Docter::where('email', $input['email'])->count();
            $emailExistsinUser = User::where('email', $input['email'])->count();

            if ($emailExists && $emailExistsinUser) {
                return $this->sendResponse("Docters", null, '3', 'Email already exists.');
            }

            $input['password'] = Hash::make($input['password']);

            $userId = DB::table('users')->insertGetId([
                'firstname' => $input['firstname'],
                'secondname' => $input['secondname'],
                'email' => $input['email'],
                'password' => $input['password'],
                'user_role' => 2,
            ]);

            $DocterData = [

                'firstname' => $input['firstname'],
                'lastname' => $input['secondname'],
                'mobileNo' => $input['mobileNo'],
                'email' => $input['email'],
                'location' => $input['location'],
                'specification_id' => $input['specification_id'],
                'subspecification_id' => $input['subspecification_id'],
                'specialization_id' => $input['specialization_id'],
                'about' => $input['about'],
                'Services_at' => $input['service_at'],
                'gender' => $input['gender'],
                'UserId' => $userId,
            ];

            if ($request->hasFile('docter_image')) {
                $imageFile = $request->file('docter_image');

                if ($imageFile->isValid()) {
                    $imageName = $imageFile->getClientOriginalName();
                    $imageFile->move(public_path('DocterImages/images'), $imageName);

                    $DocterData['docter_image'] = $imageName;
                }
            }

            $Docter = new Docter($DocterData);
            $Docter->save();



            $hospitalData = json_decode($input['hospitals'], true); // Decode the JSON string

            // Create DocterAvailability records
            if (is_array($hospitalData)) {
                foreach ($hospitalData as $hospital) {
                    $availabilityData = [
                        'docter_id' => $Docter->id,
                        'hospital_Name' => $hospital['hospitalName'],
                        'startingTime' => $hospital['startingTime'],
                        'endingTime' => $hospital['endingTime'],
                        'address' => $hospital['hospitalAddress'],
                        'location' => $hospital['hospitalLocation'],
                    ];

                    // Create and save DocterAvailability records
                    $docterAvailability = new DocterAvailability($availabilityData);
                    $docterAvailability->save();
                }
            }




            DB::commit();

            return $this->sendResponse("Docters", $Docter, '1', 'Docter created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), $errorMessages = [], $code = 404);
        }
    }


    public function show($userId)
    {
        $authenticatedUserId = auth()->user()->id;


        $specializeArray['specialize'] = Specialize::all();
        $specificationArray['specification'] = Specification::all();
        $subspecificationArray['subspecification'] = Subspecification::all();


        $docters = Docter::join('docteravaliblity', 'docter.id', '=', 'docteravaliblity.docter_id')
            ->join('users', 'docter.UserId', '=', 'users.id') // Assuming 'UserId' is the foreign key in the 'Docter' table
            ->select('docter.UserId', 'docter.id', 'docter.docter_image', 'docter.firstname', 'docter.lastname', 'docter.specialization_id', 'docter.subspecification_id', 'docter.specification_id', 'docter.about', 'docter.location', 'docteravaliblity.id as avaliblityId', 'docter.gender', 'docter.email', 'docter.mobileNo', 'docter.Services_at', 'docteravaliblity.hospital_Name', 'docteravaliblity.startingTime','docteravaliblity.endingTime','docteravaliblity.address','docteravaliblity.location')
            ->where('users.id', $userId) // Filtering by UserId from the User table
            ->get();



          $doctersWithSpecifications = [];

        foreach ($docters as $doctor) {
            $id = $doctor['id'];

            // Check if the doctor's user ID is in the "add_favorite" table for the authenticated user
            $favoriteStatus = DB::table('addfavourite')
                ->where('UserId', $authenticatedUserId)
                ->where('doctor_id', $doctor['UserId'])
                ->exists();

            if (!isset($doctersWithSpecifications[$id])) {
                $specialize = $specializeArray['specialize']->firstWhere('id', $doctor['specialization_id']);

                $doctersWithSpecifications[$id] = [
                    'id' => $id,
                    'UserId' => $doctor['UserId'],
                    'firstname' => $doctor['firstname'],
                    'secondname' => $doctor['lastname'],
                    'Specialization' => $specialize ? $specialize['specialization'] : null,
                    'DocterImage' => asset("DocterImages/images/{$doctor['docter_image']}"),
                    'About' => $doctor['about'],
                    'Location' => $doctor['location'],
                    'Gender' => $doctor['gender'],
                    'emailID' => $doctor['email'],
                    'Mobile Number' => $doctor['mobileNo'],
                    'MainHospital' => $doctor['Services_at'],
                    'subspecification_id' => $doctor['subspecification_id'],
                    'specification_id' => $doctor['specification_id'],
                    'specifications' => [],
                    'subspecifications' => [],
                    'clincs' => [],
                    'favoriteStatus' => $favoriteStatus ? 1 : 0, // Add favorite status
                ];
            }

            $specificationIds = explode(',', $doctor['specification_id']);
            $subspecificationIds = explode(',', $doctor['subspecification_id']);

            $doctersWithSpecifications[$id]['specifications'] = array_merge(
                $doctersWithSpecifications[$id]['specifications'],
                array_map(function ($id) use ($specificationArray) {
                    return $specificationArray['specification']->firstWhere('id', $id)['specification'];
                }, $specificationIds)
            );

            $doctersWithSpecifications[$id]['subspecifications'] = array_merge(
                $doctersWithSpecifications[$id]['subspecifications'],
                array_map(function ($id) use ($subspecificationArray) {
                    return $subspecificationArray['subspecification']->firstWhere('id', $id)['subspecification'];
                }, $subspecificationIds)
            );

            $doctersWithSpecifications[$id]['clincs'][] = [
                'id'  => $doctor['avaliblityId'],
                'name' => $doctor['hospital_Name'],
                'StartingTime' => $doctor['startingTime'],
                'EndingTime' => $doctor['endingTime'],
                'Address' => $doctor['address'],
                'Location' => $doctor['location'],
            ];
        }

        // Format the output to match the expected structure
        $formattedOutput = array_values($doctersWithSpecifications);

        return $this->sendResponse("Docter", $formattedOutput, '1', 'Docters retrieved successfully.');
    }
    public function update(Request $request, $userId)
    {
        try {
            DB::beginTransaction();


            $docter = Docter::where('UserId', $userId)->first();

            if (is_null($docter)) {
                return $this->sendError('Docter not found.');
            }

            $input = $request->all();

            // Update fields as needed
            $docter->firstname = $input['firstname'] ?? $docter->firstname;
            $docter->lastname = $input['lastname'] ?? $docter->lastname;
            $docter->mobileNo = $input['mobileNo'] ?? $docter->mobileNo;
            $docter->email = $input['email'] ?? $docter->email;
            $docter->location = $input['location'] ?? $docter->location;

            // Handle image upload if a new image is provided
            if ($request->hasFile('docter_image')) {
                $imageFile = $request->file('docter_image');

                if ($imageFile->isValid()) {
                    $imageName = $imageFile->getClientOriginalName();
                    $imageFile->move(public_path('DocterImages/images'), $imageName);

                    $docter->docter_image = $imageName;
                }
            }

            $docter->save();

            $user = User::find($docter->UserId);

            if (!is_null($user)) {
                $user->firstname = $input['firstname'] ?? $user->firstname;
                $user->secondname = $input['lastname'] ?? $user->secondname;
                $user->mobileNo = $input['mobileNo'] ?? $user->mobileNo;
                $user->email = $input['email'] ?? $user->email;
                $user->save();
            }

            DB::commit();

            // Include UserId in the response
            $response = [
                'success' => true,
                'UserId' => $user->id,
                'Docter' => $docter,
                'code' => '1',
                'message' => 'Docter updated successfully.'
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError($e->getMessage(), $errorMessages = [], $code = 404);
        }
    }





    public function getDoctorsBySpecialization($specializationId)
    {
        $specializeArray['specialize'] = Specialize::all();
        $specificationArray['specification'] = Specification::all();
        $subspecificationArray['subspecification'] = Subspecification::all();

        $specialization = Specialize::findOrFail($specializationId);

        $doctors = Docter::join('docteravaliblity', 'docter.id', '=', 'docteravaliblity.docter_id')
            ->where('docter.specialization_id', $specializationId)
            ->select('docter.UserId', 'docter.id', 'docter.docter_image', 'docter.firstname', 'docter.lastname', 'docter.specialization_id', 'docter.subspecification_id', 'docter.specification_id', 'docter.about', 'docter.location', 'docteravaliblity.id as avaliblityId', 'docter.gender', 'docter.email', 'docter.mobileNo', 'docter.Services_at', 'docteravaliblity.hospital_Name', 'docteravaliblity.startingTime','docteravaliblity.endingTime','docteravaliblity.address','docteravaliblity.location')
            ->get();

        $doctorsWithSpecifications = [];

        foreach ($doctors as $doctor) {
            $id = $doctor->id;

            // Initialize doctor details if not already present
            if (!isset($doctorsWithSpecifications[$id])) {
                $doctorsWithSpecifications[$id] = [
                    'id' => $id,
                    'UserId' => $doctor->UserId,
                    'firstname' => $doctor->firstname,
                    'secondname' => $doctor->lastname,
                    'Specialization' => $specialization ? $specialization->specialization : null,
                    'DocterImage' => asset("DocterImages/images/{$doctor->docter_image}"),
                    'Location' => $doctor->location,
                    'MainHospital' => $doctor->Services_at,

                ];
            }


        }

        // Format the output to match the expected structure
        $formattedOutput = array_values($doctorsWithSpecifications);


        return $this->sendResponse("Docters", $formattedOutput, '1', 'Docters retrieved successfully.');
    }






    public function getHospitalName($userId)
    {
        // Query the Docter table to get the doctor's details based on the provided UserId
        $doctor = Docter::where('UserId', $userId)->first();

        if (is_null($doctor)) {
            return response()->json(['error' => 'Doctor not found for the given UserId'], 404);
        }

        // Retrieve the doctor id associated with the doctor
        $doctorId = $doctor->id;

        // Query the DocterAvailability table to get all hospital details for the doctor
        $hospitalDetails = DocterAvailability::where('docter_id', $doctorId)->get();

        if ($hospitalDetails->isEmpty()) {
            return response()->json(['error' => 'Hospital details not found for the selected doctor'], 404);
        }

        // Combine doctor details with hospital details
        $result = [

            'hospital_details' => $hospitalDetails,
        ];

        return response()->json($result);
    }
    public function getHospitalDetailsById($hospitalId)
{
    // Query the DocterAvailability table to get hospital details based on the provided hospitalId
    $hospitalDetails = DocterAvailability::find($hospitalId);

    if (is_null($hospitalDetails)) {
        return response()->json(['error' => 'Hospital not found for the given Hospital ID'], 404);
    }


    // Combine hospital details with doctor details
    $result = [
        'clinic_details' => $hospitalDetails,

    ];

    return response()->json($result);
}


    public function ApproveOrReject(Request $request)
    {
        $doctorId = $request->input('doctor_id');
        $action = $request->input('action'); // 'approve' or 'reject'

        $doctor = Docter::find($doctorId);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        // Update the is_approve column based on the action
        if ($action == 'approve') {
            $doctor->is_approve = 1;
        } elseif ($action == 'reject') {
            $doctor->is_approve = 2;
        }

        $doctor->save();

        return response()->json(['message' => 'Doctor ' . ucfirst($action) . 'd successfully']);
    }
    public function getSymptomsBySpecialization($userId)
    {
        // Find the doctor by user ID
        $doctor = Docter::where('UserId', $userId)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found.'], 404);
        }

        // Use a join to fetch symptoms based on the doctor's specialization and specialization_id in symtoms table
        $symptoms = Symtoms::join('docter', 'symtoms.specialization_id', '=', 'docter.specialization_id')
            ->where('docter.UserId', $doctor->UserId) // Assuming 'UserId' is the correct column name in 'docter' table
            ->get(['symtoms.*']); // Select only the columns from the 'symtoms' table

        return response()->json(['symptoms' => $symptoms], 200);
    }

    // public function getTokens(Request $request)
    // {
    //     $rules = [
    //         'doctor_id'     => 'required',
    //         'hospital_id'   => 'required',
    //         'date'          => 'required',
    //     ];
    //     $messages = [
    //         'date.required' => 'Date is required',
    //     ];
    //     $validation = Validator::make($request->all(), $rules, $messages);
    //     if ($validation->fails()) {
    //         return response()->json(['status' => false, 'response' => $validation->errors()->first()]);
    //     }
    //     try {
    //         $docter = Docter::where('id', $request->doctor_id)->first();
    //         if (!$docter) {
    //             return response()->json(['status' => false, 'message' => 'Doctor not found']);
    //         }
    //         $shedulded_tokens =  schedule::where('docter_id', $request->doctor_id)->where('hospital_Id', $request->hospital_id)->first();
    //         if (!$shedulded_tokens) {
    //             return response()->json(['status' => false, 'message' => 'Data not found']);
    //         }
    //         $requestDate = Carbon::parse($request->date);
    //         $startDate = Carbon::parse($shedulded_tokens->date);
    //         $scheduledUptoDate = Carbon::parse($shedulded_tokens->scheduleupto);
    //         // Get the day of the week
    //         $dayOfWeek = $requestDate->format('l'); // 'l' format gives the full name of the day
    //         $allowedDaysArray = json_decode($shedulded_tokens->selecteddays);
    //         $token_booking = TokenBooking::where('date', $request->date)->where('doctor_id', $request->doctor_id)->where('clinic_id', $request->hospital_id)->get();

    //         if (!$requestDate->between($startDate, $scheduledUptoDate)) {
    //             return response()->json(['status' => true, 'token_data' => null, 'message' => 'Token not found on this date']);
    //         }

    //         if (!in_array($dayOfWeek, $allowedDaysArray)) {
    //             return response()->json(['status' => true, 'token_data' => null, 'message' => 'Token not found on this day']);
    //         }

    //         $shedulded_tokens =  schedule::select('id', 'tokens', 'date', 'hospital_Id', 'startingTime', 'endingTime')->where('docter_id', $request->doctor_id)->where('hospital_Id', $request->hospital_id)->first();
    //         $shedulded_tokens['tokens'] = json_decode($shedulded_tokens->tokens);


    //         $today_schedule = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id')->where('docter_id', $request->doctor_id)->where('hospital_Id', $request->hospital_id)->where('date', $request->date)->first();

    //         if ($today_schedule) {
    //             $today_schedule['startingTime'] = $shedulded_tokens->startingTime;
    //             $today_schedule['endingTime']   = $shedulded_tokens->endingTime;
    //             $shedulded_tokens = $today_schedule;
    //             $shedulded_tokens['tokens'] = json_decode($today_schedule->tokens);
    //         }

    //         foreach ($shedulded_tokens['tokens'] as $token) {
    //             // Set is_booked to 1 (or any other value you want)
    //             $token_booking = TokenBooking::where('date', $request->date)->where('doctor_id', $request->doctor_id)->where('clinic_id', $request->hospital_id)->where('TokenTime', $token->Time)->where('TokenNumber', $token->Number)->first();
    //             if ($token_booking) {
    //                 $token->is_booked = 1;
    //             }
    //         }
    //         return response()->json(['status' => true, 'token_data' => $shedulded_tokens]);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => false, 'message' => "Internal Server Error"]);
    //     }
    // }


    public function getTokens(Request $request)
{
    $rules = [
        'doctor_id'     => 'required',
        'hospital_id'   => 'required',
        'date'          => 'required',
    ];
    $messages = [
        'date.required' => 'Date is required',
    ];
    $validation = Validator::make($request->all(), $rules, $messages);
    if ($validation->fails()) {
        return response()->json(['status' => false, 'response' => $validation->errors()->first()]);
    }

    try {
        $doctor = Docter::where('id', $request->doctor_id)->first();
        if (!$doctor) {
            return response()->json(['status' => false, 'message' => 'Doctor not found']);
        }

        $scheduledTokens = schedule::where('docter_id', $request->doctor_id)
            ->where('hospital_Id', $request->hospital_id)
            ->first();

        if (!$scheduledTokens) {
            return response()->json(['status' => false, 'message' => 'Data not found']);
        }

        $requestDate = Carbon::parse($request->date);
        $startDate = Carbon::parse($scheduledTokens->date);
        $scheduledUptoDate = Carbon::parse($scheduledTokens->scheduleupto);

        // Get the day of the week
        $dayOfWeek = $requestDate->format('l'); // 'l' format gives the full name of the day
        $allowedDaysArray = json_decode($scheduledTokens->selecteddays);
        $tokenBooking = TokenBooking::where('date', $request->date)
            ->where('doctor_id', $request->doctor_id)
            ->where('clinic_id', $request->hospital_id)
            ->get();

        if (!$requestDate->between($startDate, $scheduledUptoDate)) {
            return response()->json(['status' => true, 'token_data' => null, 'message' => 'Token not found on this date']);
        }

        if (!in_array($dayOfWeek, $allowedDaysArray)) {
            return response()->json(['status' => true, 'token_data' => null, 'message' => 'Token not found on this day']);
        }

        $scheduledTokens = schedule::select('id', 'tokens', 'date', 'hospital_Id', 'startingTime', 'endingTime')
            ->where('docter_id', $request->doctor_id)
            ->where('hospital_Id', $request->hospital_id)
            ->first();

        $scheduledTokens['tokens'] = json_decode($scheduledTokens->tokens);

        $todaySchedule = TodaySchedule::select('id', 'tokens', 'date', 'hospital_Id')
            ->where('docter_id', $request->doctor_id)
            ->where('hospital_Id', $request->hospital_id)
            ->where('date', $request->date)
            ->first();

        if ($todaySchedule) {
            $todaySchedule['startingTime'] = $scheduledTokens->startingTime;
            $todaySchedule['endingTime']   = $scheduledTokens->endingTime;
            $scheduledTokens = $todaySchedule;
            $scheduledTokens['tokens'] = json_decode($todaySchedule->tokens);
        }

        $morningTokens = [];
        $eveningTokens = [];

        foreach ($scheduledTokens['tokens'] as $token) {
            // Set is_booked to 1 (or any other value you want)
            $tokenBooking = TokenBooking::where('date', $request->date)
                ->where('doctor_id', $request->doctor_id)
                ->where('clinic_id', $request->hospital_id)
                ->where('TokenTime', $token->Time)
                ->where('TokenNumber', $token->Number)
                ->first();

            $token->is_booked = $tokenBooking ? 1 : 0;

            // Categorize tokens into morning and evening
            if (Carbon::parse($token->Time) < Carbon::parse('13:00:00')) {
                $morningTokens[] = $token;
            } else {
                $eveningTokens[] = $token;
            }
        }
        $token_Data = new \stdClass(); // Create a new object to store token data
        $token_Data->morning_tokens = $morningTokens;
        $token_Data->evening_tokens = $eveningTokens;

        return response()->json([
            'status' => true,
            'token_data' => $token_Data,
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => "Internal Server Error"]);
    }
}

    public function getDoctorLeaveList(Request $request)
    {
        $rules = [
            'doctor_id'     => 'required',
            'hospital_id'   => 'required',
        ];
        $messages = [
            'doctor_id.required' => 'Docter is required',
        ];
        $validation = Validator::make($request->all(), $rules, $messages);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'response' => $validation->errors()->first()]);
        }
        try {
            $leaves = DocterLeave::select('id', 'docter_id', 'hospital_id', 'date')->where('docter_id', $request->doctor_id)->where('hospital_id', $request->hospital_id)->get();
            if (!$leaves) {
                return response()->json(['status' => true, 'leaves_data' => null, 'message' => 'No leaves.']);
            }
            return response()->json(['status' => true, 'leaves_data' => $leaves, 'message' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => "Internal Server Error"]);
        }
    }

    public function leaveUpdate(Request $request)
    {
        $rules = [
            'doctor_id'     => 'required',
            'hospital_id'   => 'required',
            'date'          => 'required',
        ];
        $messages = [
            'date.required' => 'Date is required',
        ];
        $validation = Validator::make($request->all(), $rules, $messages);
        if ($validation->fails()) {
            return response()->json(['status' => false, 'response' => $validation->errors()->first()]);
        }
        try {
            $docter = Docter::where('id', $request->doctor_id)->first();
            if (!$docter) {
                return response()->json(['status' => false, 'message' => 'Doctor not found']);
            }
            $token_booked = TokenBooking::where('date', $request->date)->where('doctor_id', $request->doctor_id)->where('clinic_id', $request->hospital_id)->first();

            if ($token_booked) {
                return response()->json(['status' => false, 'message' => 'Already bookings in this date']);
            }
            $leave = DocterLeave::where('docter_id', $request->doctor_id)->where('hospital_id', $request->hospital_id)->where('date', $request->date)->first();
            if ($leave) {
                DocterLeave::where('docter_id', $request->doctor_id)->where('hospital_id', $request->hospital_id)->where('date', $request->date)->delete();
            } else {
                $leave = new DocterLeave();
                $leave->date = $request->date;
                $leave->hospital_id = $request->hospital_id;
                $leave->docter_id   = $request->doctor_id;
                $leave->save();
            }
            return response()->json(['status' => true, 'message' => 'leave updated.']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => "Internal Server Error"]);
        }
    }



    public function searchDoctor(Request $request)
    {


        // Search for doctors by name
        $doctors = Docter::where(function ($query) use ($request) {
            $query->where('firstname', 'LIKE', '%' . $request->name . '%')
                  ->orWhere('lastname', 'LIKE', '%' . $request->name . '%');
        })->get();

        // Check if any doctors were found
        if ($doctors->isEmpty()) {

            return $this->sendResponse("Docters", null, '1', 'No doctors found with the given name.');
        }

        // Transform the doctors' data
        $doctorsWithSpecifications = $doctors->map(function ($doctor) {
            $specializeArray['specialize'] = Specialize::all();
            $specialize = $specializeArray['specialize']->firstWhere('id', $doctor['specialization_id']);
            return [
                'id' => $doctor->id,
                'UserId' => $doctor->UserId,
                'firstname' => $doctor->firstname,
                'secondname' => $doctor->lastname,
                'Specialization' => $specialize ? $specialize['specialization'] : null,
                'DocterImage' => asset("DocterImages/images/{$doctor->docter_image}"),
                'Location' => $doctor->location,
                'MainHospital' => $doctor->Services_at,
            ];
        });

        return $this->sendResponse("Docters", $doctorsWithSpecifications, '1', 'Docters retrieved successfully.');
    }

}
