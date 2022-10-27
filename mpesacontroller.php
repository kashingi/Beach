<?php

namespace App\Http\Controllers\mpesa;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class mpesacontroller extends Controller
{
	//Public function to get the access token for mpesa Payment
    public function getAccessToken(){
		//Create variable credentials using customer and consumer keys
        $credentials='Y0GAwFkGauTlQNA6vGIdfFWyAKcsgq71'.':'.'An9NZn4VVyzy8ERP';
        $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_HTTPHEADER=>['Content-type:application/json;charset=utf8'],
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => false,
                CURLOPT_USERPWD =>$credentials,
            ),
        );
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
       return $response->access_token ;
    }
          public function  stkpush(Request $request) {
			//Obtain the authorized users details from the users table
            $userId=Auth::user()->id;
			$Phone=Auth::user()->phone;
			//The paybill number as short code
            $shortcode=174379;

            $Timestamp=Carbon::rawParse('now')->format('YmdHis');
		    $password = base64_encode($shortcode.'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'.$Timestamp);
		     $payload = array (
			"BusinessShortCode" =>174379,
			"Password" => $password,
			"Timestamp" => $Timestamp,
			"TransactionType" => "CustomerPayBillOnline",
			"Amount" => 1,
			"PartyA" => $Phone,
			// "PartyA" =>254790487504,
			"PartyB" => 174379,
			"PhoneNumber" =>$Phone,
			// "PhoneNumber" =>254790487504,
            "CallBackURL"=>"https://f02c-2c0f-fe38-2401-51f0-a9fe-165d-89e6-6de5.ngrok.io/api/mpesaresponse/$userId",
			"AccountReference" => 'farmers Voice',
			"TransactionDesc" => "Payment of goods" 
		);
		$curl = curl_init();		
		curl_setopt_array($curl, 
			array(
				CURLOPT_URL =>'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
				CURLOPT_HTTPHEADER =>array('Content-Type:application/json','Authorization: Bearer '.$this->getAccessToken()),
            
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($payload) 
			)
		);

		$curl_response = curl_exec($curl);
		//Take the user to thanks page after making payment to print receipt
	   	return redirect('/thanks');

          }  
        
        

}
