<?php
/**
 * Zarinpal Transaction Checker
 * 
 * Fetches transaction data from Zarinpal panel and stores session IDs and authorities.
 * 
 * @version 1.0
 * @license MIT
 */

// ==================== تنظیمات ====================
$terminal_id = "TERMINAL_ID_SHOMA";      // شناسه ترمینال زرین‌پال شما
$cookie = "COOKIE_AUTH_SHOMA";          // کوکی احراز هویت زرین‌پال
$baggage = "";                         // مقدار اختیاری baggage
$sentry = "";                          // مقدار اختیاری sentry trace
$useragent = "Mozilla/5.0...";        // رشته user agent
$output_file = __DIR__ . '/data/truepay.json';  // مسیر فایل خروجی
// ==================== API FUNCTIONS ====================

/**
 * Fetches active sessions from Zarinpal API
 * @return string JSON response
 */
function getSessions() {
    global $terminal_id, $cookie;
    
    $query = '{"operationName":"GetSessions","variables":{"filter":"ACTIVE","limit":15,"offset":0,"terminal_id":"'.$terminal_id.'"},"query":"query GetSessions($reconciliation_id: ID, $filter: FilterEnum, $terminal_id: ID, $offset: Int, $limit: Int, $type: SessionTypeEnum, $amount: Int, $note: String, $max_amount: Int, $min_amount: Int, $created_from_date: DateTime, $created_to_date: DateTime, $id: ID, $reference_id: String, $relation_id: ID, $mobile: CellNumber, $email: String, $description: String, $card_pan: String, $rrn: String) {\\n  Session: Session(\\n    filter: $filter\\n    type: $type\\n    note: $note\\n    terminal_id: $terminal_id\\n    offset: $offset\\n    limit: $limit\\n    amount: $amount\\n    max_amount: $max_amount\\n    min_amount: $min_amount\\n    mobile: $mobile\\n    created_from_date: $created_from_date\\n    created_to_date: $created_to_date\\n    id: $id\\n    reference_id: $reference_id\\n    pagination: true\\n    relation_id: $relation_id\\n    card_pan: $card_pan\\n    email: $email\\n    description: $description\\n    rrn: $rrn\\n    reconciliation_id: $reconciliation_id\\n  ) {\\n    id\\n    type\\n    status\\n    created_at\\n    description\\n    reconciliation_id\\n    amount\\n    fee\\n    timeline {\\n      refund_id\\n      refund_status\\n      reconciled_id\\n      reconciled_time\\n      reconciled_status\\n      __typename\\n    }\\n    __typename\\n  }\\n  Pagination {\\n    total\\n    last_page\\n    __typename\\n  }\\n}"}';

    $headers = [
        'accept: */*',
        'accept-encoding: gzip, deflate, br, zstd',
        'accept-language: en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7,zh-CN;q=0.6,zh;q=0.5,sq;q=0.4',
        'baggage: ' . $baggage,
        'connection: keep-alive',
        'content-length: 1427',
        'content-type: application/json',
        'Cookie: ' . $cookie,
        'host: next.zarinpal.com',
        'origin: https://next.zarinpal.com',
        'sec-ch-ua: "Chromium";v="136", "Google Chrome";v="136", "Not.A/Brand";v="99"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "Windows"',
        'sec-fetch-dest: empty',
        'sec-fetch-mode: cors',
        'sec-fetch-site: same-origin',
        'sentry-trace: '. $sentry,
        'user-agent: '. $useragent,
        'x-request-type: graphql-without-status'
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://next.zarinpal.com/api/v4/graphql',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $query,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

/**
 * Fetches session details by ID
 * @param string $id Session ID
 * @return string JSON response
 */
function getSessionById($id) {
    global $terminal_id, $cookie;
    
    $query = '{"operationName":"SessionById","variables":{"id":"'.$id.'","terminal_id":"'.$terminal_id.'"},"query":"query SessionById($terminal_id: ID, $id: ID) {\\n  Session(terminal_id: $terminal_id, id: $id) {\\n    id\\n    note\\n    fee_detail {\\n      sms\\n      ayan\\n      raw_fee\\n      __typename\\n    }\\n    notes {\\n      id\\n      content\\n      created_at\\n      updated_at\\n      user {\\n        name\\n        __typename\\n      }\\n      __typename\\n    }\\n    wage_payouts {\\n      id\\n      __typename\\n    }\\n    reference_id\\n    reconciliation_id\\n    type\\n    fee\\n    fee_type\\n    status\\n    amount\\n    authority\\n    description\\n    created_at\\n    payer_info {\\n      card_holder_name\\n      description\\n      email\\n      mobile\\n      name\\n      order_id\\n      zarin_link_id\\n      custom_field_1_name\\n      custom_field_2_name\\n      custom_field_1\\n      custom_field_2\\n      __typename\\n    }\\n    terminal {\\n      id\\n      refund_active\\n      __typename\\n    }\\n    session_tries {\\n      is_card_mobile_verified\\n      card_info {\\n        name\\n        slug\\n        __typename\\n      }\\n      agent {\\n        country_code\\n        __typename\\n      }\\n      card_pan\\n      payer_ip\\n      rrn\\n      status\\n      __typename\\n    }\\n    refund {\\n      id\\n      session_id\\n      instant_payout {\\n        id\\n        amount\\n        fee\\n        terminal {\\n          id\\n          __typename\\n        }\\n        bank_account {\\n          id\\n          iban\\n          holder_name\\n          issuing_bank {\\n            name\\n            slug\\n            slug_image\\n            __typename\\n          }\\n          __typename\\n        }\\n        reference_id\\n        reconciled_at\\n        created_at\\n        status\\n        __typename\\n      }\\n      __typename\\n    }\\n    product {\\n      title\\n      __typename\\n    }\\n    timeline {\\n      canceled_time\\n      created_time\\n      in_bank_name\\n      in_bank_time\\n      reconciled_id\\n      reconciled_time\\n      reconciled_status\\n      reconciled_success_time\\n      settled_time\\n      verified_reference\\n      verified_time\\n      refund_status\\n      refund_amount\\n      refund_time\\n      __typename\\n    }\\n    __typename\\n  }\\n}"}';

    $headers = [
        'Accept-Encoding: gzip, deflate, br, zstd',
        'Accept-Language: en-US,en;q=0.9,fa-IR;q=0.8,fa;q=0.7,zh-CN;q=0.6,zh;q=0.5,sq;q=0.4',
        'Connection: keep-alive',
        'Content-Length: 2224',
        'Cookie: ' . $cookie,
        'Host: next.zarinpal.com',
        'Origin: https://next.zarinpal.com',
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-origin',
        'user-agent: Mozilla/4.0 (Windows NT 11.0; Win32; x32) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/536.66',
        'accept: */*',
        'baggage: ' . $baggage,
        'content-type: application/json',
        'sec-ch-ua: "Chromium";v="136", "Google Chrome";v="136", "Not.A/Brand";v="99"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "Windows"',
        'sentry-trace: '. $sentry,
        'x-request-type: graphql-without-status'
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://next.zarinpal.com/api/v4/graphql',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $query,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// ==================== MAIN EXECUTION ====================

// Create data directory if not exists
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

// Fetch active sessions
$response = getSessions();
$data = json_decode($response, true);

// Extract session IDs
$ids = [];
if (isset($data['data']['Session']) && is_array($data['data']['Session'])) {
    foreach ($data['data']['Session'] as $session) {
        if (isset($session['id'])) {
            $ids[] = $session['id'];
        }
    }
}

// Load existing data
$existingData = [];
if (file_exists($output_file)) {
    $existingData = json_decode(file_get_contents($output_file), true) ?? [];
}

// Merge new IDs with existing ones
$allIds = array_unique(array_merge($existingData, $ids));

// Save IDs to file
file_put_contents($output_file, json_encode($allIds, JSON_PRETTY_PRINT));

// Fetch authorities for each session
$authorities = [];
foreach ($allIds as $id) {
    $sessionResponse = getSessionById($id);
    $sessionData = json_decode($sessionResponse, true);
    
    if (isset($sessionData['data']['Session'][0]['authority'])) {
        $authorities[$id] = $sessionData['data']['Session'][0]['authority'];
    }
}

// Save authorities to file
if (!empty($authorities)) {
    $currentData = json_decode(file_get_contents($output_file), true) ?? [];
    $currentData['authorities'] = $authorities;
    file_put_contents($output_file, json_encode($currentData, JSON_PRETTY_PRINT));
}

// Output response
echo $response;
