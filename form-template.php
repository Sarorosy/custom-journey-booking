<?php
$locations = $this->get_locations();

$destinationMappings = $this->get_destination_mappings();

?>
<div class="jb-wrapper">

  <!-- STEPS HEADER -->
  <div class="jb-steps" id="journeyBookingSteps">
    <span class="active"> <div class="jb-step-number">1</div> <span class="jb-step-name">Fare Details</span></span>
    <span> <div class="jb-step-number">2</div> <span class="jb-step-name">Journey Details</span></span>
    <span> <div class="jb-step-number">3</div> <span class="jb-step-name">Personal Details</span></span>
  </div>

  <form id="journeyBookingForm">
    <input type="hidden" name="amount" id="booking_amount" value="">


    <style>
      .jb-radio {
        display: flex;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 14px;
        margin-bottom: 24px;
        gap: 4px;
      }

      .jb-radio label {
        margin: 0 !important;
        padding: 10px 24px !important;
        border: none !important;
        border-radius: 10px !important;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px !important;
        color: #64748b !important;
        background: transparent !important;
        box-shadow: none !important;
        width: 50%;
        border: 1px solid #1e293b !important;
      }

      .jb-radio label:has(input:checked) {
        background: #9c1305 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(255, 90, 0, 0.25) !important;
      }

      .jb-radio input[type="radio"] {
        display: none !important;
      }
      
      

      .jb-radio label:hover:not(:has(input:checked)) {
        background: #e2e8f0 !important;
        color: #1e293b !important;
        border: 1px solid #1e293b !important;
      }

      /* Vehicle Selection Styling */
      .jb-vehicle {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
      }

      .jb-vehicle label {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: #ffffff;
        text-align: center;
        position: relative;
        overflow: hidden;
      }

      .jb-vehicle label:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      }

      .jb-vehicle label:has(input:checked) {
        border-color: #9c1305 !important;
        background: #fffaf5 !important;
        box-shadow: 0 8px 20px rgba(255, 90, 0, 0.15) !important;
      }

      .jb-vehicle input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
      }

      .jb-vehicle img {
        width: 100%;
        max-width: 140px;
        height: 100px;
        object-fit: contain;
        margin-bottom: 12px;
        transition: transform 0.3s ease;
      }

      .jb-vehicle label:hover img {
        transform: scale(1.05);
      }

      .jb-vehicle span {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
      }

      .jb-vehicle .car-price, 
      .jb-vehicle .van-price {
        color: #9c1305;
        font-weight: 800;
      }

      /* Checkmark indicator for active state */
      .jb-vehicle label:has(input:checked)::after {
        content: '✓';
        position: absolute;
        top: 10px;
        right: 10px;
        background: #9c1305;
        color: white;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(255, 90, 0, 0.3);
      }

      .jb-input-row {
        display: flex;
        gap: 20px;
        margin-bottom: 24px;
      }

      .jb-input-row .col {
        flex: 1;
      }
      
      .jb-input-row .col input,
      .jb-input-row .col select,
      .jb-input-row .col .vscomp-wrapper {
        margin-bottom: 0 !important;
      }

      .jb-step h3 {
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 12px;
        margin-bottom: 28px;
      }

      .jb-checkbox-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 24px;
      }

      .jb-checkbox label {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 10px !important;
        padding: 14px 20px !important;
        background: #ffffff !important;
        border: 2px solid #e2e8f0 !important;
        border-radius: 12px !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        font-weight: 600 !important;
        color: #475569 !important;
        margin: 0 !important;
        font-size: 14px !important;
      }

      .jb-checkbox label:has(input:checked) {
        background: #9c1305 !important;
        border-color: #9c1305 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(255, 90, 0, 0.2) !important;
      }

      .jb-checkbox input[type="checkbox"] {
        display: none !important;
      }

      .jb-checkbox label:hover:not(:has(input:checked)) {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
      }

      .jb-agreement {
        margin-bottom: 32px; 
        padding: 20px; 
        background: #f8fafc; 
        border-radius: 16px; 
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
      }

      .jb-agreement:hover {
        border-color: #9c1305;
        background: #fffaf5;
      }

      .jb-agreement label {
        display: flex !important; 
        align-items: center !important; 
        gap: 12px !important; 
        margin: 0 !important; 
        cursor: pointer !important; 
        font-weight: 500 !important;
        font-size: 14px !important;
        color: #475569 !important;
      }

      .jb-agreement input[type="checkbox"] {
        width: 20px !important; 
        height: 20px !important; 
        accent-color: #9c1305 !important;
        margin: 0 !important;
        cursor: pointer !important;
      }

      .jb-agreement a {
        color: #9c1305;
        text-decoration: none;
        font-weight: 700;
      }

      .jb-agreement a:hover {
        text-decoration: underline;
      }

      @media (max-width: 600px) {
        .jb-input-row {
          flex-direction: column;
          gap: 24px;
        }
        
        .jb-vehicle {
          flex-direction: column;
        }

        .jb-checkbox-group {
          grid-template-columns: 1fr;
        }
      }

      /* success */
      .jb-success {
        display: flex;
        justify-content: center;
        margin-top: 40px;
      }

      .jb-success-card {
        text-align: center;
        padding: 40px 30px;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(0,0,0,.08);
        max-width: 420px;
        width: 100%;
      }

      .jb-check {
        width: 70px;
        height: 70px;
        margin: 0 auto 20px;
        border-radius: 50%;
        border: 3px solid #28a745;
        color: #28a745;
        font-size: 36px;
        font-weight: bold;
        line-height: 64px;
      }

      .jb-submit[disabled] {
        opacity: 0.6;
        cursor: not-allowed;
      }

    </style>

    <!-- STEP 1 -->
    <div class="jb-step active">
      <h3>Journey Type</h3>

      <div class="jb-radio">
        <label><input type="radio" name="journey_type" value="single" checked> Single</label>
        <label><input type="radio" name="journey_type" value="round"> Round Trip</label>
      </div>

      <label>Pickup Location</label>
      <select name="pickup" id="pickup_location" placeholder="Select Pickup Location" data-silent-initial-value-set="true">
        <option value="">Select Pickup Location</option>
        <?php foreach ($locations as $loc): ?>
          <option value="<?= $loc ?>"><?= $loc ?></option>
        <?php endforeach; ?>
      </select>


      <div id="pickup_train_station_wrapper" style="display:none">
        <label>Select Train Station</label>
        <select name="pickup_train_station" id="pickup_train_station" placeholder="Select Train Station">
          <option value="">Select Train Station</option>
          <option value="Gare du Nord">Gare du Nord</option>
          <option value="Gare de Lyon">Gare de Lyon</option>
          <option value="Gare de L'Est">Gare de L'Est</option>
          <option value="Gare st Lazarre">Gare st Lazarre</option>
          <option value="Gare de Bercy">Gare de Bercy</option>
          <option value="Gare Montpernasse">Gare Montpernasse</option>
        </select>
      </div>

      <div id="pickup_disney_hotel_wrapper" style="display:none">
        <label>Select Disney Hotel</label>
        <select name="pickup_disney_hotel" id="pickup_disney_hotel" placeholder="Select Disney Hotel">
          <option value="">Select Disney Hotel</option>
          <option value="Disneyland paris (Park)">Disneyland paris (Park)</option>
          <option value="Disneyland hotel">Disneyland hotel</option>
          <option value="Disney's hotel Cheyenne">Disney's hotel Cheyenne</option>
          <option value="Disney's hotel Santa Fe">Disney's hotel Santa Fe</option>
          <option value="Disney's hotel Newport Bay Club">Disney's hotel Newport Bay Club</option>
          <option value="Disney's hotel Sequoia Lodge">Disney's hotel Sequoia Lodge</option>
          <option value="Disney's hotel Marvel Newyork">Disney's hotel Marvel Newyork</option>
          <option value="Hotel Explorers">Hotel Explorers</option>
          <option value="Dream castle">Dream castle</option>
          <option value="Grand magic hotel">Grand magic hotel</option>
          <option value="Hotel B&B Val de France Disney">Hotel B&B Val de France Disney</option>
          <option value="Campanile Val de FranceDisney">Campanile Val de FranceDisney</option>
          <option value="Village Nature">Village Nature</option>
          <option value="Radisson blu">Radisson blu</option>
          <option value="Marriott's Village">Marriott's Village</option>
          <option value="Stay city Marne la Valée">Stay city Marne la Valée</option>
          <option value="Adagio Val d'Europe 42 cours du Danube, Serris">Adagio Val d'Europe 42 cours du Danube, Serris</option>
          <option value="Adagio 18 cours de I Elbe Disneyland Paris, Serris">Adagio 18 cours de I Elbe Disneyland Paris, Serris</option>
          <option value="Elysée Val d'europe">Elysée Val d'europe</option>
          <option value="Ibis Val d'Europe">Ibis Val d'Europe</option>
          <option value="Relais SPA">Relais SPA</option>
          <option value="Resid'home">Resid'home</option>
          <option value="Hotel Moxy Val d'europe">Hotel Moxy Val d'europe</option>
          <option value="Hotel Dali">Hotel Dali</option>
          <option value="Séjours Affaires Apparthotel">Séjours Affaires Apparthotel</option>
          <option value="Campanile BussySt Georges">Campanile BussySt Georges</option>
          <option value="Best western Bussy St Georges">Best western Bussy St Georges</option>
          <option value="Premiere Classe Bussy St Georges">Premiere Classe Bussy St Georges</option>
          <option value="Villa Bella Bussy St Georges">Villa Bella Bussy St Georges</option>
          <option value="Golden Tulip Bussy St Georges">Golden Tulip Bussy St Georges</option>
          <option value="Citea Bussy St Georges">Citea Bussy St Georges</option>
          <option value="B&B Bussy St Georges">B&B Bussy St Georges</option>
          <option value="Paxton Ferrière">Paxton Ferrière</option>
          <option value="Campanile Torcy">Campanile Torcy</option>
          <option value="Chessy Gare">Chessy Gare</option>
          <option value="Gare Marne la Valée">Gare Marne la Valée</option>
          <option value="Serris Gare">Serris Gare</option>
          <option value="Val d'europe shopping center">Val d'europe shopping center</option>
          <option value="Vallée Village">Vallée Village</option>
          <option value="Others">Others</option>
        </select>
      </div>

      <label>Destination</label>
      <select name="destination" id="destination_location" placeholder="Select Destination" data-silent-initial-value-set="true">
        <option value="">Select Destination</option>
        <?php 
        // Destination options are populated dynamically via JS 
        // based on the $destinationMappings logic in booking.js
        ?>
      </select>


      <div id="destination_train_station_wrapper" style="display:none">
        <label>Select Train Station</label>
        <select name="destination_train_station" id="destination_train_station" placeholder="Select Train Station">
          <option value="">Select Train Station</option>
          <option value="Gare du Nord">Gare du Nord</option>
          <option value="Gare de Lyon">Gare de Lyon</option>
          <option value="Gare de L'Est">Gare de L'Est</option>
          <option value="Gare st Lazarre">Gare st Lazarre</option>
          <option value="Gare de Bercy">Gare de Bercy</option>
          <option value="Gare Montpernasse">Gare Montpernasse</option>
        </select>
      </div>

      <div id="destination_disney_hotel_wrapper" style="display:none">
        <label>Select Disney Hotel</label>
        <select name="destination_disney_hotel" id="destination_disney_hotel" placeholder="Select Disney Hotel">
          <option value="">Select Disney Hotel</option>
          <option value="Disneyland paris (Park)">Disneyland paris (Park)</option>
          <option value="Disneyland hotel">Disneyland hotel</option>
          <option value="Disney's hotel Cheyenne">Disney's hotel Cheyenne</option>
          <option value="Disney's hotel Santa Fe">Disney's hotel Santa Fe</option>
          <option value="Disney's hotel Newport Bay Club">Disney's hotel Newport Bay Club</option>
          <option value="Disney's hotel Sequoia Lodge">Disney's hotel Sequoia Lodge</option>
          <option value="Disney's hotel Marvel Newyork">Disney's hotel Marvel Newyork</option>
          <option value="Hotel Explorers">Hotel Explorers</option>
          <option value="Dream castle">Dream castle</option>
          <option value="Grand magic hotel">Grand magic hotel</option>
          <option value="Hotel B&B Val de France Disney">Hotel B&B Val de France Disney</option>
          <option value="Campanile Val de FranceDisney">Campanile Val de FranceDisney</option>
          <option value="Village Nature">Village Nature</option>
          <option value="Radisson blu">Radisson blu</option>
          <option value="Marriott's Village">Marriott's Village</option>
          <option value="Stay city Marne la Valée">Stay city Marne la Valée</option>
          <option value="Adagio Val d'Europe 42 cours du Danube, Serris">Adagio Val d'Europe 42 cours du Danube, Serris</option>
          <option value="Adagio 18 cours de I Elbe Disneyland Paris, Serris">Adagio 18 cours de I Elbe Disneyland Paris, Serris</option>
          <option value="Elysée Val d'europe">Elysée Val d'europe</option>
          <option value="Ibis Val d'Europe">Ibis Val d'Europe</option>
          <option value="Relais SPA">Relais SPA</option>
          <option value="Resid'home">Resid'home</option>
          <option value="Hotel Moxy Val d'europe">Hotel Moxy Val d'europe</option>
          <option value="Hotel Dali">Hotel Dali</option>
          <option value="Séjours Affaires Apparthotel">Séjours Affaires Apparthotel</option>
          <option value="Campanile BussySt Georges">Campanile BussySt Georges</option>
          <option value="Best western Bussy St Georges">Best western Bussy St Georges</option>
          <option value="Premiere Classe Bussy St Georges">Premiere Classe Bussy St Georges</option>
          <option value="Villa Bella Bussy St Georges">Villa Bella Bussy St Georges</option>
          <option value="Golden Tulip Bussy St Georges">Golden Tulip Bussy St Georges</option>
          <option value="Citea Bussy St Georges">Citea Bussy St Georges</option>
          <option value="B&B Bussy St Georges">B&B Bussy St Georges</option>
          <option value="Paxton Ferrière">Paxton Ferrière</option>
          <option value="Campanile Torcy">Campanile Torcy</option>
          <option value="Chessy Gare">Chessy Gare</option>
          <option value="Gare Marne la Valée">Gare Marne la Valée</option>
          <option value="Serris Gare">Serris Gare</option>
          <option value="Val d'europe shopping center">Val d'europe shopping center</option>
          <option value="Vallée Village">Vallée Village</option>
          <option value="Others">Others</option>
        </select>
      </div>

      <div id="pickup_address_wrapper" style="display:none">
        <label>Pickup Address</label>
        <input type="text" name="pickup_address" id="pickup_address" placeholder="Enter pickup address">
      </div>

      <div id="destination_address_wrapper" style="display:none">
        <label>Destination Address</label>
        <input type="text" name="destination_address" id="destination_address" placeholder="Enter destination address">
      </div>

      <label>Passengers</label>
      <select name="passengers" id="passengers_count">
        <?php for($i=1;$i<=21;$i++): ?>
          <option><?= $i ?></option>
        <?php endfor; ?>
      </select>

      <div id="vehicle_selection_section">
        <h3>Choose Vehicle</h3>
        <div class="jb-vehicle">
          <label id="vehicle_car_wrapper">
            <input type="radio" name="vehicle" value="car" checked>
            <img src="<?= plugin_dir_url(__FILE__) ?>assets/images/ecocar.webp" alt="Car">
            <span>Car <span class="car-price">€160</span></span>
          </label>
          <label id="vehicle_economy_van_wrapper" style="display:none;">
            <input type="radio" name="vehicle" value="economy_van">
            <img src="<?= plugin_dir_url(__FILE__) ?>assets/images/echovan.webp" alt="Economy Van">
            <span>Economy Van <span class="economy-van-price">€160</span></span>
          </label>
          <label id="vehicle_business_van_wrapper">
            <input type="radio" name="vehicle" value="van">
            <img src="<?= plugin_dir_url(__FILE__) ?>assets/images/bizvan.jpg" alt="Business Van">
            <span>Business Van <span class="van-price">€170</span></span>
          </label>
        </div>
      </div>

      <div id="large_group_message" style="display:none; padding: 20px; background: #fffaf5; border: 1px solid #9c1305; border-radius: 12px; margin-bottom: 24px; color: #9c1305; font-weight: 600; line-height: 1.6;">
        For groups of 9 passengers, we will arrange appropriate luxury vehicles to accommodate your group comfortably. Our team will contact you to discuss the specific arrangements.
      </div>

      <button type="button" class="jb-next">Next →</button>
    </div>

    <!-- STEP 2 -->
    <div class="jb-step">
      <h3>Journey Details</h3>

      <div class="jb-input-row">
        <div class="col">
          <label>Pickup Date</label>
          <input type="date" name="pickup_date" required style="height: 52px;">
        </div>
        <div class="col">
          <label>Pickup Time</label>
          <input type="time" name="pickup_time" required>
        </div>
      </div>

      <label>Flight / Train Number</label>
      <input type="text" name="flight_number" placeholder="e.g. EK073 or Eurostar 9010" required>

      <div id="return_journey_details" style="display:none; margin-top: 24px; border-top: 2px solid #f1f5f9; padding-top: 24px;">
        <h3 style="margin-bottom: 24px;">Return Journey Details</h3>
        <div class="jb-input-row">
          <div class="col">
            <label>Return Date</label>
            <input type="date" name="return_date" style="height: 52px;">
          </div>
          <div class="col">
            <label>Return Time</label>
            <input type="time" name="return_time">
          </div>
        </div>
        <label>Return Flight / Train Number</label>
        <input type="text" name="return_flight_number" placeholder="e.g. EK073 or Eurostar 9010">
      </div>

      <div class="jb-checkbox-group">
        <div class="jb-checkbox">
          <label style="width: 100%;">
            <input type="checkbox" name="has_luggage" id="need_luggage" value="Yes"> 
            <span style="font-size: 18px;">🧳</span> Luggage Space
          </label>
        </div>

        <div class="jb-checkbox">
          <label style="width: 100%;">
            <input type="checkbox" name="has_baby" id="need_baby" value="Yes"> 
            <span style="font-size: 18px;">👶</span> Baby Seat
          </label>
        </div>
      </div>

      <div id="luggage_wrapper" style="display:none; margin-bottom: 24px;">
        <div class="jb-input-row">
          <div class="col">
            <label>Luggage Count</label>
            <select name="luggage_count" id="luggage_count_select" disabled>
              <?php for($i=1;$i<=10;$i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col">
            <label>Select Luggage Size</label>
            <select name="luggage_size" id="luggage_select" disabled>
              <option>Small</option>
              <option>Medium</option>
              <option>Large</option>
              <option>Extra Large</option>
            </select>
          </div>
        </div>
      </div>

      <div id="baby_wrapper" style="display:none; margin-bottom: 24px;">
        <div class="jb-input-row">
          <div class="col">
            <label>Baby Seat (1 to 2 years)</label>
            <select name="baby_seat" id="baby_seat_select" placeholder="Select Baby Seat" disabled>
              <option value="1">1 seat</option>
              <option value="2">2 seats</option>
              <option value="3">3 seats</option>
            </select>
          </div>
          <div class="col">
            <label>Booster Seat (3 to 8 years)</label>
            <select name="booster_seat" id="booster_seat_select" placeholder="Select Booster Seat" disabled>
              <option value="1">1 booster seat</option>
              <option value="2">2 booster seats</option>
              <option value="3">3 booster seats</option>
            </select>
          </div>
        </div>
      </div>

      <label>Payment Option</label>
      <select name="payment" id="payment_method" placeholder="Select Payment Option">
        <option value="">Select Payment Option</option>
        <option value="Pay by cash to the driver">Pay by cash to the driver</option>
        <option value="Pay by credit card to the driver">Pay by credit card to the driver  (+5 €)</option>
      </select>

      <label>Additional Notes</label>
      <textarea name="notes" placeholder="Any special requests?"></textarea>

      <button type="button" class="jb-back">← Back</button>
      <button type="button" class="jb-next">Next →</button>
    </div>

    <!-- STEP 3 -->
    <div class="jb-step">
      <h3>Personal Details</h3>

      <div class="jb-input-row">
        <div class="col">
          <label>First Name</label>
          <input type="text" name="first_name" placeholder="Enter first name" required>
        </div>
        <div class="col">
          <label>Last Name</label>
          <input type="text" name="last_name" placeholder="Enter last name" required>
        </div>
      </div>

      <div class="jb-input-row">
        <div class="col">
          <label>Email Address</label>
          <input type="email" name="email" id="email" placeholder="email@example.com" required>
        </div>
        <div class="col">
          <label>Confirm Email</label>
          <input type="email" name="confirm_email" id="confirm_email" placeholder="Confirm your email" required>
          <span id="email_mismatch_error" style="color: #9c1305; font-size: 13px; font-weight: 600; display: none; margin-top: 5px;">Emails do not match.</span>
        </div>
      </div>

      <label>Phone Number</label>
      <input type="tel" name="phone" id="phone" required>

      <div class="jb-agreement">  
        <label>
          <input type="checkbox" required>
          <span>I agree to the <a href="https://disneyholidaytransfer.com/terms-and-conditions/">Terms & Conditions</a> and <a href="https://disneyholidaytransfer.com/privacy-policy/">Privacy Policy</a>.</span>
        </label>
      </div>

      <div style="margin-top: 32px; border-top: 2px solid #f1f5f9; padding-top: 24px;">
        <button type="button" class="jb-back">← Back</button>
        <button type="submit" class="jb-submit" id="submit_button">Complete Booking</button>
      </div>
    </div>

  </form>

  <!-- Success  -->
  <div id="bookingSuccess" style="display:none;" class="jb-success">
    <div class="jb-success-card">
      <div class="jb-check">✓</div>
      <h2>Booking Successful!</h2>
      <p>
        Your booking reference is:
        <strong id="bookingRef"></strong>
      </p>
      <p>
        Total Amount:
        <strong id="bookingTotalAmount"></strong>
      </p>
    </div>
  </div>
</div>
