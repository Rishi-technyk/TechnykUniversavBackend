<?php

namespace App\CPU;


use App\Models\BusinessSetting;
use App\Model\Currency;
use App\Models\TeeMyBuddies;
use App\Models\TeeSheet;
use App\Models\TeeGroup;
use DB;

class Helpers
{
    public static function status($id)
    {
        if ($id == 1) {
            $x = 'active';
        } elseif ($id == 0) {
            $x = 'in-active';
        }

        return $x;
    }
    public static function set_common_request($request, $is_update = false)
    {
        if ($is_update == false) {
            $request['created_by'] = 11;
            $request['updated_by'] = 11;
        } else {
            $request['updated_by'] = 11;
        }


        return $request;
    }
    

    public static function date_format($date)
    {
        $dateStr = "";
        if ($date ) {
          $dateStr = date('d-m-Y',strtotime( $date));
        } 

        return $dateStr;
    }


    public static function get_setting($name)
    {
        $config = null;
       
            $data = BusinessSetting::where(['key_name' => $name])->first();
            if (isset($data)) {
                $config = json_decode($data['key_value'], true);
                if (is_null($config)) {
                    $config = $data['key_value'];
                }
            }

        return $config;
    }




    public static function get_business_settings($name)
    {
        $config = null;
        $check = ['currency_model', 'currency_symbol_position', 'system_default_currency', 'language', 'company_name', 'decimal_point_settings'];

        if (in_array($name, $check) == true && session()->has($name)) {
            $config = session($name);
        } else {
            $data = BusinessSetting::where(['type' => $name])->first();
            if (isset($data)) {
                $config = json_decode($data['value'], true);
                if (is_null($config)) {
                    $config = $data['value'];
                }
            }

            if (in_array($name, $check) == true) {
                session()->put($name, $config);
            }
        }

        return $config;
    }

    public static function get_settings($object, $type)
    {
        $config = null;
        foreach ($object as $setting) {
            if ($setting['type'] == $type) {
                $config = $setting;
            }
        }
        return $config;
    }



    public static function get_image_path($type)
    {
        $path = asset('storage/app/public/brand');
        return $path;
    }



    public static function currency_load()
    {
        $default = Helpers::get_business_settings('system_default_currency');
        $current = \session('system_default_currency_info');
        if (session()->has('system_default_currency_info') == false || $default != $current['id']) {
            $id = Helpers::get_business_settings('system_default_currency');
            $currency = Currency::find($id);
            session()->put('system_default_currency_info', $currency);
            session()->put('currency_code', $currency->code);
            session()->put('currency_symbol', $currency->symbol);
            session()->put('currency_exchange_rate', $currency->exchange_rate);
        }
    }

    public static function currency_converter($amount)
    {
        $currency_model = Helpers::get_business_settings('currency_model');
        if ($currency_model == 'multi_currency') {
            if (session()->has('usd')) {
                $usd = session('usd');
            } else {
                $usd = Currency::where(['code' => 'USD'])->first()->exchange_rate;
                session()->put('usd', $usd);
            }
            $my_currency = \session('currency_exchange_rate');
            $rate = $my_currency / $usd;
        } else {
            $rate = 1;
        }

        return Helpers::set_symbol(round($amount * $rate, 2));
    }

    public static function language_load()
    {
        if (\session()->has('language_settings')) {
            $language = \session('language_settings');
        } else {
            $language = BusinessSetting::where('type', 'language')->first();
            \session()->put('language_settings', $language);
        }
        return $language;
    }

    public static function tax_calculation($price, $tax, $tax_type)
    {
        $amount = ($price / 100) * $tax;
        return $amount;
    }

    /**
     * Retrieve booking information for a specific player ID.
     * S@DEV
     * @param int $player_id The ID of the player for whom booking information is fetched.
     * @return mixed Tee sheet data associated with the player ID.
     */
    public static function get_player_booking($player_id)
    {
        $tableObj = TeeSheet::select(
            'tee_sheet.*',
            'tee_holes.hole_number',
            'tbd.id as tee_booking_detail_id',
            'tbd.player1_id',
            'tbd.player2_id',
            'tbd.player3_id',
            'tbd.player4_id',
            'mp1.MemberID as player1_member_id',
            'mp2.MemberID as player2_member_id',
            'mp3.MemberID as player3_member_id',
            'mp4.MemberID as player4_member_id',
            'mp1.DisplayName as player1_name',
            'mp2.DisplayName as player2_name',
            'mp3.DisplayName as player3_name',
            'mp4.DisplayName as player4_name',
        )

            ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
            ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
            ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
            ->leftJoin('tee_booking_details as tbd', 'tbd.tee_sheet_id', '=', 'tee_sheet.id')
            ->leftJoin('memberprofile as mp1', 'mp1.id', '=', 'tbd.player1_id')
            ->leftJoin('memberprofile as mp2', 'mp2.id', '=', 'tbd.player2_id')
            ->leftJoin('memberprofile as mp3', 'mp3.id', '=', 'tbd.player3_id')
            ->leftJoin('memberprofile as mp4', 'mp4.id', '=', 'tbd.player4_id');



        $teeSheets = $tableObj->get();
        $matchingPlayer = $teeSheets->first(function ($sheet) use ($player_id) {
            return in_array($player_id, [
                $sheet->player1_id,
                $sheet->player2_id,
                $sheet->player3_id,
                $sheet->player4_id,
            ]);
        });


        return $matchingPlayer;
    }
    public static function get_buddy_list(){
        $membersObj = TeeMyBuddies::select(
            'tee_my_buddies.id',
            'memberprofile.Email',
            'memberprofile.DisplayName as name',
            'memberprofile.MemberID',
            'memberprofile.Status',

        )
        ->leftJoin('memberprofile', 'memberprofile.id', '=', 'tee_my_buddies.member_id')
        ->where('tee_my_buddies.created_by',auth()->user()->id)
        ->get()->toArray();
        return $membersObj;
    }

    public static function get_my_group_list(){
        $membersObj= TeeGroup::select(
            'tee_my_groups.*',
            'mp1.MemberID as player1_member_id',
            'mp2.MemberID as player2_member_id',
            'mp3.MemberID as player3_member_id',
            'mp4.MemberID as player4_member_id',
            'mp1.DisplayName as player1_name',
            'mp2.DisplayName as player2_name',
            'mp3.DisplayName as player3_name',
            'mp4.DisplayName as player4_name',
        )

            ->leftJoin('memberprofile as mp1', 'mp1.id', '=', 'tee_my_groups.player1_id')
            ->leftJoin('memberprofile as mp2', 'mp2.id', '=', 'tee_my_groups.player2_id')
            ->leftJoin('memberprofile as mp3', 'mp3.id', '=', 'tee_my_groups.player3_id')
            ->leftJoin('memberprofile as mp4', 'mp4.id', '=', 'tee_my_groups.player4_id')
            ->where("tee_my_groups.created_by",auth()->user()->id)
            ->get();

        return $membersObj;
    }


    public static function module_permission_check($mod_name)
    {
        $user_role = auth('admin')->user()->role;
        $permission = $user_role->module_access;
        if (isset($permission) && $user_role->status == 1 && in_array($mod_name, (array) json_decode($permission)) == true) {
            return true;
        }

        if (auth('admin')->user()->admin_role_id == 1) {
            return true;
        }
        return false;
    }

    public static function convert_currency_to_usd($price)
    {
        $currency_model = Helpers::get_business_settings('currency_model');
        if ($currency_model == 'multi_currency') {
            Helpers::currency_load();
            $code = session('currency_code') == null ? 'USD' : session('currency_code');
            if ($code == 'USD') {
                return $price;
            }
            $currency = Currency::where('code', $code)->first();
            $price = floatval($price) / floatval($currency->exchange_rate);

            $usd_currency = Currency::where('code', 'USD')->first();
            $price = $usd_currency->exchange_rate < 1 ? (floatval($price) * floatval($usd_currency->exchange_rate)) : (floatval($price) / floatval($usd_currency->exchange_rate));
        } else {
            $price = floatval($price);
        }

        return $price;
    }

    public static function order_status_update_message($status)
    {
        if ($status == 'pending') {
            $data = BusinessSetting::where('type', 'order_pending_message')->first()->value;
        } elseif ($status == 'confirmed') {
            $data = BusinessSetting::where('type', 'order_confirmation_msg')->first()->value;
        } elseif ($status == 'processing') {
            $data = BusinessSetting::where('type', 'order_processing_message')->first()->value;
        } elseif ($status == 'out_for_delivery') {
            $data = BusinessSetting::where('type', 'out_for_delivery_message')->first()->value;
        } elseif ($status == 'delivered') {
            $data = BusinessSetting::where('type', 'order_delivered_message')->first()->value;
        } elseif ($status == 'returned') {
            $data = BusinessSetting::where('type', 'order_returned_message')->first()->value;
        } elseif ($status == 'failed') {
            $data = BusinessSetting::where('type', 'order_failed_message')->first()->value;
        } elseif ($status == 'delivery_boy_delivered') {
            $data = BusinessSetting::where('type', 'delivery_boy_delivered_message')->first()->value;
        } elseif ($status == 'del_assign') {
            $data = BusinessSetting::where('type', 'delivery_boy_assign_message')->first()->value;
        } elseif ($status == 'ord_start') {
            $data = BusinessSetting::where('type', 'delivery_boy_start_message')->first()->value;
        } elseif ($status == 'expected_delivery_date') {
            $data = BusinessSetting::where('type', 'delivery_boy_expected_delivery_date_message')->first()->value;
        } elseif ($status == 'canceled') {
            $data = BusinessSetting::where('type', 'order_canceled')->first()->value;
        } else {
            $data = '{"status":"0","message":""}';
        }

        $res = json_decode($data, true);

        if ($res['status'] == 0) {
            return 0;
        }
        return $res['message'];
    }

    /**
     * Device wise notification send
     */
    public static function send_push_notif_to_device($fcm_token, $data)
    {
        $key = BusinessSetting::where(['type' => 'push_notification_key'])->first()->value;
        $url = "https://fcm.googleapis.com/fcm/send";
        $header = array(
            "authorization: key=" . $key . "",
            "content-type: application/json"
        );

        if (isset($data['order_id']) == false) {
            $data['order_id'] = null;
        }

        $postdata = '{
            "to" : "' . $fcm_token . '",
            "data" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $data['image'] . '",
                "order_id":"' . $data['order_id'] . '",
                "is_read": 0
              },
              "notification" : {
                "title" :"' . $data['title'] . '",
                "body" : "' . $data['description'] . '",
                "image" : "' . $data['image'] . '",
                "order_id":"' . $data['order_id'] . '",
                "title_loc_key":"' . $data['order_id'] . '",
                "is_read": 0,
                "icon" : "new",
                "sound" : "default"
              }
        }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }

    public static function send_push_notif_to_topic($data)
    {
        $key = BusinessSetting::where(['type' => 'push_notification_key'])->first()->value;

        $url = "https://fcm.googleapis.com/fcm/send";
        $header = [
            "authorization: key=" . $key . "",
            "content-type: application/json",
        ];

        $image = asset('storage/app/public/notification') . '/' . $data['image'];
        $postdata = '{
            "to" : "/topics/sixvalley",
            "data" : {
                "title":"' . $data->title . '",
                "body" : "' . $data->description . '",
                "image" : "' . $image . '",
                "is_read": 0
              },
              "notification" : {
                "title":"' . $data->title . '",
                "body" : "' . $data->description . '",
                "image" : "' . $image . '",
                "title_loc_key":null,
                "is_read": 0,
                "icon" : "new",
                "sound" : "default"
              }
        }';

        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        // Get URL content
        $result = curl_exec($ch);
        // close handle to release resources
        curl_close($ch);

        return $result;
    }

}