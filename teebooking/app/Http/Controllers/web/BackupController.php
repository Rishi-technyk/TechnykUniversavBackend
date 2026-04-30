<?php

namespace App\Http\Controllers\web;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Models\User;
use App\Models\TeeSheet;
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

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;




class HomeController extends Controller
{
    public function index(Request $request)
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
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE tbd.id END as tee_booking_detail_id'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE tbd.bookingId END as bookingId'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE tbd.player1_id END as player1_id'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE tbd.player2_id END as player2_id'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE tbd.player3_id END as player3_id'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE tbd.player4_id END as player4_id'),
                    'tbd.created_at as booking_date',
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE mp1.MemberID END as player1_member_id'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE mp2.MemberID END as player2_member_id'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE mp3.MemberID END as player3_member_id'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE mp4.MemberID END as player4_member_id'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE mp1.DisplayName END as player1_name'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE mp2.DisplayName END as player2_name'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE mp3.DisplayName END as player3_name'),
                    DB::raw('CASE WHEN tbd.is_cancelled = 1 THEN NULL ELSE mp4.DisplayName END as player4_name'),
                    DB::raw('(4 - 
                        CASE WHEN tbd.player1_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 ELSE 0 END -
                        CASE WHEN tbd.player2_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 ELSE 0 END -
                        CASE WHEN tbd.player3_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 ELSE 0 END -
                        CASE WHEN tbd.player4_id IS NOT NULL and tbd.is_cancelled = 0 THEN 1 ELSE 0 END
                    ) AS available_players')
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
        $teeBooking = TeeBookingDetails::find($id);
        $teeBooking->cancelled_by = Auth()->user()->id;
        $teeBooking->is_cancelled = 1;
        $teeBooking->cancelled_at = Carbon::now();
        //$teeBooking->cancelled_at = 
        $teeBooking->save();

        return redirect()->route('home')->with('success', 'Booking canceled successfully');
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
        $validator = $request->validate([
            'tee_sheet_id' => 'required',
        ]);
        $tee_sheet_id = $request->tee_sheet_id;
        TeeBookingDetails::updateOrCreate(
            ['tee_sheet_id' => $tee_sheet_id],
            array_merge($validator, [
                'locked_by' => 1,
                'created_by' => 1,
            ])
        );

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
                    $bookingExists = TeeBookingDetails::whereDate('created_at', now()->toDateString())
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

        $teeBookingDetail = TeeBookingDetails::updateOrCreate(
            ['id' => $teeBookingDetailId],
            array_merge($validator, [
                'locked_by' => 1,
                'created_by' => 1,
            ])
        );

        // Get the id after the updateOrCreate operation
        $teeBookingDetailId = $teeBookingDetail->id;
        $bookingId = "TB" . str_pad($teeBookingDetailId, 8, '0', STR_PAD_LEFT);
        $TBD = TeeBookingDetails::find($teeBookingDetailId);
        $TBD->bookingId = $bookingId;
        $TBD->save();

        $email = auth()->user()->Email;

        $data = TeeBookingDetails::get_booking_details($teeBookingDetailId);
        //$email = "sohanbairwa2021@gmail.com";
        Mail::to($email)->send(new TeeSendMail($data));
        return response()->json(['message' => 'TeeBookingDetails created successfully']);
    }

    public function logout()
    {
        session()->flush();

        Auth::logout();

        return redirect()->route('admin.login');
    }
}
