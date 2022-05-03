<?php
function aeam_get_access_token() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://mcpdbpr80z7fl1y24g0hkqz9gb21.auth.marketingcloudapis.com/v2/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
        "grant_type": "client_credentials",
        "client_id": "9z05dlejcr0cpiqrqof974qb",
        "client_secret": "yLRYXGUbb958qI0pxhZWQOz4",
        "scope": "data_extensions_read data_extensions_write",
        "account_id": "526003494"
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $response = json_decode($response);

    return $response->access_token;
}

function aeam_register_user($token , $fname , $email){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://mcpdbpr80z7fl1y24g0hkqz9gb21.rest.marketingcloudapis.com/data/v1/async/dataextensions/key:5E0E6046-C3C3-4B80-96CF-41AE0746E34E/rows',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
        "items": [
            {
                "FirstName": "'.$fname.'",
                "Email": "'.$email.'",
                "SignupStatus": "yes"
            }
        ]
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '.$token
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    
    return json_decode($response);

}

function aeam_order_status_change($token , $fname , $email , $orderID , $orderStatus , $deliveryStatus , $products){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://mcpdbpr80z7fl1y24g0hkqz9gb21.rest.marketingcloudapis.com/data/v1/async/dataextensions/key:B63BDC78-5D5E-4DE0-9714-10935D35AD66/rows',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS =>'{
        "items": [
            {
                "FirstName": "'.$fname.'",
                "Email": "'.$email.'",
                "Cart": "'.$products.'",
                "trackingId": "'.$orderID.'",
                "OrderDispatchStatus": "'.$orderStatus.'",
                "DeliveryStatus": "'.$deliveryStatus.'",
                "OrderId":"#'.$orderID.'"
            }
        ]
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '.$token
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    
    return json_decode($response);

}

function aeam_cart_abandonment_api ($token , $fname , $email , $orderID , $orderStatus , $deliveryStatus , $products , $phone){

    $curl = curl_init();
    $currentDate = date('m/d/Y');

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://mcpdbpr80z7fl1y24g0hkqz9gb21.rest.marketingcloudapis.com/data/v1/async/dataextensions/key:4557451D-1179-403A-A1FB-A7C31D101760/rows',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS =>'{
        "items": [
            {
                "FirstName": "'.$fname.'",
                "Email": "'.$email.'",
                "Cart": "'.$products.'",
                "trackingId": "'.$orderID.'",
                "OrderDispatchStatus": "'.$orderStatus.'",
                "DeliveryStatus": "'.$deliveryStatus.'",
                "OrderId":"#'.$orderID.'",
                "ProductAddedToCartDate": "'.$currentDate.'",
                "ProductInCart": "'.$products.'",
                "OrderPlaceDate": "",
                "Contact": "'.$phone.'"
            }
        ]
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer '.$token
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    
    return json_decode($response);

}