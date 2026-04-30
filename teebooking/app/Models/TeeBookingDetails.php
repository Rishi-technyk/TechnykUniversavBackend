<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TeeSheet;
use DB;
use DateTime;
use Carbon\Carbon;
use App\CPU\Helpers;

class TeeBookingDetails extends Model
{
    protected $table = 'tee_booking_details';

    protected $fillable = [
        'tee_booking_id',
        'tee_sheet_id',
        'locked_by',
        'locked_by_remarks',
        'player1_id',
        'player2_id',
        'player3_id',
        'player4_id',
        'locked_till',
        'is_created_by_admin',
        'is_cancelled',
        'cancelled_by',
        'cancelled_at',
        'is_cancelled_by_admin',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $dates = [
        'locked_till',
        'cancelled_at',
        'created_at',
        'updated_at',
    ];

    // Add any relationships or additional methods as needed
    /**
     * Retrieve booking information for a specific player ID.
     *
     * @param int $player_id The ID of the player for whom booking information is fetched.
     * @return mixed Tee sheet data associated with the player ID.
     */
    public static function get_player_booking($player_id)
    {
        $tableObj = TeeSheet::select(
            'tbd.*',
            'tee_sheet.tee_time as tee_sheet_time',
            'tee_booking.booking_date',
            'tee_holes.hole_number',
            'tbd.id as tee_booking_detail_id',
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
                WHEN tbd.player1_id IS NOT NULL THEN 1 
                ELSE 0 
            END -
            CASE 
                WHEN tbd.player2_id IS NOT NULL THEN 1 
                ELSE 0 
            END -
            CASE 
                WHEN tbd.player3_id IS NOT NULL THEN 1 
                ELSE 0 
            END -
            CASE 
                WHEN tbd.player4_id IS NOT NULL THEN 1 
                ELSE 0 
            END) AS available_players')
        )

            ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
            ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
            ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
            ->leftJoin('tee_booking_details as tbd', 'tbd.tee_sheet_id', '=', 'tee_sheet.id')
            ->leftJoin('memberprofile as mp1', 'mp1.id', '=', 'tbd.player1_id')
            ->leftJoin('memberprofile as mp2', 'mp2.id', '=', 'tbd.player2_id')
            ->leftJoin('memberprofile as mp3', 'mp3.id', '=', 'tbd.player3_id')
            ->leftJoin('memberprofile as mp4', 'mp4.id', '=', 'tbd.player4_id')
            ->where(function ($query) use ($player_id) {
                $query->where('tbd.player1_id', $player_id)
                    ->orWhere('tbd.player2_id', $player_id)
                    ->orWhere('tbd.player3_id', $player_id)
                    ->orWhere('tbd.player4_id', $player_id);
            });

        $teeSheets = $tableObj->orderBy('tbd.id', 'DESC')->get();

        return $teeSheets;
    }

    public static function get_booking_details($booking_id)
    {
        $tableObj = TeeSheet::select(
            'tbd.*',
            'tee_sheet.tee_time',
            'tee_holes.hole_number',
            'tee_booking.booking_date',
            'tbd.id as tee_booking_detail_id',
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
                WHEN tbd.player1_id IS NOT NULL THEN 1 
                ELSE 0 
            END -
            CASE 
                WHEN tbd.player2_id IS NOT NULL THEN 1 
                ELSE 0 
            END -
            CASE 
                WHEN tbd.player3_id IS NOT NULL THEN 1 
                ELSE 0 
            END -
            CASE 
                WHEN tbd.player4_id IS NOT NULL THEN 1 
                ELSE 0 
            END) AS available_players')
        )

            ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
            ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
            ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
            ->leftJoin('tee_booking_details as tbd', 'tbd.tee_sheet_id', '=', 'tee_sheet.id')
            ->leftJoin('memberprofile as mp1', 'mp1.id', '=', 'tbd.player1_id')
            ->leftJoin('memberprofile as mp2', 'mp2.id', '=', 'tbd.player2_id')
            ->leftJoin('memberprofile as mp3', 'mp3.id', '=', 'tbd.player3_id')
            ->leftJoin('memberprofile as mp4', 'mp4.id', '=', 'tbd.player4_id')
            ->where(function ($query) use ($booking_id) {
                $query->where('tbd.id', $booking_id);
                    
            });

        $teeSheets = $tableObj->orderBy('tbd.id', 'DESC')->first();

        return $teeSheets;
    }

    /**
     * Checks if a given date meets certain criteria.
     *
     * @param DateTime|null $date The date to check. If not provided, returns false.
     *
     * @return bool Returns true if the date meets specified conditions, otherwise false.
     */
    public static function date_check($date)
    {
        $status = false;

        // If no date provided, return false
        if (!$date) {
            return $status;
        }

        // Modify the given date by adding 1 day and format it to 'Y-m-d'
        // $date = $date->modify("+1 day");
        // $date = $date->format('Y-m-d');

        $teeSheetStartTime = TeeSheet::leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
        ->where('tee_booking.booking_date', $date)->first()->tee_time;
       // dd( $teeSheetStartTime);
        $teeTime = '06:00:00';
        
        $currentDateTime = new DateTime();
        // $specifiedDateTime = new DateTime($date . '06:00:00');
        //dd(Helpers::get_setting('booking_start_time'),);
       // $specifiedDateTime = new DateTime($date . Helpers::get_setting('booking_start_time'));
       $specifiedDateTime = new DateTime($date . $teeTime);

        // Current date and time
        //$currentDateTime = new DateTime();

        // Create a DateTime object for the specified date at 09:00:00
       // $specifiedDateTime = new DateTime($date . Helpers::get_setting('booking_start_time'));

        // Calculate 36 hours before the specified date and time
        $windowStart = clone $specifiedDateTime;
        $HBB = Helpers::get_setting('hour_before_booking');
        $windowStart->modify('-'.$HBB.' hours');

        // Calculate 16 hours after the specified date and time
        $windowEnd = clone $windowStart;
        $HBR = Helpers::get_setting('hour_booking_range');
        $windowEnd->modify('+'.$HBR.' hours');

        // Check if the current date and time falls within the specified window
        if ($currentDateTime > $windowStart && $currentDateTime < $windowEnd) {
            $status = true;
        }

        // Uncomment below if you want to display the window start and end times
        // $windowMessage = "The window starts at: " . $windowStart->format('d-m-Y H:i') . "<br>";
        // $windowMessage .= "The window ends at: " . $windowEnd->format('d-m-Y H:i');
        // echo $windowMessage;
        // die();
        return $status;
    }

}
