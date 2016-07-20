<?php

namespace l5ebtapi\l5ebtapi\Http\Controllers;

//use Illuminate\Http\Request;

use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

//use Intervention\Image\Facades\Image;

class L5ebtapiController extends Controller
{

    // Declare private variables
    private $api_url = 'https://api.ebay.com/ws/api.dll';
    private $api_verify_ssl = true;
    private $api_compatibility_level = '971';
    private $api_error_language = 'US';
    private $api_warning_level = 'Low';
    private $api_runame = '';
    private $api_user_token = '';
    private $api_dev_id;
    private $api_app_id;
    private $api_cert_id;
    private $api_site_id = '0';

    /**
     * Create a new L5ebtapiController instance.
     *
     * @return void
     */
    public function __construct($attributes)
    {
        if (isset($attributes['api_url']) && strlen($attributes['api_url']) > 0) {

            $this->api_url = $attributes['api_url'];

        }
        if (isset($attributes['api_verify_ssl']) && $attributes['api_verify_ssl'] == true || $attributes['api_verify_ssl'] == false) {

            $this->api_verify_ssl = $attributes['api_verify_ssl'];

        } else {

            $this->api_verify_ssl = true;

        }
        if (isset($attributes['api_compatibility_level']) && strlen($attributes['api_compatibility_level']) > 0) {

            $this->api_compatibility_level = $attributes['api_compatibility_level'];

        }
        if (isset($attributes['api_error_language']) && strlen($attributes['api_error_language']) > 0) {

            $this->api_error_language = $attributes['api_error_language'];

        }
        if (isset($attributes['api_warning_level']) && strlen($attributes['api_warning_level']) > 0) {

            $this->api_warning_level = $attributes['api_warning_level'];

        }
        if (isset($attributes['api_runame']) && empty($attributes['api_runame']) || is_null($attributes['api_runame'])) {

            $this->api_runame = '';

        } else {

            $this->api_runame = $attributes['api_runame'];

        }
        if (isset($attributes['api_user_token']) && empty($attributes['api_user_token']) || is_null($attributes['api_user_token'])) {

            $this->api_user_token = '';

        } else {

            $this->api_user_token = $attributes['api_user_token'];

        }
        if (isset($attributes['api_dev_id'])) {

            $this->api_dev_id = $attributes['api_dev_id'];

        }
        if (isset($attributes['api_app_id'])) {

            $this->api_app_id = $attributes['api_app_id'];

        }
        if (isset($attributes['api_cert_id'])) {

            $this->api_cert_id = $attributes['api_cert_id'];

        }
        if (isset($attributes['api_site_id']) && strlen($attributes['api_site_id']) > 0) {

            $this->api_site_id = $attributes['api_site_id'];

        }

    }// END constructor

    /**
     * Method: getEbayOfficialTime() - get the eBay official time API call.
     *
     * @return Array The eBay official time key = 'eBay_Official_Time' OR Array with a key = 'Error:' and Value = 'The error message'
     * EX. ['Error:' => 'An error occurred during the request. please verify all settings are correct and try again.']
     */
    public function getEbayOfficialTime()
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                        <GeteBayOfficialTimeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                        <RequesterCredentials>
                        <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                        </RequesterCredentials>

                        <!-- Standard Input Fields -->

                         <ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                         <Version>' . $this->api_compatibility_level . '</Version>
                         <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
                        </GeteBayOfficialTimeRequest>​​​';

        $responseXml = L5ebtapiController::request('GeteBayOfficialTime()', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getEbayOfficialTime() 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try again.'];

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getEbayOfficialTime() Error sending request' .
                'the XML response is an empty string');

            return ['Error:' => 'The XML response is an empty string. Please verify all eBay API settings are correct' .
            'and try again.'];

        }

        //Xml string is parsed and creates a DOM Document object
        $responseDoc = new DomDocument();

        $responseDoc->loadXML($responseXml);

        //get any error nodes
        $errors = $responseDoc->getElementsByTagName('Errors');

        //if there are error nodes return the error message (array)
        if ($errors->length > 0) {

            $code = $errors->item(0)->getElementsByTagName('ErrorCode');

            $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');

            $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

            //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
            if (count($longMsg) > 0) {

                Log::warning('eBay API Call: getEbayOfficialTime(). Short message: ' .
                    $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                Log::warning('eBay API Call: getEbayOfficialTime(). Long message: ' .
                    $longMsg->item(0)->nodeValue);

            } else {

                Log::warning('eBay API Call: getEbayOfficialTime(). Short message: ' .
                    $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

            }

            return ['Error:' => 'An error occurred while processing the getEbayOfficialTime() request.' .
                'Please verify all eBay API settings are correct and try the request again.'];

        } else //no errors so return the eBay official time as a (string)
        {
            $xml = simplexml_load_string($responseDoc->saveXML());

            if ($xml->Ack && ((string)$xml->Ack == 'Success')) {

                if ($xml->Timestamp) {

                    return ['eBay_Official_Time' => (string)$xml->Timestamp];

                } else {

                    $timestamp = ['Error:' => 'An error occurred While processing the getEbayOfficialTime() request.' .
                        'Please verify all eBay API settings are correct and try the request again.'];

                }

            } else {

                $timestamp = ['Error:' => 'An error occurred While processing the getEbayOfficialTime() request.' .
                    'Please verify all eBay API settings are correct and try the request again.'];

            }

        }

        return $timestamp;

    }// END getEbayOfficialTime()

    /**
     * Method: getEbayDetails() - Retrieves eBay IDs and codes for Example shipping service codes, enumerated data
     * for Example payment methods, and other common eBay meta-data.
     *
     * @return Array mixed OR Array with a key = 'Error:' and Value = 'The error message'
     */
    public function getEbayDetails($detailName)
    {

        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                         <GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                         <RequesterCredentials>
                         <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                         </RequesterCredentials>

                         <!-- Call-specific Input Fields -->

                         <DetailName>' . $detailName . '</DetailName>

                         <!-- Standard Input Fields -->

                         <ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                         <Version>' . $this->api_compatibility_level . '</Version>
                         <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
                         </GeteBayDetailsRequest>​';

        $responseXml = L5ebtapiController::request('GeteBayDetails', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: getEbayDetails() 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try the request again.'];

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: getEbayDetails() Error sending request. The XML response is an empty string');

            return ['Error:' => 'There was an error sending request the XML response is an empty string.' .
                'Please verify all eBay API settings are correct and try the request again.'];

        }

        //Xml string is parsed and creates a DOM Document object
        $responseDoc = new DomDocument();

        $responseDoc->loadXML($responseXml);

        //dd($responseDoc->saveXML());

        $ack = $responseDoc->getElementsByTagName('Ack');

        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Failure' || $ack->item(0)->nodeValue == 'Warning') {

            //get any error nodes
            $errors = $responseDoc->getElementsByTagName('Errors');

            //if there are error nodes return the error message (array)
            if ($errors->length > 0) {

                $code = $errors->item(0)->getElementsByTagName('ErrorCode');

                $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');

                $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

                if ($ack->item(0)->nodeValue == 'Failure') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::error('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::error('eBay API Call: getEbayDetails() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' .
                            $shortMsg->item(0)->nodeValue . ' : Long Message: ' . $longMsg->item(0)->nodeValue];

                    } else {

                        Log::error('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' . $shortMsg->item(0)->nodeValue];

                    }

                }
                if ($ack->item(0)->nodeValue == 'Warning') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::warning('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::warning('eBay API Call: getEbayDetails() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                    } else {

                        Log::warning('eBay API Call: getEbayDetails() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                }

            }


        }
        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Success' || $ack->item(0)->nodeValue == 'Warning') {

            $xml = simplexml_load_string($responseDoc->saveXML());

            $data = json_encode($xml);

            return json_decode($data, TRUE);

        }
        else {

            Log::error('eBay API Call: getEbayDetails(): An error occurred during the getEbayDetails() ebay API' .
                'call. Please verify all API settings are correct and then try the request again.');

            return ['Error:' => 'An error occurred during the getEbayDetails() ebay API call.' .
                'Please verify all API settings are correct and then try the request again.'];

        }

    }// END getEbayDetails()

    /**
     * Method: uploadSiteHostedPictures($multiPartImageData, $image_name) - Upload an image to the eBay Picture Service.
     *
     * @param $multiPartImageData the image data. Acceptable formats (jpg, gif, png)
     * @param $image_name The name the uploaded image will have on the eBay Picture Services server.
     * @return Array key = 'eBay_Picture_Url' Value = 'The URL to the picture' OR Array with a key = 'Error:' and
     * Value = 'The error message'
     * EX. ['Error:' => 'An error occurred during the request. please verify all settings are correct and try again.']
     */
    public function uploadSiteHostedPictures($multiPartImageData, $imageName)
    {

        ///Build the request XML request which is first part of multi-part POST
        $xmlReq = '<?xml version="1.0" encoding="utf-8"?>
                           <UploadSiteHostedPicturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                           <RequesterCredentials>
                           <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                           </RequesterCredentials>

                           <!-- Call-specific Input Fields -->
                           <PictureName>' . $imageName . '</PictureName>
                           <PictureSet>Standard</PictureSet>
                           <PictureUploadPolicy>ClearAndAdd</PictureUploadPolicy>

                           <!-- Standard Input Fields -->

                           <ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                           <Version>' . $this->api_compatibility_level . '</Version>
                           <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
                           </UploadSiteHostedPicturesRequest>';

        $boundary = "==Multipart_Boundary_x" . md5(mt_rand()) . "x";
        $CRLF = "\r\n";

        // The complete POST consists of an XML request plus the binary image separated by boundaries
        $firstPart = '';
        $firstPart .= "--" . $boundary . $CRLF;
        $firstPart .= 'Content-Disposition: form-data; name="XML Payload"' . $CRLF;
        $firstPart .= 'Content-Type: text/xml;charset=utf-8' . $CRLF . $CRLF;
        $firstPart .= $xmlReq;
        $firstPart .= $CRLF;

        //$secondPart = '';
        $secondPart = "--" . $boundary . $CRLF;
        $secondPart .= 'Content-Disposition: form-data; name="dummy"; filename="dummy"' . $CRLF;
        $secondPart .= "Content-Transfer-Encoding: binary" . $CRLF;
        $secondPart .= "Content-Type: application/octet-stream" . $CRLF . $CRLF;
        $secondPart .= $multiPartImageData;
        $secondPart .= $CRLF;
        $secondPart .= "--" . $boundary . "--" . $CRLF;

        $request_body = $firstPart . $secondPart;

        $respXmlStr = L5ebtapiController::multiPartRequest('UploadSiteHostedPictures', $request_body, $boundary);   // send multi-part request and get string XML response

        if (stristr($respXmlStr, 'HTTP 404')) {

            Log::error('eBay API Call: uploadSiteHostedPictures() 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try the request' .
                'again.'];

        }

        if ($respXmlStr == '') {

            Log::error('eBay API Call: uploadSiteHostedPictures() Error sending request' .
                'the XML response is an empty string');

            return ['Error:' => 'The XML response is an empty string. Please verify all eBay API settings are correct' .
            'and try the request again.'];

        }

        $respXmlObj = simplexml_load_string($respXmlStr);

        if ($respXmlObj->SiteHostedPictureDetails->FullURL) {

            $picURL = ['eBay_Picture_Url' => (string)$respXmlObj->SiteHostedPictureDetails->FullURL];

        } else {

            $picURL = ['Error:' => 'An error occurred While processing the uploadSiteHostedPictures() request.' .
                'Please verify all eBay API settings and input are correct and try the request again.'];

        }

        return $picURL;

    }// END of uploadSiteHostedPictures($multiPartImageData, $image_name)

    /**
     * Method: addFixedPriceItem(array $attributes) - List a single eBay Fixed Priced Item.
     *
     * @param $attributes
     *
     * @return Array The eBay item id and associated fees OR Array with a key = 'Error:' and Value = 'The error message'
     * EX. ['Error:' => 'An error occurred during the request. please verify all settings are correct and try again.']
     */
    public function addFixedPriceItem(array $attributes)
    {
        $request_body = '<?xml version="1.0" encoding="utf-8"?>
                                <AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                                <RequesterCredentials>
                                <eBayAuthToken>' . $this->api_user_token . '</eBayAuthToken>
                                </RequesterCredentials>

                                <!-- Call-specific Input Fields -->

                                <Item>
                                <Title>' . $attributes['Item_Title'] . '</Title>
                                <Description>' . $attributes['Item_Description'] . '</Description>
                                <PrimaryCategory>
                                <CategoryID>' . $attributes['Item_Category_Id'] . '</CategoryID>
                                </PrimaryCategory>
                                <StartPrice>' . $attributes['Item_Start_Price'] . '</StartPrice>
                                <CategoryMappingAllowed>true</CategoryMappingAllowed>
                                <ConditionID>' . $attributes['Item_Condition_Id'] . '</ConditionID>
                                <Country>' . $attributes['Item_Country'] . '</Country>
                                <Currency>' . $attributes['Item_Currency'] . '</Currency>
                                <DispatchTimeMax>2</DispatchTimeMax>
                                <ListingDuration>' . $attributes['Item_Listing_Duration'] . '</ListingDuration>
                                <ListingType>' . $attributes['Item_Listing_Type'] . '</ListingType>
                                <PaymentMethods>' . $attributes['Item_Payment_Methods'] . '</PaymentMethods>
                                <PayPalEmailAddress>' . $attributes['Item_Paypal_Email'] . '</PayPalEmailAddress>
                                <PictureDetails>
                                <GalleryType>' . $attributes['Itm_Gallery_Type'] . '</GalleryType>
                                <PictureSource>' . $attributes['Itm_Picture_Source'] . '</PictureSource>
                                ';

        foreach ($attributes['Item_Image_Urls'] as $image_url) {

            $request_body .= '<PictureURL>' . $image_url . '</PictureURL>';

        }

        $request_body .= '
                                </PictureDetails>

                                <PostalCode>' . $attributes['Itm_Postal_Code'] . '</PostalCode>

                                <ItemSpecifics>
                                <NameValueList>
                                <Name>Brand</Name>
                                <Value>' . $attributes['Item_Brand'] . '</Value>
                                </NameValueList>
                                <NameValueList>
                                <Name>MPN</Name>
                                <Value>' . $attributes['Item_Mpn'] . '</Value>
                                </NameValueList>
                                <NameValueList>
                                <Name>Color</Name>
                                <Value>' . $attributes['Item_Color'] . '</Value>
                                </NameValueList>
                                <NameValueList>
                                <Name>Model</Name>
                                <Value>' . $attributes['Item_Model'] . '</Value>
                                </NameValueList>
                                </ItemSpecifics>

                                <Quantity>1</Quantity>

                                <ReturnPolicy>
                                <ReturnsAcceptedOption>' . $attributes['RetPol_Returns_Accepted'] . '</ReturnsAcceptedOption>
                                <RefundOption>' . $attributes['RetPol_Refund_Option'] . '</RefundOption>
                                <ReturnsWithinOption>' . $attributes['RetPol_Returns_Within'] . '</ReturnsWithinOption>
                                <Description>' . $attributes['RetPol_Description'] . '</Description>
                                <ShippingCostPaidByOption>' . $attributes['RetPol_Shipping_Cost_Paid_By'] . '</ShippingCostPaidByOption>
                                </ReturnPolicy>

                                <ShippingDetails>

                                <ShippingType>' . $attributes['Shipping_Details_Shipping_Type'] . '</ShippingType>

                                <ShippingServiceOptions>
                                <ShippingServicePriority>' . $attributes['Shipping_Details_Shipping_Service_Priority'] . '</ShippingServicePriority>
                                <ShippingService>' . $attributes['Shipping_Details_Shipping_Service'] . '</ShippingService>
                                <FreeShipping>' . $attributes['Shipping_Details_Free_Shipping'] . '</FreeShipping>
                                <ShippingServiceAdditionalCost currencyID="' . $attributes['Shipping_Details_Currency_Id'] . '">' . $attributes['Shipping_Details_Additional_Cost'] . '</ShippingServiceAdditionalCost>
                                </ShippingServiceOptions>

                                </ShippingDetails>

                                <Site>' . $attributes['Item_Site'] . '</Site>
                                </Item>

                                <!-- Standard Input Fields -->

                                <ErrorLanguage>' . $this->api_error_language . '</ErrorLanguage>
                                <Version>' . $this->api_compatibility_level . '</Version>
                                <WarningLevel>' . $this->api_warning_level . '</WarningLevel>
                                </AddFixedPriceItemRequest>';

        $responseXml = L5ebtapiController::request('AddFixedPriceItem', $request_body);

        if (stristr($responseXml, 'HTTP 404')) {

            Log::error('eBay API Call: addFixedPriceItem() 404 Not Found');

            return ['Error:' => '404 Not Found. Please verify all eBay API settings are correct and try the request again.'];

        }

        if ($responseXml == '') {

            Log::error('eBay API Call: addFixedPriceItem() Error sending request. The XML response is an empty string');

            return ['Error:' => 'There was an error sending request the XML response is an empty string. Please verify all' .
            'eBay API settings are correct and try the request again.'];

        }

        //Xml string is parsed and creates a DOM Document object
        $responseDoc = new DomDocument();

        $responseDoc->loadXML($responseXml);

        //dd($responseDoc->saveXML());

        $ack = $responseDoc->getElementsByTagName('Ack');

        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Failure' || $ack->item(0)->nodeValue == 'Warning') {

            //get any error nodes
            $errors = $responseDoc->getElementsByTagName('Errors');

            //if there are error nodes return the error message (array)
            if ($errors->length > 0) {

                $code = $errors->item(0)->getElementsByTagName('ErrorCode');

                $shortMsg = $errors->item(0)->getElementsByTagName('ShortMessage');

                $longMsg = $errors->item(0)->getElementsByTagName('LongMessage');

                if ($ack->item(0)->nodeValue == 'Failure') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::error('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::error('eBay API Call: AddFixedPriceItem() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' .
                            $shortMsg->item(0)->nodeValue . ' : Long Message: ' . $longMsg->item(0)->nodeValue];

                    } else {

                        Log::error('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        return ['Error:' => $code->item(0)->nodeValue . ' : Short Message: ' . $shortMsg->item(0)->nodeValue];

                    }

                }
                if ($ack->item(0)->nodeValue == 'Warning') {

                    //if there is a long message (ie ErrorLevel=1), construct the error message array with short & long message
                    if (count($longMsg) > 0) {

                        Log::warning('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                        Log::warning('eBay API Call: AddFixedPriceItem() Long message: ' .
                            $longMsg->item(0)->nodeValue);

                    } else {

                        Log::warning('eBay API Call: AddFixedPriceItem() Short message: ' .
                            $code->item(0)->nodeValue . ' : ' . $shortMsg->item(0)->nodeValue);

                    }

                }

            }


        }
        if ($ack->item(0)->nodeValue && $ack->item(0)->nodeValue == 'Success' || $ack->item(0)->nodeValue == 'Warning') {

            $xml = simplexml_load_string($responseDoc->saveXML());

            if ($xml->ItemID) {

                $data = json_encode($xml);

                return json_decode($data, TRUE);

                //return (string)$xml->ItemID;

            }

        }
        else {

            Log::error('eBay API Call: AddFixedPriceItem(): An error occurred during the AddFixedPriceItem() ebay API' .
                'call. Please verify all API settings are correct and then try the request again.');

            return ['Error:' => 'An error occurred during the AddFixedPriceItem() ebay API call. Please verify all' .
                'API settings are correct and then try the request again.'];

        }


    }// END - addFixedPriceItem(array $attributes)

    /**
     * Method: request($call_name, $request_body) - Make an eBay API request.
     *
     * @param $call_name the eBay API call name
     * @param $request_body the body of the request
     * @return mixed
     */
    public function request($call_name, $request_body)
    {

        $client = new Client();

        try {

            $response = $client->post($this->api_url, array(
                'verify' => $this->api_verify_ssl,
                'headers' => array(
                    'Content-Type' => 'text/xml',
                    'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->api_compatibility_level,
                    'X-EBAY-API-DEV-NAME' => $this->api_dev_id,
                    'X-EBAY-API-APP-NAME' => $this->api_app_id,
                    'X-EBAY-API-CERT-NAME' => $this->api_cert_id,
                    'X-EBAY-API-SITEID' => $this->api_site_id,
                    'X-EBAY-API-CALL-NAME' => $call_name
                ),
                'body' => $request_body
            ));

        } catch (\GuzzleHttp\Exception\ServerException $e) {

            $response = $e->getResponse();

            Log::warning($response->getBody()->getContents());

        }

        $body = $response->getBody()->getContents();

        return $body;

    }// END - request($call_name, $request_body)

    /**
     * Method: multiPartRequest($call_name, $request_body, $boundary) - Make an eBay API multi-part request.
     *
     * @param $call_name the eBay API call name
     * @param $request_body the body of the request
     * @param $boundary the boundary for the multi-part data
     * @return mixed
     */
    public function multiPartRequest($call_name, $request_body, $boundary)
    {

        $client = new Client();

        try {

            $response = $client->post($this->api_url, array(
                'verify' => $this->api_verify_ssl,
                'headers' => array(
                    'HTTP' => '1.0',
                    'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
                    'Content-Length' => strlen($request_body),
                    'X-EBAY-API-COMPATIBILITY-LEVEL' => $this->api_compatibility_level,
                    'X-EBAY-API-DEV-NAME' => $this->api_dev_id,
                    'X-EBAY-API-APP-NAME' => $this->api_app_id,
                    'X-EBAY-API-CERT-NAME' => $this->api_cert_id,
                    'X-EBAY-API-SITEID' => $this->api_site_id,
                    'X-EBAY-API-CALL-NAME' => $call_name
                ),
                'body' => $request_body
            ));

        } catch (\GuzzleHttp\Exception\ServerException $e) {

            $response = $e->getResponse();

            Log::warning($response->getBody()->getContents());

        }

        return $response->getBody()->getContents();

    } // END - multiPartRequest($call_name, $request_body, $boundary)

    public function xmlstr_to_array($xmlstr) {
        $doc = new DOMDocument();
        $doc->loadXML($xmlstr);
        $root = $doc->documentElement;
        $output = L5ebtapiController::domnode_to_array($root);
        $output['@root'] = $root->tagName;
        return $output;
    }

    public function domnode_to_array($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = L5ebtapiController::domnode_to_array($child);
                    if(isset($child->tagName)) {
                        $t = $child->tagName;
                        if(!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    }
                    elseif($v || $v === '0') {
                        $output = (string) $v;
                    }
                }
                if($node->attributes->length && !is_array($output)) { //Has attributes but isn't an array
                    $output = array('@content'=>$output); //Change output into an array.
                }
                if(is_array($output)) {
                    if($node->attributes->length) {
                        $a = array();
                        foreach($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if(is_array($v) && count($v)==1 && $t!='@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
                break;
        }
        return $output;
    }



}// END of class L5ebtapiController