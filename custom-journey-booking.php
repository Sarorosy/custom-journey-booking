<?php
/**
 * Plugin Name: Custom Journey Booking Form
 * Description: 3-step journey booking form (Fare → Journey → Personal)
 * Version: 1.0
 * Author: Saravanan Sri
 */

if (!defined('ABSPATH')) exit;

class CustomJourneyBooking {

    public function __construct() {
        add_shortcode('journey_booking_form', [$this, 'render_form']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_action('wp_ajax_submit_journey_booking', [$this, 'submit_form']);
        add_action('wp_ajax_nopriv_submit_journey_booking', [$this, 'submit_form']);
        
        // Ensure database is up to date
        if (get_option('journey_booking_db_version') !== '1.4') {
            self::activate();
            update_option('journey_booking_db_version', '1.4');
        }

        // Add iCal download action
        add_action('wp_ajax_download_booking_ics', [$this, 'download_ics']);
        add_action('wp_ajax_nopriv_download_booking_ics', [$this, 'download_ics']);
    }
    
    



    /* ---------------------------------------------------------
     * 1. CREATE TABLE (CALLED ON ACTIVATION)
     * --------------------------------------------------------- */
    public static function activate() {
        global $wpdb;

        $table = $wpdb->prefix . 'journey_bookings';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id VARCHAR(20) NOT NULL,
            journey_type VARCHAR(20),
            pickup TEXT,
            pickup_hotel TEXT,
            pickup_train_station VARCHAR(100),
            destination TEXT,
            destination_hotel TEXT,
            destination_train_station VARCHAR(100),
            passengers INT,
            vehicle VARCHAR(20),
            payment VARCHAR(50),
            pickup_date DATE,
            pickup_time TIME,
            flight_number VARCHAR(50),
            has_luggage VARCHAR(5),
            luggage_count INT,
            luggage_size VARCHAR(20),
            has_baby VARCHAR(5),
            baby_seat INT,
            booster_seat INT,
            notes TEXT,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            email VARCHAR(150),
            phone VARCHAR(30),
            amount VARCHAR(20),
            pickup_address TEXT,
            destination_address TEXT,
            input_address TEXT,
            return_date DATE,
            return_time TIME,
            return_flight_number VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /* ---------------------------------------------------------
     * 2. ASSETS
     * --------------------------------------------------------- */
    public function assets() {
        wp_enqueue_style(
            'journey-booking-css',
            plugin_dir_url(__FILE__) . 'assets/css/booking.css',
            [],
            '1.0'
        );

        wp_enqueue_style(
            'virtual-select-css',
            'https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.45/dist/virtual-select.min.css',
            [],
            '1.0.45'
        );

        wp_enqueue_script(
            'virtual-select-js',
            'https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.45/dist/virtual-select.min.js',
            [],
            '1.0.45',
            true
        );

        wp_enqueue_style(
            'intl-tel-input-css',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@24.4.0/build/css/intlTelInput.css',
            [],
            '24.4.0'
        );

        wp_enqueue_script(
            'intl-tel-input-js',
            'https://cdn.jsdelivr.net/npm/intl-tel-input@24.4.0/build/js/intlTelInput.min.js',
            [],
            '24.4.0',
            true
        );

        wp_enqueue_script(
            'journey-booking-js',
            plugin_dir_url(__FILE__) . 'assets/js/booking.js',
            ['jquery', 'virtual-select-js', 'intl-tel-input-js'],
            time(),
            true
        );

        wp_localize_script('journey-booking-js', 'JB_Ajax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'destinationMappings' => $this->get_destination_mappings()
        ]);
    }

    /* ---------------------------------------------------------
     * 3. FORM DATA
     * --------------------------------------------------------- */
    public function get_destination_mappings() {
        return [
            "Charles de Gaulle Airport (CDG)" => [
                "Beauvais Airport (BVA)",
                "Disneyland and Hotels",
                "Paris (Hotels, Apartments, Historic monuments)",
                "Paris Train Stations",
                "Versailles"
            ],
            "Orly Airport (ORY)" => [
                "Charles de Gaulle Airport (CDG)",
                "Disneyland and Hotels",
                "Paris (Hotels, Apartments, Historic monuments)",
                "Paris Train Stations",
                "Versailles"
            ],
            "Beauvais Airport (BVA)" => [
                "Charles de Gaulle Airport (CDG)",
                "Orly Airport (ORY)",
                "Disneyland and Hotels",
                "Paris (Hotels, Apartments, Historic monuments)",
                "Paris Train Stations",
                "Versailles"
            ],
            "Disneyland and Hotels" => [
                "Charles de Gaulle Airport (CDG)",
                "Orly Airport (ORY)",
                "Beauvais Airport (BVA)",
                "Paris (Hotels, Apartments, Historic monuments)",
                "Paris Train Stations",
                "Versailles"
            ],
            "Paris (Hotels, Apartments, Historic monuments)" => [
                "Charles de Gaulle Airport (CDG)",
                "Orly Airport (ORY)",
                "Beauvais Airport (BVA)",
                "Disneyland and Hotels",
                "Paris (Hotels, Apartments, Historic monuments)",
                "Paris Train Stations",
                "Versailles"
            ],
            "Paris Train Stations" => [
                "Charles de Gaulle Airport (CDG)",
                "Orly Airport (ORY)",
                "Beauvais Airport (BVA)",
                "Disneyland and Hotels",
                "Paris (Hotels, Apartments, Historic monuments)",
                "Paris Train Stations",
                "Versailles"
            ],
            "Versailles" => [
                "Charles de Gaulle Airport (CDG)",
                "Orly Airport (ORY)",
                "Beauvais Airport (BVA)",
                "Disneyland and Hotels",
                "Paris (Hotels, Apartments, Historic monuments)",
                "Paris Train Stations"
            ]
        ];
    }

    public function get_locations() { return array_keys($this->get_destination_mappings()); }

    public function render_form() {
        ob_start();
        include __DIR__ . '/form-template.php';
        return ob_get_clean();
    }

    /* ---------------------------------------------------------
     * 4. AJAX HANDLER (STORE DATA)
     * --------------------------------------------------------- */
     
     private function generate_booking_ref() {
        return 'DHT' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 7));
    }
    
    
    public function submit_form() {

        if (empty($_POST['data'])) {
            wp_send_json_error('No data received');
        }

        parse_str($_POST['data'], $formData);

        // Combine dial code and phone number
        if (isset($formData['dial_code'])) {
            $formData['phone'] = trim($formData['dial_code'] . ' ' . ($formData['phone'] ?? ''));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'journey_bookings';
        
        do {
            $booking_ref = $this->generate_booking_ref();
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}journey_bookings WHERE booking_id = %s",
                    $booking_ref
                )
            );
        } while ($exists);

        $wpdb->insert($table, [
            'booking_id'      => $booking_ref,
            'journey_type'  => $formData['journey_type'] ?? '',
            'pickup'        => $formData['pickup'] ?? '',
            'pickup_hotel'  => $formData['pickup_disney_hotel'] ?? ($formData['pickup_hotel'] ?? ''),
            'pickup_train_station' => $formData['pickup_train_station'] ?? '',
            'destination'   => $formData['destination'] ?? '',
            'destination_hotel' => $formData['destination_disney_hotel'] ?? ($formData['destination_hotel'] ?? ''),
            'destination_train_station' => $formData['destination_train_station'] ?? '',
            'passengers'    => intval($formData['passengers'] ?? 0),
            'vehicle'       => $formData['vehicle'] ?? '',
            'payment'       => $formData['payment'] ?? '',
            'pickup_date'   => $formData['pickup_date'] ?? null,
            'pickup_time'   => $formData['pickup_time'] ?? null,
            'flight_number' => $formData['flight_number'] ?? '',
            'has_luggage'   => $formData['has_luggage'] ?? 'No',
            'luggage_count' => intval($formData['luggage_count'] ?? 0),
            'luggage_size'  => $formData['luggage_size'] ?? '',
            'has_baby'      => $formData['has_baby'] ?? 'No',
            'baby_seat'     => intval($formData['baby_seat'] ?? 0),
            'booster_seat'  => intval($formData['booster_seat'] ?? 0),
            'notes'         => $formData['notes'] ?? '',
            'first_name'    => $formData['first_name'] ?? '',
            'last_name'     => $formData['last_name'] ?? '',
            'email'         => $formData['email'] ?? '',
            'phone'         => $formData['phone'] ?? '',
            'amount'        => $formData['amount'] ?? '',
            'pickup_address' => $formData['pickup_address'] ?? '',
            'destination_address' => $formData['destination_address'] ?? '',
            'input_address' => $formData['pickup_address'] ?? '', // Fallback for old field
            'return_date' => $formData['return_date'] ?? null,
            'return_time' => $formData['return_time'] ?? null,
            'return_flight_number' => $formData['return_flight_number'] ?? '',
        ]);

        if ($wpdb->last_error) {
            wp_send_json_error($wpdb->last_error);
        }
        
        /* ---------------------------------------------------------
     * 📧 EMAIL TO CUSTOMER
     * --------------------------------------------------------- */
    $to = sanitize_email($formData['email']);
    $subject = "Booking Confirmation – {$booking_ref}";

    $google_cal_url = $this->get_google_calendar_url($formData, $booking_ref);
    $ical_url = admin_url('admin-ajax.php') . '?action=download_booking_ics&booking_ref=' . $booking_ref;

    $calendar_html = '
    <!-- CALENDAR SECTION -->
    <tr>
      <td style="padding: 25px; text-align: center; border-bottom: 1px solid #e5e7eb; background: #ffffff;">
        <div style="margin-bottom: 12px;">
          <img src="https://img.icons8.com/color/48/000000/calendar--v1.png" width="30" style="vertical-align: middle; margin-right: 8px;">
          <span style="font-size: 20px; font-weight: bold; color: #19365f; vertical-align: middle;">Add to Your Calendar</span>
        </div>
        <p style="margin: 0 0 20px; font-size: 15px; color: #475569;">Don\'t forget your pickup! Add this to your calendar:</p>
        <div>
          <a href="'.$google_cal_url.'" target="_blank" style="display: inline-block; padding: 12px 20px; background: #19365f; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 5px; font-size: 14px;">
            <img src="https://img.icons8.com/color/48/000000/google-calendar--v2.png" width="18" style="vertical-align: middle; margin-right: 8px;">
            Add to Google Calendar
          </a>
          <a href="'.$ical_url.'" style="display: inline-block; padding: 12px 20px; background: #475569; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 5px; font-size: 14px;">
            <img src="https://img.icons8.com/color/48/000000/box-important.png" width="18" style="vertical-align: middle; margin-right: 8px;">
            Download iCal/Outlook
          </a>
        </div>
      </td>
    </tr>';


    $message = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Booking Confirmation</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:30px 0;">
<tr>
<td align="center">

  <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

    <!-- HEADER -->
    <tr>
      <td style="background:#19365f; color:#ffffff; padding:25px; text-align:center;">
        <h1 style="margin:0; font-size:26px;">Booking Confirmation</h1>
        <p style="margin:8px 0 0; color:#9fd7ff; font-size:14px;">'.$booking_ref.'</p>
        <p style="margin:6px 0 0; font-size:14px;">Status: <strong>Confirmed</strong></p>
      </td>
    </tr>

    '.$calendar_html.'

    <!-- BODY -->
    <tr>
      <td style="padding:25px;">

        <!-- CUSTOMER DETAILS -->
        <h3 style="border-bottom:1px solid #e5e7eb; padding-bottom:6px;">Customer Details</h3>
        <p>
          <strong>First Name:</strong> '.$formData['first_name'].'<br>
          <strong>Last Name:</strong> '.$formData['last_name'].'<br>
          <strong>Email:</strong> '.$formData['email'].'<br>
          <strong>Phone:</strong> '.$formData['phone'].'
        </p>

        <!-- JOURNEY DETAILS -->
        <h3 style="border-bottom:1px solid #e5e7eb; padding-bottom:6px;">Journey Details</h3>
        <p>
          <strong>Journey Type:</strong> '.$formData['journey_type'].'<br>
          <strong>Pickup Location:</strong> '.$formData['pickup'].'<br>
          <strong>Pickup Details:</strong> '.($formData['pickup_disney_hotel'] ?: ($formData['pickup_hotel'] ?: ($formData['pickup_train_station'] ?: 'N/A'))).'<br>
          <strong>Pickup Date:</strong> '.date('d F Y', strtotime($formData['pickup_date'])).'<br>
          <strong>Pickup Time:</strong> '.$formData['pickup_time'].'<br>
          <strong>Destination:</strong> '.$formData['destination'].'<br>
          <strong>Destination Details:</strong> '.($formData['destination_disney_hotel'] ?: ($formData['destination_hotel'] ?: ($formData['destination_train_station'] ?: 'N/A'))).'
          '.(!empty($formData['pickup_address']) ? '<br><strong>Pickup Address:</strong> '.$formData['pickup_address'] : '').'
          '.(!empty($formData['destination_address']) ? '<br><strong>Destination Address:</strong> '.$formData['destination_address'] : '').'<br>
          <strong>Flight/Train Number:</strong> '.($formData['flight_number'] ?: 'N/A').'
          '.($formData['journey_type'] === 'round' ? '
          <br><br>
          <strong>--- Return Journey ---</strong><br>
          <strong>Return Date:</strong> '.(!empty($formData['return_date']) ? date('d F Y', strtotime($formData['return_date'])) : 'N/A').'<br>
          <strong>Return Time:</strong> '.($formData['return_time'] ?: 'N/A').'<br>
          <strong>Return Flight/Train Number:</strong> '.($formData['return_flight_number'] ?: 'N/A').'
          ' : '').'
        </p>

        <!-- PASSENGER DETAILS -->
        <h3 style="border-bottom:1px solid #e5e7eb; padding-bottom:6px;">Passenger & Vehicle Details</h3>
        <p>
          <strong>Passengers:</strong> '.$formData['passengers'].'<br>
          <strong>Vehicle:</strong> '.ucfirst($formData['vehicle'] ?? '').'<br>
          <strong>Luggage:</strong> '.($formData['has_luggage'] === 'Yes' ? 'Yes (Count: '.$formData['luggage_count'].', Size: '.$formData['luggage_size'].')' : 'No').'<br>
          <strong>Baby Seat Required:</strong> '.($formData['has_baby'] ?? 'No').'
          '.(($formData['has_baby'] ?? 'No') === 'Yes' ? '<br><strong>Baby Seats (1–2 years):</strong> '.($formData['baby_seat'] ?? 0).'<br><strong>Booster Seats (3–8 years):</strong> '.($formData['booster_seat'] ?? 0) : '').'
        </p>

        <!-- NOTES -->
        <h3 style="border-bottom:1px solid #e5e7eb; padding-bottom:6px;">Price & Payment</h3>
        <p>
          <strong>Total Amount:</strong> €'.$formData['amount'].'<br>
          <strong>Payment Method:</strong> '.$formData['payment'].'
        </p>

        <div style="margin-top:20px; padding:15px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;">
          <p style="margin:0 0 8px; color:#1e293b;"><strong>Night Time Surcharge (10:00 PM to 6:00 AM) :</strong> +€15</p>
          <p style="margin:0 0 8px; color:#1e293b;"><strong>Payment :</strong> Payment is collected at the end of each ride.</p>
          <p style="margin:0 0 5px; color:#1e293b;"><strong>We accept the following payment methods :</strong></p>
          <ul style="margin:0; padding-left:20px; color:#334155;">
            <li>Cash</li>
            <li>Card</li>
            <li>Apple Pay</li>
          </ul>
        </div>

        '.(!empty($formData['notes']) ? '
        <h3 style="border-bottom:1px solid #e5e7eb; padding-bottom:6px;">Additional Notes</h3>
        <p>'.$formData['notes'].'</p>
        ' : '').'

        <p style="margin-top:30px;">
          Thank you for choosing <strong>Disney Holiday Transfer</strong>.<br>
          We will contact you shortly if we need any additional details.
        </p>

      </td>
    </tr>

    <!-- FOOTER -->
    <tr>
      <td style="background:#f1f5f9; text-align:center; padding:15px; font-size:12px; color:#6b7280;">
        © '.date('Y').' Disney Taxi Paris. All rights reserved.
      </td>
    </tr>

  </table>

</td>
</tr>
</table>

</body>
</html>';


    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Journey Booking <noreply@disneyholidaytransfer.com>'
    ];

    wp_mail($to, $subject, $message, $headers);

    /* ---------------------------------------------------------
     * 📧 EMAIL TO ADMIN
     * --------------------------------------------------------- */
    $admin_subject = "New Booking Alert – #{$booking_ref}";
    $admin_message = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>New Booking Notification</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding:30px 0;">
<tr>
<td align="center">

  <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb;">

    <!-- HEADER -->
    <tr>
      <td style="background:#0f172a; color:#ffffff; padding:25px; text-align:center;">
        <h1 style="margin:0; font-size:26px;">New Booking Received</h1>
        <p style="margin:8px 0 0; color:#94a3b8; font-size:14px;">Reference: <strong>'.$booking_ref.'</strong></p>
      </td>
    </tr>

    '.$calendar_html.'

    <!-- BODY -->
    <tr>
      <td style="padding:25px;">

        <!-- CUSTOMER DETAILS -->
        <h3 style="color:#0f172a; border-bottom:1px solid #e5e7eb; padding-bottom:6px; margin-top:0;">Customer Details</h3>
        <p style="font-size:14px; line-height:1.6; color:#334155;">
          <strong>Name:</strong> '.$formData['first_name'].' '.$formData['last_name'].'<br>
          <strong>Email:</strong> <a href="mailto:'.$formData['email'].'" style="color:#2563eb; text-decoration:none;">'.$formData['email'].'</a><br>
          <strong>Phone:</strong> <a href="tel:'.str_replace(' ', '', $formData['phone']).'" style="color:#2563eb; text-decoration:none;">'.$formData['phone'].'</a>
        </p>

        <!-- JOURNEY DETAILS -->
        <h3 style="color:#0f172a; border-bottom:1px solid #e5e7eb; padding-bottom:6px; margin-top:25px;">Journey Details</h3>
        <p style="font-size:14px; line-height:1.6; color:#334155;">
          <strong>Journey Type:</strong> '.ucfirst($formData['journey_type'] ?? '').'<br>
          <strong>Pickup:</strong> '.$formData['pickup'].'<br>
          <strong>Pickup Details:</strong> '.($formData['pickup_disney_hotel'] ?: ($formData['pickup_hotel'] ?: ($formData['pickup_train_station'] ?: 'N/A'))).'<br>
          <strong>Date & Time:</strong> '.date('d F Y', strtotime($formData['pickup_date'])).' at '.$formData['pickup_time'].'<br>
          <strong>Destination:</strong> '.$formData['destination'].'<br>
          <strong>Destination Details:</strong> '.($formData['destination_disney_hotel'] ?: ($formData['destination_hotel'] ?: ($formData['destination_train_station'] ?: 'N/A'))).'
          '.(!empty($formData['pickup_address']) ? '<br><strong>Pickup Address:</strong> '.$formData['pickup_address'] : '').'
          '.(!empty($formData['destination_address']) ? '<br><strong>Destination Address:</strong> '.$formData['destination_address'] : '').'<br>
          <strong>Flight/Train Number:</strong> '.($formData['flight_number'] ?: 'N/A').'
          '.($formData['journey_type'] === 'round' ? '
          <br><strong>Return Date:</strong> '.(!empty($formData['return_date']) ? date('d F Y', strtotime($formData['return_date'])) : 'N/A').' at '.($formData['return_time'] ?: 'N/A').'
          <br><strong>Return Flight/Train:</strong> '.($formData['return_flight_number'] ?: 'N/A').'
          ' : '').'
        </p>

        <!-- PASSENGER & VEHICLE DETAILS -->
        <h3 style="color:#0f172a; border-bottom:1px solid #e5e7eb; padding-bottom:6px; margin-top:25px;">Vehicle & Requirements</h3>
        <p style="font-size:14px; line-height:1.6; color:#334155;">
          <strong>Vehicle:</strong> '.ucfirst($formData['vehicle'] ?? '').'<br>
          <strong>Passengers:</strong> '.$formData['passengers'].'<br>
          <strong>Luggage:</strong> '.(($formData['has_luggage'] ?? '') === 'Yes' ? 'Yes (Count: '.($formData['luggage_count'] ?? 0).', Size: '.($formData['luggage_size'] ?? '').')' : 'No').'<br>
          <strong>Baby Seats:</strong> '.(($formData['has_baby'] ?? '') === 'Yes' ? ($formData['baby_seat'] ?: 0).' Baby, '.($formData['booster_seat'] ?: 0).' Booster' : 'No').'
        </p>

        <!-- PRICE & PAYMENT -->
        <h3 style="color:#0f172a; border-bottom:1px solid #e5e7eb; padding-bottom:6px; margin-top:25px;">Payment Information</h3>
        <p style="font-size:14px; line-height:1.6; color:#334155;">
          <strong>Amount:</strong> <span style="font-size:18px; color:#b91c1c; font-weight:bold;">€'.$formData['amount'].'</span><br>
          <strong>Payment Method:</strong> '.$formData['payment'].'
        </p>

        '.(!empty($formData['notes']) ? '
        <h3 style="color:#0f172a; border-bottom:1px solid #e5e7eb; padding-bottom:6px; margin-top:25px;">Admin Notes / Requests</h3>
        <p style="font-size:14px; line-height:1.6; color:#334155; background:#f8fafc; padding:10px; border-radius:4px; border-left:4px solid #e2e8f0;">'.$formData['notes'].'</p>
        ' : '').'

      </td>
    </tr>

    <!-- FOOTER -->
    <tr>
      <td style="background:#f8fafc; text-align:center; padding:15px; font-size:12px; color:#64748b; border-top:1px solid #e5e7eb;">
        This is an automated notification from your booking system.
      </td>
    </tr>

  </table>

</td>
</tr>
</table>

</body>
</html>';

    wp_mail(get_option('admin_email'), $admin_subject, $admin_message, $headers);

        wp_send_json_success([
            'message' => 'Booking stored successfully',
            'id' => $booking_ref,
            'amount' => $formData['amount'] ?? ''
        ]);
    }

    /* ---------------------------------------------------------
     * 5. CALENDAR & ICAL HELPERS
     * --------------------------------------------------------- */
    
    private function get_google_calendar_url($formData, $booking_ref) {
        $title = "Airport Transfer - " . $booking_ref;
        
        // Format dates for Google: YYYYMMDDTHHMMSS
        $pickup_dt = $formData['pickup_date'] . ' ' . $formData['pickup_time'];
        $start_time = date('Ymd\THis', strtotime($pickup_dt));
        $end_time = date('Ymd\THis', strtotime($pickup_dt . ' +1 hour'));
        
        $pickup = $formData['pickup'] . (!empty($formData['pickup_address']) ? " ({$formData['pickup_address']})" : "");
        $dest = $formData['destination'] . (!empty($formData['destination_address']) ? " ({$formData['destination_address']})" : "");
        
        $details = "Booking Reference: $booking_ref\n";
        $details .= "Pickup: $pickup\n";
        $details .= "Destination: $dest\n";
        $details .= "Vehicle: " . ucfirst($formData['vehicle'] ?? '') . "\n";
        $details .= "Passengers: " . ($formData['passengers'] ?? 0);

        return "https://www.google.com/calendar/render?action=TEMPLATE" .
               "&text=" . urlencode($title) .
               "&dates=" . $start_time . "/" . $end_time .
               "&details=" . urlencode($details) .
               "&location=" . urlencode($pickup);
    }

    public function download_ics() {
        $ref = isset($_GET['booking_ref']) ? sanitize_text_field($_GET['booking_ref']) : '';
        if (!$ref) wp_die('Missing booking reference');

        global $wpdb;
        $table = $wpdb->prefix . 'journey_bookings';
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE booking_id = %s", $ref), ARRAY_A);

        if (!$booking) wp_die('Booking not found');

        $pickup_dt = $booking['pickup_date'] . ' ' . $booking['pickup_time'];
        $start = date('Ymd\THis', strtotime($pickup_dt));
        $end = date('Ymd\THis', strtotime($pickup_dt . ' +1 hour'));

        $summary = "Airport Transfer - " . $booking['booking_id'];
        $pickup = $booking['pickup'] . (!empty($booking['pickup_address']) ? " ({$booking['pickup_address']})" : "");
        $dest = $booking['destination'] . (!empty($booking['destination_address']) ? " ({$booking['destination_address']})" : "");
        
        $description = "Booking Reference: {$booking['booking_id']}\\nPickup: $pickup\\nDestination: $dest";

        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename=booking-' . $ref . '.ics');

        echo "BEGIN:VCALENDAR\r\n";
        echo "VERSION:2.0\r\n";
        echo "PRODID:-//Disney Holiday Transfer//Booking System//EN\r\n";
        echo "BEGIN:VEVENT\r\n";
        echo "UID:" . $ref . "@disneyholidaytransfer.com\r\n";
        echo "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        echo "DTSTART:" . $start . "\r\n";
        echo "DTEND:" . $end . "\r\n";
        echo "SUMMARY:" . $summary . "\r\n";
        echo "DESCRIPTION:" . $description . "\r\n";
        echo "LOCATION:" . $pickup . "\r\n";
        echo "END:VEVENT\r\n";
        echo "END:VCALENDAR\r\n";
        exit;
    }
}

/* ---------------------------------------------------------
 * 5. ACTIVATE PLUGIN HOOK
 * --------------------------------------------------------- */
register_activation_hook(__FILE__, ['CustomJourneyBooking', 'activate']);

new CustomJourneyBooking();
