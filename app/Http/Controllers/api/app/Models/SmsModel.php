<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsModel extends Model
{


    public function sendSms($mobileNumber)
    {
        $alphanum = "0123456789";
        $otp = substr(str_shuffle($alphanum), 0, 6);
        $authKey = "135468AwHMDbYRku58e1d959";
        //$mobileNumber = "9549103767";
        $senderId = "AVICLB";
        $TemplateID = "1207168192446221344";
        $SMSText = "Dear Member, your OTP for Login is $otp. Please do not share this OTP.";
        $SMSText = $SMSText . "\n" . " Regards,";
        $SMSText = $SMSText . "\n" . " AVICLUB,";
        $SMSText = $SMSText . "\n" . " powered by technyk";
        $message = urlencode($SMSText);
        //Define route 
        $route = "4";
        //Prepare your post parameters
        $postData = array(
            'authkey' => $authKey,
            'mobiles' => $mobileNumber,
            'message' => $message,
            'sender' => $senderId,
            'route' => $route,
            'DLT_TE_ID' => $TemplateID,
            'country' => 91
        );

        //API URL
        $url = "http://india.msg91.com/sendhttp.php";

        // init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData)
        )
        );

        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //get response
        $output = curl_exec($ch);

        //Print error if any
        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
        }
        curl_close($ch);
        return $otp;

    }

    public function sendBookingSms($mobileNumber,)
    {

        $alphanum = "0123456789";
        $otp = substr(str_shuffle($alphanum), 0, 6);
        //$mobileNumber = "9549103767";
        $authKey = "135468AwHMDbYRku58e1d959";
        //$mobileNumber = "9549103767";
        //$senderId = "AVICLB";
        $senderId = "AEPTAG";
        // $TemplateID = "1207168192446221344";
        $TemplateID = "1707170575162965711";
        $SMSText = "Dear Member, Your Tee Booking has been confirmed for $otp against booking number $otp. Regards. MGT AEPTA";
       /* $SMSText = $SMSText . "\n" . " Your Tee Booking has been confirmed for $otp against booking number $otp.";
        $SMSText = $SMSText . "\n" . " Regards.";
        //$SMSText = $SMSText . "\n" . " AVICLUB,";
        $SMSText = $SMSText . "\n" . " AEPTA by technyk";*/
        //$SMSText = $SMSText . "\n" . " powered by technyk";
        $message = urlencode($SMSText);
        //Define route 
        $route = "4";
        //Prepare your post parameters
        $postData = array(
            'authkey' => $authKey,
            'mobiles' => $mobileNumber,
            'message' => $message,
            'sender' => $senderId,
            'route' => $route,
            'DLT_TE_ID' => $TemplateID,
            'country' => 91
        );

        //API URL
        $url = "http://india.msg91.com/sendhttp.php";

        // init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData)
        )
        );

        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        //get response
        $output = curl_exec($ch);

        //Print error if any
        if (curl_errno($ch)) {
            echo 'error:' . curl_error($ch);
        }
        curl_close($ch);
        return $otp;

    }


}

?>