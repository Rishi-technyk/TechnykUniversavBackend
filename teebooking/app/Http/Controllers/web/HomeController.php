<?php



namespace App\Http\Controllers\web;



use App\CPU\Helpers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Session;

use App\Models\User;

use App\Models\TeeSheet;

use App\Models\TeeGroup;

use App\Models\TeeBookingDetails;

use App\Models\RentalClub;

use App\Models\TeeHole;

use App\Models\TeeSession;

use App\Models\SmsModel;

use Illuminate\Support\Facades\Auth;

use DB;

use DateTime;

use Carbon\Carbon;

use App\Mail\TeeSendMail;

use App\Mail\BookingCancelMail;



use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Collection;











class HomeController extends Controller

{



    public function index(Request $request)

    {


        
        $smsModel = new SmsModel();

        ///echo $otp =   $smsModel->sendSms('9549103767');

        //echo $otp =   $smsModel->sendBookingSms('9549103767');

/// die();

        /* $data = TeeBookingDetails::get_booking_details(15);

          $email = "sohanbairwa2021@gmail.com";

           

           

            Mail::to($email)->send(new TeeSendMail($data));

            return new JsonResponse(

                [

                    'success' => true, 

                    'message' => "Thank you for subscribing to our email, please check your inbox"

                ], 

                200

            );*/







        // die();

        $date = $request->date;

        if (!$date) {

            $currentDate = now();

            $timelineDate = $currentDate->copy();

            $date = $timelineDate->modify("+1 day");

            $date = $timelineDate->format('Y-m-d');

        }



        $teeSheetStartTime = TeeSheet::leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')

            ->where('tee_booking.booking_date', $date)->first();





        if ($teeSheetStartTime) {

            $teeTime = $teeSheetStartTime->tee_time;

        } else {

            $teeTime = '09:00:00'; // Set a default value

        }

        $teeTime = '06:00:00';

        // dd( $teeSheetStartTime);



        $currentDateTime = new DateTime();

        // $specifiedDateTime = new DateTime($date . '06:00:00');

        //dd(Helpers::get_setting('booking_start_time'),);

        // $specifiedDateTime = new DateTime($date . Helpers::get_setting('booking_start_time'));

        $specifiedDateTime = new DateTime($date . $teeTime);



        $teeSessions = TeeSession::active()->get();





        // Check if the specified date is a Monday

        $teeSheets = [];

        $selectedHole = $request->teeHole;



        $selectedSession = $request->session_name;

        $selectedSessions = TeeSession::pluck('id')->toArray();

        if ($selectedSession && $selectedSession !== 'All Day') {

            $selectedSessions = [$request->session_name];

        }



        $show_time = $request->show_time;

        $notesMessage = "";

        $currentDateMessage = "";

        $windowMessage = "";

        $loggedInMemberId = "";



        if ($specifiedDateTime->format('N') === '2') {

            $notesMessage = "<span class='text-danger'>The window is closed on Tuesday.</span>";

        } else {

            // Calculate 36 hours before the specified date and time

            $windowStart = clone $specifiedDateTime;

            $HBB = Helpers::get_setting('hour_before_booking');

            // $windowStart->modify('-36 hours');

            $windowStart->modify('-' . $HBB . ' hours');



            // Calculate 16 hours after the specified date and time

            $windowEnd = clone $windowStart;

            $HBR = Helpers::get_setting('hour_booking_range');

            // $windowEnd->modify('+16 hours');

            $windowEnd->modify('+' . $HBR . ' hours');



            $currentDateMessage = "Current date and time: " . $currentDateTime->format('d-m-Y H:i:s') . "<br>\n";





            // dd($currentDateTime, $windowStart, $windowEnd, $currentDateTime >= $windowStart, $currentDateTime <= $windowEnd);

            if ($currentDateTime >= $windowStart && $currentDateTime <= $windowEnd) {

                $tableObj = TeeSheet::select(

                    'tee_sheet.*',

                    'tee_holes.hole_number'

                )

                    ->active()

                    ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')

                    ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')

                    ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')

                    ->where('tee_booking.booking_date', $date);

                /*->where(function($query) {

                    // Conditionally add where clause if tee_booking_details records exist

                    $query->where(function($subquery) {

                        $subquery->where('tbd.is_cancelled', 0)

                                  ->orWhereNull('tbd.id'); // Allow tee_booking_details to be null

                    })

                    ->orWhereHas('teeBookingDetails', function($subquery) {

                        // Add a condition for tee_booking_details existence

                        $subquery->where('is_cancelled', 0);

                    });

                });*/

                if ($selectedHole) {

                    $tableObj->where("tee_sheet.tee_off_hole_id", $selectedHole);

                }

                $loggedInUser = Auth::user();

                $loggedInMemberId = $loggedInUser->id;

                // dd($loggedInMemberId);



                //dd($tableObj->get());

                if (!empty($selectedSessions)) {

                    $tableObj->where(function ($query) use ($selectedSessions, $loggedInMemberId) {

                        foreach ($selectedSessions as $session) {

                            $query->orWhere(function ($subquery) use ($session, $loggedInMemberId) {

                                $subquery->where('tee_sessions.id', $session)

                                    ->whereExists(function ($innerSubquery) use ($loggedInMemberId) {

                                        $innerSubquery->select(DB::raw(1))

                                            ->from('tee_session_categories')

                                            ->leftJoin('memberprofile', 'memberprofile.CategoryTypeCode', '=', 'tee_session_categories.category_type_Code')

                                            ->whereRaw('tee_session_categories.tee_session_id = tee_sessions.id')

                                            ->whereRaw('memberprofile.id = ?', [$loggedInMemberId]);

                                    });

                            });

                        }

                    });

                }



                $teeSheets = $tableObj->orderBy('tee_sheet.id')->get();

                // dd($teeSheets);

                foreach ($teeSheets as $key => $value) {



                    $data = TeeSheet::select(

                        'tbd.id as tee_booking_detail_id',

                        'tbd.bookingId',

                        'tbd.player1_id',

                        'tbd.player2_id',

                        'tbd.player3_id',

                        'tbd.player4_id',

                        'tbd.created_at as booking_date',

                        'mp1.MemberID as player1_member_id',

                        'mp2.MemberID as player2_member_id',

                        'mp3.MemberID as player3_member_id',

                        'mp4.MemberID as player4_member_id',

                        'mp1.DisplayName as player1_name',

                        'mp2.DisplayName as player2_name',

                        'mp3.DisplayName as player3_name',

                        'mp4.DisplayName as player4_name',

                        DB::raw('(4 - 

                CASE 

                    WHEN tbd.player1_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 

                    ELSE 0 

                END -

                CASE 

                    WHEN tbd.player2_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 

                    ELSE 0 

                END -

                CASE 

                    WHEN tbd.player3_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 

                    ELSE 0 

                END -

                CASE 

                    WHEN tbd.player4_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 

                    ELSE 0 

                END) AS available_players')

                    )

                        ->active()

                        ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')

                        ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')

                        ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')

                        ->leftJoin('tee_booking_details as tbd', 'tbd.tee_sheet_id', '=', 'tee_sheet.id')

                        ->leftJoin('memberprofile as mp1', 'mp1.id', '=', 'tbd.player1_id')

                        ->leftJoin('memberprofile as mp2', 'mp2.id', '=', 'tbd.player2_id')

                        ->leftJoin('memberprofile as mp3', 'mp3.id', '=', 'tbd.player3_id')

                        ->leftJoin('memberprofile as mp4', 'mp4.id', '=', 'tbd.player4_id')

                        ->where('tbd.is_cancelled', 0)

                        ->where('tbd.tee_sheet_id', $value['id'])->first();

                    $nullObject = json_decode(json_encode([

                        'tee_booking_detail_id' => null,

                        'bookingId' => null,

                        'player1_id' => null,

                        'player2_id' => null,

                        'player3_id' => null,

                        'player4_id' => null,

                        'booking_date' => null,

                        'player1_member_id' => null,

                        'player2_member_id' => null,

                        'player3_member_id' => null,

                        'player4_member_id' => null,

                        'player1_name' => null,

                        'player2_name' => null,

                        'player3_name' => null,

                        'player4_name' => null,

                        'available_players' => 4,

                    ]), false);

                    $teeSheets[$key]['data'] = $data ?? $nullObject;



                }

            }



            // dd($teeSheets);

            // dd($currentDateTime, $windowStart, $currentDateTime, $windowEnd, count($teeSheets));



            if ($currentDateTime >= $windowStart && $currentDateTime <= $windowEnd && count($teeSheets) > 0) {

                $notesMessage = "<span class='text-success'>The window is open.</span>";

                $windowMessage .= "The window will starts at: " . $windowStart->format('d-m-Y H:i') . "<br>";

                $windowMessage .= "The window will ends at: " . $windowEnd->format('d-m-Y H:i');

            } else {

                $notesMessage = "<span class='text-danger'>The window is closed.</span>";

            }

        }



        $rentalClubs = RentalClub::active()->get();

        $teeHoles = TeeHole::where("is_active", 1)->get();



        $selectedDate = $date;

        // dd($loggedInMemberId);

        $is_booking_exist = TeeBookingDetails::join('tee_booking', 'tee_booking.id', '=', 'tee_booking_details.tee_booking_id')

            ->where(function ($query) use ($loggedInMemberId) {

                $query->where('player1_id', $loggedInMemberId)

                    ->orWhere('player2_id', $loggedInMemberId)

                    ->orWhere('player3_id', $loggedInMemberId)

                    ->orWhere('player4_id', $loggedInMemberId);

            })

            ->where('tee_booking_details.is_cancelled', 0)

            ->where('tee_booking.booking_date', $date)

            ->exists();



        return view('website.home', compact('rentalClubs', 'teeSheets', 'selectedDate', 'teeHoles', 'teeSessions', 'selectedHole', 'selectedSession', 'notesMessage', 'windowMessage', 'is_booking_exist'));

    }

    public function index_old(Request $request)

    {



        $smsModel = new SmsModel();

        //echo $otp =   $smsModel->sendSms('9549103767');

//       echo $otp =   $smsModel->sendBookingSms('9549103767');

// die();

        /* $data = TeeBookingDetails::get_booking_details(15);

          $email = "sohanbairwa2021@gmail.com";

           

           

            Mail::to($email)->send(new TeeSendMail($data));

            return new JsonResponse(

                [

                    'success' => true, 

                    'message' => "Thank you for subscribing to our email, please check your inbox"

                ], 

                200

            );*/







        // die();

        $date = $request->date;

        if (!$date) {

            $currentDate = now();

            $timelineDate = $currentDate->copy();

            $date = $timelineDate->modify("+1 day");

            $date = $timelineDate->format('Y-m-d');

        }



        $teeSheetStartTime = TeeSheet::leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')

            ->where('tee_booking.booking_date', $date)->first();





        if ($teeSheetStartTime) {

            $teeTime = $teeSheetStartTime->tee_time;

        } else {

            $teeTime = '09:00:00'; // Set a default value

        }



        // dd( $teeSheetStartTime);



        $currentDateTime = new DateTime();

        // $specifiedDateTime = new DateTime($date . '06:00:00');

        //dd(Helpers::get_setting('booking_start_time'),);

        // $specifiedDateTime = new DateTime($date . Helpers::get_setting('booking_start_time'));

        $specifiedDateTime = new DateTime($date . $teeTime);



        $teeSessions = TeeSession::active()->get();





        // Check if the specified date is a Monday

        $teeSheets = [];

        $selectedHole = $request->teeHole;



        $selectedSession = $request->session_name;

        $selectedSessions = TeeSession::pluck('id')->toArray();

        if ($selectedSession && $selectedSession !== 'All Day') {

            $selectedSessions = [$request->session_name];

        }



        $show_time = $request->show_time;

        $notesMessage = "";

        $currentDateMessage = "";

        $windowMessage = "";



        if ($specifiedDateTime->format('N') === '2') {

            $notesMessage = "<span class='text-danger'>The window is closed on Tuesday.</span>";

        } else {

            // Calculate 36 hours before the specified date and time

            $windowStart = clone $specifiedDateTime;

            $HBB = Helpers::get_setting('hour_before_booking');

            // $windowStart->modify('-36 hours');

            $windowStart->modify('-' . $HBB . ' hours');



            // Calculate 16 hours after the specified date and time

            $windowEnd = clone $windowStart;

            $HBR = Helpers::get_setting('hour_booking_range');

            // $windowEnd->modify('+16 hours');

            $windowEnd->modify('+' . $HBR . ' hours');



            $currentDateMessage = "Current date and time: " . $currentDateTime->format('d-m-Y H:i:s') . "<br>\n";





            // dd($currentDateTime, $windowStart, $windowEnd, $currentDateTime >= $windowStart, $currentDateTime <= $windowEnd);

            if ($currentDateTime >= $windowStart && $currentDateTime <= $windowEnd) {

                $tableObj = TeeSheet::select(

                    'tee_sheet.*',

                    'tee_holes.hole_number',

                    'tbd.id as tee_booking_detail_id',

                    'tbd.bookingId',

                    'tbd.player1_id',

                    'tbd.player2_id',

                    'tbd.player3_id',

                    'tbd.player4_id',

                    'tbd.created_at as booking_date',

                    'mp1.MemberID as player1_member_id',

                    'mp2.MemberID as player2_member_id',

                    'mp3.MemberID as player3_member_id',

                    'mp4.MemberID as player4_member_id',

                    'mp1.DisplayName as player1_name',

                    'mp2.DisplayName as player2_name',

                    'mp3.DisplayName as player3_name',

                    'mp4.DisplayName as player4_name',

                    DB::raw('(4 - 

            CASE 

                WHEN tbd.player1_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 

                ELSE 0 

            END -

            CASE 

                WHEN tbd.player2_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 

                ELSE 0 

            END -

            CASE 

                WHEN tbd.player3_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 

                ELSE 0 

            END -

            CASE 

                WHEN tbd.player4_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 

                ELSE 0 

            END) AS available_players')

                )

                    ->active()

                    ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')

                    ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')

                    ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')

                    ->leftJoin('tee_booking_details as tbd', 'tbd.tee_sheet_id', '=', 'tee_sheet.id')

                    ->leftJoin('memberprofile as mp1', 'mp1.id', '=', 'tbd.player1_id')

                    ->leftJoin('memberprofile as mp2', 'mp2.id', '=', 'tbd.player2_id')

                    ->leftJoin('memberprofile as mp3', 'mp3.id', '=', 'tbd.player3_id')

                    ->leftJoin('memberprofile as mp4', 'mp4.id', '=', 'tbd.player4_id')

                    ->where('tee_booking.booking_date', $date);

                /*->where(function($query) {

                    // Conditionally add where clause if tee_booking_details records exist

                    $query->where(function($subquery) {

                        $subquery->where('tbd.is_cancelled', 0)

                                  ->orWhereNull('tbd.id'); // Allow tee_booking_details to be null

                    })

                    ->orWhereHas('teeBookingDetails', function($subquery) {

                        // Add a condition for tee_booking_details existence

                        $subquery->where('is_cancelled', 0);

                    });

                });*/



                if ($selectedHole) {

                    $tableObj->where("tee_sheet.tee_off_hole_id", $selectedHole);

                }

                $loggedInUser = Auth::user();

                $loggedInMemberId = $loggedInUser->id;



                dd($tableObj->get());

                if (!empty($selectedSessions)) {

                    $tableObj->where(function ($query) use ($selectedSessions, $loggedInMemberId) {

                        foreach ($selectedSessions as $session) {

                            $query->orWhere(function ($subquery) use ($session, $loggedInMemberId) {

                                $subquery->where('tee_sessions.id', $session)

                                    ->whereExists(function ($innerSubquery) use ($loggedInMemberId) {

                                        $innerSubquery->select(DB::raw(1))

                                            ->from('tee_session_categories')

                                            ->leftJoin('memberprofile', 'memberprofile.CategoryTypeCode', '=', 'tee_session_categories.category_type_Code')

                                            ->whereRaw('tee_session_categories.tee_session_id = tee_sessions.id')

                                            ->whereRaw('memberprofile.id = ?', [$loggedInMemberId]);

                                    });

                            });

                        }

                    });

                }



                $teeSheets = $tableObj->orderBy('tee_sheet.id')->get();



            }

            //dd( $loggedInUser);

            //dd($currentDateTime, $windowStart, $currentDateTime, $windowEnd, count($teeSheets));



            if ($currentDateTime >= $windowStart && $currentDateTime <= $windowEnd && count($teeSheets) > 0) {

                $notesMessage = "<span class='text-success'>The window is open.</span>";

                $windowMessage .= "The window will starts at: " . $windowStart->format('d-m-Y H:i') . "<br>";

                $windowMessage .= "The window will ends at: " . $windowEnd->format('d-m-Y H:i');

            } else {

                $notesMessage = "<span class='text-danger'>The window is closed.</span>";

            }





        }



        $rentalClubs = RentalClub::active()->get();

        $teeHoles = TeeHole::where("is_active", 1)->get();









        $selectedDate = $date;



        return view('website.home', compact('rentalClubs', 'teeSheets', 'selectedDate', 'teeHoles', 'teeSessions', 'selectedHole', 'selectedSession', 'notesMessage', 'windowMessage'));

    }



    public function cancel_booking(Request $request)

    {

        $id = $request->id;

        // $id = '1056';

        

        // $teeBooking = TeeBookingDetails::find($id);

        // $teeBooking->cancelled_by = '15479';

        // $teeBooking->is_cancelled = 1;

        // $teeBooking->cancelled_at = Carbon::now();

        // //$teeBooking->cancelled_at = 

        // $teeBooking->save();

        

        // $data = TeeBookingDetails::get_booking_details($id);

        // $email = 'amrishcool82@gmail.com';

        // $subject = "Tee Booking Cancellation";

        // $data->booking_type = "cancelled";

        // Mail::to($email)->send(new BookingCancelMail([], $subject));

        // dd('cancel');

        // die;





        $teeBooking = TeeBookingDetails::find($id);

        $teeBooking->cancelled_by = Auth()->user()->id;

        $teeBooking->is_cancelled = 1;

        $teeBooking->cancelled_at = Carbon::now();

        //$teeBooking->cancelled_at = 

        $teeBooking->save();

        $data = TeeBookingDetails::get_booking_details($id);

        $email =  Auth()->user()->Email;

        $subject = "Tee Booking Cancellation";

        $data->booking_type = "cancelled";

        // Mail::to($email)->send(new BookingCancelMail($data, $subject));





        return redirect()->route('home')->with('success', 'Booking cancelled successfully');

    }

    public function login(Request $request)

    {

        $request->validate([

            'email' => 'required|email',

            'password' => 'required',

        ]);



        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {

            return redirect()->route('admin.dashboard')

                ->with('success', 'Signed in!');

        }



        return redirect()->route('admin.login')->with('error', 'Login credentials are not valid!');

    }



    public function lock_tee_booking(Request $request)

    {

        $tee_sheet_id = $request->tee_sheet_id;



         $tBDBooked = TeeBookingDetails::where("tee_sheet_id", $tee_sheet_id)->whereNotNull('player1_id')->where("is_cancelled",0)->whereNot('created_by',Auth()->user()->id)->first();

        //$tBD1 = TeeBookingDetails::where("tee_sheet_id",$tee_sheet_id)->whereNull('player1_id')->whereNotNull("is_cancelled")->first();



        if ($tBDBooked) {

            return response()->json(['error' => 'This slot is already booked'], 400);

        }



        // Calculate the timestamp for 3 minutes ago

        $threeMinutesAgo = now()->subMinutes(1);

        TeeBookingDetails::whereNull('player1_id')->where('locked_by', Auth()->user()->id)->delete();

       

        // Delete records older than 3 minutes

        TeeBookingDetails::where("tee_sheet_id", $tee_sheet_id)->whereNull('player1_id')->where('locked_till', '<', $threeMinutesAgo)->whereNotNull('locked_by')->delete();





        $validator = $request->validate([

            'tee_sheet_id' => 'required',



        ]);



        $tBD = TeeBookingDetails::where("tee_sheet_id", $tee_sheet_id)->whereNull('player1_id')->whereNotNull("locked_by")->first();

        //$tBD1 = TeeBookingDetails::where("tee_sheet_id",$tee_sheet_id)->whereNull('player1_id')->whereNotNull("is_cancelled")->first();



        if ($tBD) {

            return response()->json(['error' => 'This slot is under booking'], 400);

        } else {



            $tBD = TeeBookingDetails::where("tee_sheet_id", $tee_sheet_id)->where('is_cancelled', 1)->first();

            if ($tBD) {

                TeeBookingDetails::create(

                    [

                        'tee_sheet_id' => $tee_sheet_id,

                        'tee_booking_id' => $request->tee_booking_id,

                        'locked_till' => now(),

                        'locked_by' => Auth()->user()->id



                    ]

                   

                );



            } else {



                TeeBookingDetails::updateOrCreate(

                    [

                        'tee_sheet_id' => $tee_sheet_id,

                        'tee_booking_id' => $request->tee_booking_id

                    ],

                    array_merge($validator, [

                        'locked_till' => now(),

                        'locked_by' => Auth()->user()->id,

                    ])

                );

            }



        }



    }



    public function store_tee_booking(Request $request)

    {

        $validator = $request->validate([

            'tee_booking_id' => 'required',

            'tee_sheet_id' => 'required',

            'player1_id' => 'required|different:player2_id,player3_id,player4_id',

            'player2_id' => 'required|different:player1_id,player3_id,player4_id',

            'player3_id' => 'required|different:player1_id,player2_id,player4_id',

            'player4_id' => 'nullable|different:player1_id,player2_id,player3_id',

        ], [



            'player1_id.required' => 'Player 1 required for Tee Booking.',

            'player2_id.required' => 'Player 2 required for Tee Booking.',

            'player3_id.required' => 'Player 3 required for Tee Booking.',

            'player4_id.required' => 'Player 4 required for Tee Booking.',

            'player1_id.different' => 'Player 1 must be different from other players.',

            'player2_id.different' => 'Player 2 must be different from other players.',

            'player3_id.different' => 'Player 3 must be different from other players.',

            'player4_id.different' => 'Player 4 must be different from other players.',



        ]);



        $playerId1 = $request->input("player1_id");

        $playerId2 = $request->input("player2_id");



        if ($playerId1 == $playerId2) {

            return response()->json(['Player 3 already selected as Player1'], 400);

        }



        $tBDBooked = TeeBookingDetails::where("tee_sheet_id",$request->input("tee_sheet_id"))->whereNotNull('player1_id')->where("is_cancelled",0)->whereNot('created_by',Auth()->user()->id)->first();

        //$tBD1 = TeeBookingDetails::where("tee_sheet_id",$tee_sheet_id)->whereNull('player1_id')->whereNotNull("is_cancelled")->first();



        if ($tBDBooked) {

            return response()->json(['error' => 'This slot is already booked'], 400);

        }









        $playerIds = array_filter($request->only('player1_id', 'player2_id', 'player3_id', 'player4_id'));



        if (count($playerIds) < 3) {

            return response()->json(['error' => 'At least 3 players are required.'], 400);

        }

        $playerIds = [

            'player1_id',

            'player2_id',

            'player3_id',

            'player4_id'

        ];

        $teeBookingDetailId = $request->tee_booking_detail_id;

        foreach ($playerIds as $index => $playerIdField) {

            if ($request->has($playerIdField)) {

                $playerId = $request->input($playerIdField);

                if ($playerId) {

                    // $bookingExists = TeeBookingDetails::whereDate('created_at', now()->toDateString())

                    $bookingExists = TeeBookingDetails::where('tee_booking_id', $request->tee_booking_id)

                        ->whereNot("id", $teeBookingDetailId)

                        ->where("is_cancelled", 0)

                        ->where(function ($query) use ($playerId) {

                            $query->where('player1_id', $playerId)

                                ->orWhere('player2_id', $playerId)

                                ->orWhere('player3_id', $playerId)

                                ->orWhere('player4_id', $playerId);

                        })

                        ->exists();



                    if ($bookingExists) {

                        $playerNumber = $index + 1; // Get the player number (1-indexed)

                        return response()->json(['error' => "Player $playerNumber has already booked a slot on the same day."], 400);

                    }

                }

            }

        }

        $booking_type = "";

        if ($teeBookingDetailId) {

            $subject = "Tee Booking Modification";

            $booking_type = "modified";

        } else {

            $subject = "Tee Booking Confirmation";

            $booking_type = "confirmed";

        }

        TeeBookingDetails::whereNull('player1_id')->where('locked_by', Auth()->user()->id)->delete();





        $teeBookingDetail = TeeBookingDetails::updateOrCreate(

            ['id' => $teeBookingDetailId],

            array_merge($validator, [

                'created_by' => auth()->user()->id,

                'locked_by' => ''

            ])

        );



        // Get the id after the updateOrCreate operation

        $teeBookingDetailId = $teeBookingDetail->id;

        $bookingId = "TB" . str_pad($teeBookingDetailId, 8, '0', STR_PAD_LEFT);

        $TBD = TeeBookingDetails::find($teeBookingDetailId);

        $TBD->bookingId = $bookingId;

        $TBD->save();











        $data = TeeBookingDetails::get_booking_details($teeBookingDetailId);

        $email = Auth()->user()->Email;

        //$email = "sohanbairwa2021@gmail.com";

        $data->booking_type = $booking_type;

        // Mail::to($email)->send(new TeeSendMail($data, $subject));







        return response()->json(['message' => 'TeeBookingDetails created successfully']);

    }

    public function store_group_booking(Request $request)

    {



        $reeGroup = TeeGroup::find($request->group_id);



      

        //group_id

       /* $validator = $request->validate([

            'tee_booking_id' => 'required',

            'tee_sheet_id' => 'required',

            'player1_id' => 'required|different:player2_id,player3_id,player4_id',

            'player2_id' => 'required|different:player1_id,player3_id,player4_id',

            'player3_id' => 'required|different:player1_id,player2_id,player4_id',

            'player4_id' => 'nullable|different:player1_id,player2_id,player3_id',

        ], [



            'player1_id.required' => 'Player 1 required for Tee Booking.',

            'player2_id.required' => 'Player 2 required for Tee Booking.',

            'player3_id.required' => 'Player 3 required for Tee Booking.',

            'player4_id.required' => 'Player 4 required for Tee Booking.',

            'player1_id.different' => 'Player 1 must be different from other players.',

            'player2_id.different' => 'Player 2 must be different from other players.',

            'player3_id.different' => 'Player 3 must be different from other players.',

            'player4_id.different' => 'Player 4 must be different from other players.',



        ]);*/

        

       

        $playerId1 = $reeGroup->player1_id;

        $playerId2 = $reeGroup->player2_id;

        $playerId3 = $reeGroup->player3_id;

        $playerId4 = $reeGroup->player4_id;

        $validator = array(

            "player1_id"=>$playerId1,

            "player2_id"=>$playerId2,

            "player3_id"=>$playerId3,

            "player4_id"=>$playerId4,

            "tee_booking_id"=>$request->input("tee_booking_id"),

            "tee_sheet_id"=>$request->input("tee_sheet_id"),

        );

       



        if ($playerId1 == $playerId2) {

            return response()->json(['Player 3 already selected as Player1'], 400);

        }



        $tBDBooked = TeeBookingDetails::where("tee_sheet_id",$request->input("tee_sheet_id"))->whereNotNull('player1_id')->where("is_cancelled",0)->whereNot('created_by',Auth()->user()->id)->first();

        //$tBD1 = TeeBookingDetails::where("tee_sheet_id",$tee_sheet_id)->whereNull('player1_id')->whereNotNull("is_cancelled")->first();



        if ($tBDBooked) {

            return response()->json(['error' => 'This slot is already booked'], 400);

        }









        $playerIds = array_filter([$playerId1,$playerId2,$playerId3,$playerId4]);



        if (count($playerIds) < 3) {

            return response()->json(['error' => 'At least 3 players are required.'], 400);

        }

        $playerIds = [

            'player1_id',

            'player2_id',

            'player3_id',

            'player4_id'

        ];

        $teeBookingDetailId = $request->tee_booking_detail_id;

        foreach ($playerIds as $index => $playerIdField) {

            if ($validator[$playerIdField]) {

                $playerId = $validator[$playerIdField];

                if ($playerId) {

                    // $bookingExists = TeeBookingDetails::whereDate('created_at', now()->toDateString())

                    $bookingExists = TeeBookingDetails::where('tee_booking_id', $request->tee_booking_id)

                        ->whereNot("id", $teeBookingDetailId)

                        ->where("is_cancelled", 0)

                        ->where(function ($query) use ($playerId) {

                            $query->where('player1_id', $playerId)

                                ->orWhere('player2_id', $playerId)

                                ->orWhere('player3_id', $playerId)

                                ->orWhere('player4_id', $playerId);

                        })

                        ->exists();



                    if ($bookingExists) {

                        $playerNumber = $index + 1; // Get the player number (1-indexed)

                        return response()->json(['error' => "Player $playerNumber has already booked a slot on the same day."], 400);

                    }

                }

            }

        }

        $booking_type = "";

        if ($teeBookingDetailId) {

            $subject = "Tee Booking Modification";

            $booking_type = "modified";

        } else {

            $subject = "Tee Booking Confirmation";

            $booking_type = "confirmed";

        }

        TeeBookingDetails::whereNull('player1_id')->where('locked_by', Auth()->user()->id)->delete();





        $teeBookingDetail = TeeBookingDetails::updateOrCreate(

            ['id' => $teeBookingDetailId],

            [

                "player1_id"=>$playerId1,

                "player2_id"=>$playerId2,

                "player3_id"=>$playerId3,

                "player4_id"=>$playerId4,

                "tee_booking_id"=>$request->tee_booking_id,

                "tee_sheet_id"=>$request->tee_sheet_id,

                'created_by' => auth()->user()->id,

                'locked_by' => ''

            ]

        );



        // Get the id after the updateOrCreate operation

        $teeBookingDetailId = $teeBookingDetail->id;

        $bookingId = "TB" . str_pad($teeBookingDetailId, 8, '0', STR_PAD_LEFT);

        $TBD = TeeBookingDetails::find($teeBookingDetailId);

        $TBD->bookingId = $bookingId;

        $TBD->save();











        $data = TeeBookingDetails::get_booking_details($teeBookingDetailId);

        $email = Auth()->user()->Email;

        //$email = "sohanbairwa2021@gmail.com";

        $data->booking_type = $booking_type;

        // Mail::to($email)->send(new TeeSendMail($data, $subject));



        return response()->json(['message' => 'TeeBookingDetails created successfully']);

    }

    public function store_buddy_booking(Request $request)

    {





        if (!isset($request->buddy_id) || count($request->buddy_id) < 2) {

            return response()->json(['error' => 'Please select at least 2 buddies'], 400);

        }



       

       

        $playerId1 = auth()->user()->id;

        if(isset($request->buddy_id[0])) {

            $playerId2 = $request->buddy_id[0];

        } else {

            $playerId2 = null; 

        }

        

        if(isset($request->buddy_id[1])) {

            $playerId3 = $request->buddy_id[1];

        } else {

            $playerId3 = null; 

        }

        

        if(isset($request->buddy_id[2])) {

            $playerId4 = $request->buddy_id[2];

        } else {

            $playerId4 = null; 

        }

        $validator = array(

            "player1_id"=>$playerId1,

            "player2_id"=>$playerId2,

            "player3_id"=>$playerId3,

            "player4_id"=>$playerId4,

            "tee_booking_id"=>$request->input("tee_booking_id"),

            "tee_sheet_id"=>$request->input("tee_sheet_id"),

        );

       



        if ($playerId1 == $playerId2) {

            return response()->json(['Player 3 already selected as Player1'], 400);

        }



        $tBDBooked = TeeBookingDetails::where("tee_sheet_id",$request->input("tee_sheet_id"))->whereNotNull('player1_id')->where("is_cancelled",0)->whereNot('created_by',Auth()->user()->id)->first();

        //$tBD1 = TeeBookingDetails::where("tee_sheet_id",$tee_sheet_id)->whereNull('player1_id')->whereNotNull("is_cancelled")->first();



        if ($tBDBooked) {

            return response()->json(['error' => 'This slot is already booked'], 400);

        }









        $playerIds = array_filter([$playerId1,$playerId2,$playerId3,$playerId4]);



        if (count($playerIds) < 2) {

            return response()->json(['error' => 'At least 2 players are required.'], 400);

        }

        $playerIds = [

            'player1_id',

            'player2_id',

            'player3_id',

            'player4_id'

        ];

        $teeBookingDetailId = $request->tee_booking_detail_id;

        foreach ($playerIds as $index => $playerIdField) {

            if ($validator[$playerIdField]) {

                $playerId = $validator[$playerIdField];

                if ($playerId) {

                    // $bookingExists = TeeBookingDetails::whereDate('created_at', now()->toDateString())

                    $bookingExists = TeeBookingDetails::where('tee_booking_id', $request->tee_booking_id)

                        ->whereNot("id", $teeBookingDetailId)

                        ->where("is_cancelled", 0)

                        ->where(function ($query) use ($playerId) {

                            $query->where('player1_id', $playerId)

                                ->orWhere('player2_id', $playerId)

                                ->orWhere('player3_id', $playerId)

                                ->orWhere('player4_id', $playerId);

                        })

                        ->exists();



                    if ($bookingExists) {

                        $playerNumber = $index + 1; // Get the player number (1-indexed)

                        if($playerNumber==1){

                            return response()->json(['error' => "You have already booked a slot on the same day."], 400);

                        }else{



                            return response()->json(['error' => "Player $playerNumber has already booked a slot on the same day."], 400);

                        }

                    }

                }

            }

        }

        $booking_type = "";

        if ($teeBookingDetailId) {

            $subject = "Tee Booking Modification";

            $booking_type = "modified";

        } else {

            $subject = "Tee Booking Confirmation";

            $booking_type = "confirmed";

        }

        TeeBookingDetails::whereNull('player1_id')->where('locked_by', Auth()->user()->id)->delete();





        $teeBookingDetail = TeeBookingDetails::updateOrCreate(

            ['id' => $teeBookingDetailId],

            [

                "player1_id"=>$playerId1,

                "player2_id"=>$playerId2,

                "player3_id"=>$playerId3,

                "player4_id"=>$playerId4,

                "tee_booking_id"=>$request->tee_booking_id,

                "tee_sheet_id"=>$request->tee_sheet_id,

                'created_by' => auth()->user()->id,

                'locked_by' => ''

            ]

        );



        // Get the id after the updateOrCreate operation

        $teeBookingDetailId = $teeBookingDetail->id;

        $bookingId = "TB" . str_pad($teeBookingDetailId, 8, '0', STR_PAD_LEFT);

        $TBD = TeeBookingDetails::find($teeBookingDetailId);

        $TBD->bookingId = $bookingId;

        $TBD->save();











        $data = TeeBookingDetails::get_booking_details($teeBookingDetailId);

        $email = Auth()->user()->Email;

        //$email = "sohanbairwa2021@gmail.com";

        $data->booking_type = $booking_type;

        // Mail::to($email)->send(new TeeSendMail($data, $subject));



        return response()->json(['message' => 'TeeBookingDetails created successfully']);

    }



    public function logout()

    {

        session()->flush();



        Auth::logout();



        return redirect()->route('admin.login');

    }

}

