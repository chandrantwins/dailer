<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeController extends Controller
{
    //https://packalyst.com/packages/package/atacante/cashier-authorizenetpaisa
    public function index() {
        return view('checkout.authorize');
    }
    
    public function chargeCreditCard(Request $request)
    {
        // Common setup for API credentials
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('services.authorize.login'));
        $merchantAuthentication->setTransactionKey(config('services.authorize.key'));
        $refId = 'ref'.time();

        // Create the payment data for a credit card
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($request->cnumber);
        
        // $creditCard->setExpirationDate( "2038-12");
        $expiry = $request->card_expiry_year . '-' . $request->card_expiry_month;
        $creditCard->setExpirationDate($expiry);
        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard($creditCard);
        
        // Create a transaction
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($request->camount);
        $transactionRequestType->setPayment($paymentOne);
        
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId( $refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        if ($response != null) {
                $tresponse = $response->getTransactionResponse();
                if (($tresponse != null) && ($tresponse->getResponseCode()=="1")) {
                    echo "Charge Credit Card AUTH CODE : " . $tresponse->getAuthCode() . "\n";
                    echo "Charge Credit Card TRANS ID  : " . $tresponse->getTransId() . "\n";
                } else {
                    echo "Charge Credit Card ERROR :  Invalid response\n";
                }
        }
        else {
                echo  "Charge Credit Card Null response returned";
        }

        return redirect('/');
    }
}