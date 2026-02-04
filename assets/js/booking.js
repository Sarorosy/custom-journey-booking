console.log('booking.js loaded');
jQuery(function ($) {
  console.log('jQuery Ready - booking.js');
  console.log('JB_Ajax defined?', typeof JB_Ajax !== 'undefined');
  if (typeof JB_Ajax !== 'undefined') {
    console.log('Mapping keys:', Object.keys(JB_Ajax.destinationMappings));
  } else {
    console.error('JB_Ajax is NOT defined. Localization might have failed.');
  }

  // Initialize Virtual Select for all dropdowns
  if (typeof VirtualSelect !== 'undefined') {
    console.log('Initializing VirtualSelect');
    VirtualSelect.init({
      ele: 'select',
      maxWidth: '100%',
    });
  } else {
    console.error('VirtualSelect library is NOT loaded. Check your enqueuing or CDN links.');
  }

  let step = 0;
  const steps = $('.jb-step');
  const indicators = $('.jb-steps > span');

  // Initialize intl-tel-input
  const phoneInput = document.querySelector('input[name="phone"]');
  let iti;
  if (phoneInput && typeof window.intlTelInput !== 'undefined') {
    iti = window.intlTelInput(phoneInput, {
      initialCountry: "fr",
      separateDialCode: true,
      nationalMode: true,
      autoPlaceholder: "aggressive",
      utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@24.4.0/build/js/utils.js",
    });
  }

  function showStep(i) {
    steps.removeClass('active').eq(i).addClass('active');
    indicators.removeClass('active').each(function (index) {
      const $this = $(this);
      if (index === i) {
        $this.addClass('active').removeClass('completed');
        $this.find('.jb-step-number').text(index + 1);
      } else if (index < i) {
        $this.addClass('completed');
        $this.find('.jb-step-number').html('✓');
      } else {
        $this.removeClass('completed');
        $this.find('.jb-step-number').text(index + 1);
      }
    });
  }

  $('.jb-next').on('click', function () {
    if (validateStep(step)) {
      if (step < steps.length - 1) step++;
      showStep(step);
    }
  });

  function validateStep(s) {
    let isValid = true;
    const currentStep = steps.eq(s);

    if (s === 0) {
      // Step 1 Validation
      // Helper to check if value is empty (handles string or array)
      const isEmpty = (val) => {
        if (!val) return true;
        if (Array.isArray(val) && val.length === 0) return true;
        if (typeof val === 'string' && val.trim() === '') return true;
        return false;
      };

      const pickup = $('#pickup_location').val();
      const destination = $('#destination_location').val();

      if (isEmpty(pickup)) {
        alert("Please select a pickup location.");
        isValid = false;
      } else if ($('#pickup_hotel_wrapper').is(':visible') && isEmpty($('#pickup_hotel').val())) {
        alert("Please select a pickup hotel.");
        isValid = false;
      } else if ($('#pickup_train_station_wrapper').is(':visible') && isEmpty($('#pickup_train_station').val())) {
        alert("Please select a pickup train station.");
        isValid = false;
      } else if ($('#pickup_disney_hotel_wrapper').is(':visible') && isEmpty($('#pickup_disney_hotel').val())) {
        alert("Please select a pickup Disney hotel.");
        isValid = false;
      } else if ($('#destination_disney_hotel_wrapper').is(':visible') && isEmpty($('#destination_disney_hotel').val())) {
        alert("Please select a destination Disney hotel.");
        isValid = false;
      } else if (isEmpty(destination)) {
        alert("Please select a destination.");
        isValid = false;
      } else if ($('#destination_hotel_wrapper').is(':visible') && isEmpty($('#destination_hotel').val())) {
        alert("Please select a destination hotel.");
        isValid = false;
      } else if ($('#destination_train_station_wrapper').is(':visible') && isEmpty($('#destination_train_station').val())) {
        alert("Please select a destination train station.");
        isValid = false;
      } else if ($('#pickup_address_wrapper').is(':visible') && isEmpty($('#pickup_address').val())) {
        alert("Please enter the pickup address.");
        isValid = false;
      } else if ($('#destination_address_wrapper').is(':visible') && isEmpty($('#destination_address').val())) {
        alert("Please enter the destination address.");
        isValid = false;
      }
    } else if (s === 1) {
      // Step 2 Validation
      const date = $('input[name="pickup_date"]').val();
      const time = $('input[name="pickup_time"]').val();
      const flight = $('input[name="flight_number"]').val();
      const payment = $('#payment_method').val();

      if (!date) {
        alert("Please select a pickup date.");
        isValid = false;
      } else if (!time) {
        alert("Please select a pickup time.");
        isValid = false;
      } else if (!flight || flight.trim() === '') {
        alert("Please enter a Flight or Train Number.");
        isValid = false;
      } else if ($('#return_journey_details').is(':visible')) {
        const rDate = $('input[name="return_date"]').val();
        const rTime = $('input[name="return_time"]').val();
        const rFlight = $('input[name="return_flight_number"]').val();

        if (!rDate) {
          alert("Please select a return date.");
          isValid = false;
        } else if (!rTime) {
          alert("Please select a return time.");
          isValid = false;
        } else if (!rFlight || rFlight.trim() === '') {
          alert("Please enter a Return Flight or Train Number.");
          isValid = false;
        }
      }

      if (isValid && (!payment || payment.trim() === '')) {
        alert("Please select a payment option.");
        isValid = false;
      }
    } else if (s === 2) {
      // Step 3 Validation (Personal Details)
      const email = $('#email').val();
      const confirmEmail = $('#confirm_email').val();
      const errorMsg = $('#email_mismatch_error');

      if (email !== confirmEmail) {
        errorMsg.show();
        isValid = false;
      } else {
        errorMsg.hide();
      }
    }

    return isValid;
  }

  $('.jb-back').on('click', function () {
    if (step > 0) step--;
    showStep(step);
  });

  $('#need_luggage').on('change', function () {
    const wrapper = $('#luggage_wrapper');
    wrapper.toggle(this.checked);

    // Enable/Disable inputs inside to prevent them from being serialized if not needed
    wrapper.find('select, input, textarea').prop('disabled', !this.checked);

    if (this.checked) {
      ['#luggage_select', '#luggage_count_select'].forEach(id => {
        const select = document.querySelector(id);
        if (select && select.refresh) select.refresh();
        if (select && select.enable) select.enable();
      });
    } else {
      ['#luggage_select', '#luggage_count_select'].forEach(id => {
        const select = document.querySelector(id);
        if (select && select.disable) select.disable();
      });
    }
  });

  $('#need_baby').on('change', function () {
    const wrapper = $('#baby_wrapper');
    wrapper.toggle(this.checked);

    // Enable/Disable inputs inside
    wrapper.find('select, input, textarea').prop('disabled', !this.checked);

    if (this.checked) {
      ['#baby_seat_select', '#booster_seat_select'].forEach(id => {
        const select = document.querySelector(id);
        if (select && select.refresh) select.refresh();
        if (select && select.enable) select.enable();
      });
    } else {
      ['#baby_seat_select', '#booster_seat_select'].forEach(id => {
        const select = document.querySelector(id);
        if (select && select.disable) select.disable();
      });
    }
  });

  // Expanded pricingMatrix to support 1-21 passengers
  // Structure: "Route": { passengers: [CarPrice, VanPrice] }
  // Note: For 9-21 passengers, typically only Van/Minibus is available, 
  // but we provide slots for both to match your UI requirements.
  const pricingMatrix = {
    "Charles de Gaulle Airport (CDG)": {
      "Beauvais Airport (BVA)": {
        1: [160, 170], 2: [160, 170], 3: [160, 170], 4: [160, 170],
        5: [170, 180], 6: [170, 180], 7: [170, 180], 8: [170, 180],
        9: [320, 340], 10: [320, 340], 11: [320, 340], 12: [320, 340],
        13: [320, 340], 14: [320, 340], 15: [320, 340], 16: [320, 340],
        17: [320, 340], 18: [320, 340], 19: [320, 340], 20: [320, 340], 21: [320, 340]
      },
      "Disneyland and Hotels": {
        1: [70, 85], 2: [70, 85], 3: [70, 85], 4: [80, 90],
        5: [85, 95], 6: [90, 100], 7: [90, 100], 8: [95, 105],
        9: [160, 160], 10: [170, 170], 11: [170, 170], 12: [180, 180],
        13: [180, 180], 14: [180, 180], 15: [190, 190], 16: [190, 190],
        17: [270, 270], 18: [270, 270], 19: [270, 270], 20: [270, 270], 21: [270, 270]
      },
      "Paris (Hotels, Apartments, Historic monuments)": {
        1: [80, 100], 2: [80, 100], 3: [80, 100], 4: [100, 110],
        5: [100, 110], 6: [100, 120], 7: [100, 120], 8: [110, 125],
        9: [195, 195], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [210, 210], 14: [220, 220], 15: [240, 240], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Paris Train Stations": {
        1: [80, 100], 2: [80, 100], 3: [80, 100], 4: [100, 110],
        5: [100, 110], 6: [100, 120], 7: [100, 120], 8: [110, 125],
        9: [195, 195], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [210, 210], 14: [220, 220], 15: [240, 240], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Versailles": {
        1: [115, 125], 2: [115, 125], 3: [115, 125], 4: [115, 125],
        5: [125, 135], 6: [125, 135], 7: [125, 135], 8: [125, 135],
        9: [230, 250], 10: [230, 250], 11: [230, 250], 12: [230, 250],
        13: [230, 250], 14: [230, 250], 15: [230, 250], 16: [230, 250],
        17: [230, 250], 18: [230, 250], 19: [230, 250], 20: [230, 250], 21: [230, 250]
      },
      "Orly Airport (ORY)": {
        1: [100, 110], 2: [100, 110], 3: [100, 110], 4: [100, 110],
        5: [115, 120], 6: [115, 120], 7: [115, 120], 8: [115, 120],
        9: [180, 180], 10: [180, 180], 11: [180, 180], 12: [180, 180],
        13: [220, 220], 14: [220, 220], 15: [220, 220], 16: [220, 220],
        17: [220, 220], 18: [220, 220], 19: [220, 220], 20: [220, 220], 21: [220, 220]
      }
    },
    "Orly Airport (ORY)": {
      "Charles de Gaulle Airport (CDG)": {
        1: [100, 110], 2: [100, 110], 3: [100, 110], 4: [100, 110],
        5: [110, 120], 6: [110, 120], 7: [110, 120], 8: [110, 120],
        9: [200, 220], 10: [200, 220], 11: [200, 220], 12: [200, 220],
        13: [200, 220], 14: [200, 220], 15: [200, 220], 16: [200, 220],
        17: [200, 220], 18: [200, 220], 19: [200, 220], 20: [200, 220], 21: [200, 220]
      },
      "Disneyland and Hotels": {
        1: [90, 100], 2: [90, 100], 3: [90, 100], 4: [100, 110],
        5: [105, 115], 6: [110, 120], 7: [110, 120], 8: [115, 125],
        9: [200, 200], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [210, 210], 14: [210, 210], 15: [220, 220], 16: [220, 220],
        17: [300, 300], 18: [300, 300], 19: [300, 300], 20: [300, 300],
        21: [300, 300]
      },
      "Paris (Hotels, Apartments, Historic monuments)": {
        1: [80, 90], 2: [80, 90], 3: [80, 90], 4: [90, 100],
        5: [95, 105], 6: [95, 105], 7: [95, 105], 8: [100, 110],
        9: [170, 170], 10: [180, 180], 11: [185, 185], 12: [190, 190],
        13: [195, 195], 14: [200, 200], 15: [210, 210], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Paris Train Stations": {
        1: [80, 90], 2: [80, 90], 3: [80, 90], 4: [90, 100],
        5: [95, 105], 6: [95, 105], 7: [95, 105], 8: [100, 110],
        9: [170, 170], 10: [180, 180], 11: [185, 185], 12: [190, 190],
        13: [195, 195], 14: [200, 200], 15: [210, 210], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Versailles": {
        1: [100, 110], 2: [100, 110], 3: [100, 110], 4: [100, 110],
        5: [110, 120], 6: [110, 120], 7: [110, 120], 8: [110, 120],
        9: [200, 220], 10: [200, 220], 11: [200, 220], 12: [200, 220],
        13: [200, 220], 14: [200, 220], 15: [200, 220], 16: [200, 220],
        17: [200, 220], 18: [200, 220], 19: [200, 220], 20: [200, 220], 21: [200, 220]
      },
      "Beauvais Airport (BVA)": {
        1: [170, 180], 2: [170, 180], 3: [170, 180], 4: [170, 180],
        5: [190, 200], 6: [190, 200], 7: [190, 200], 8: [190, 200],
        9: [280, 280], 10: [280, 280], 11: [280, 280], 12: [280, 280],
        13: [330, 330], 14: [330, 330], 15: [330, 330], 16: [330, 330],
        17: [330, 330], 18: [330, 330], 19: [330, 330], 20: [330, 330], 21: [330, 330]
      }
    },
    "Beauvais Airport (BVA)": {
      "Charles de Gaulle Airport (CDG)": {
        1: [160, 170], 2: [160, 170], 3: [160, 170], 4: [160, 170],
        5: [170, 180], 6: [170, 180], 7: [170, 180], 8: [170, 180],
        9: [320, 340], 10: [320, 340], 11: [320, 340], 12: [320, 340],
        13: [320, 340], 14: [320, 340], 15: [320, 340], 16: [320, 340],
        17: [320, 340], 18: [320, 340], 19: [320, 340], 20: [320, 340], 21: [320, 340]
      },
      "Orly Airport (ORY)": {
        1: [170, 180], 2: [170, 180], 3: [170, 180], 4: [170, 180],
        5: [180, 190], 6: [180, 190], 7: [180, 190], 8: [180, 190],
        9: [340, 360], 10: [340, 360], 11: [340, 360], 12: [340, 360],
        13: [340, 360], 14: [340, 360], 15: [340, 360], 16: [340, 360],
        17: [340, 360], 18: [340, 360], 19: [340, 360], 20: [340, 360], 21: [340, 360]
      },
      "Disneyland and Hotels": {
        1: [160, 180], 2: [160, 180], 3: [160, 180], 4: [170, 180],
        5: [170, 180], 6: [170, 180], 7: [170, 180], 8: [180, 190],
        9: [320, 320], 10: [320, 320], 11: [320, 320], 12: [340, 340],
        13: [340, 340], 14: [340, 340], 15: [360, 360], 16: [360, 360],
        17: [510, 510], 18: [510, 510], 19: [540, 540], 20: [540, 540],
        21: [540, 540]
      },
      "Paris (Hotels, Apartments, Historic monuments)": {
        1: [160, 180], 2: [160, 180], 3: [160, 180], 4: [170, 180],
        5: [170, 180], 6: [170, 180], 7: [170, 180], 8: [180, 190],
        9: [320, 320], 10: [320, 320], 11: [320, 320], 12: [340, 340],
        13: [340, 340], 14: [340, 340], 15: [360, 360], 16: [360, 360],
        17: [510, 510], 18: [510, 510], 19: [540, 540], 20: [540, 540],
        21: [540, 540]
      },
      "Paris Train Stations": {
        1: [160, 180], 2: [160, 180], 3: [160, 180], 4: [170, 180],
        5: [170, 180], 6: [170, 180], 7: [170, 180], 8: [180, 190],
        9: [320, 320], 10: [320, 320], 11: [320, 320], 12: [340, 340],
        13: [340, 340], 14: [340, 340], 15: [360, 360], 16: [360, 360],
        17: [510, 510], 18: [510, 510], 19: [540, 540], 20: [540, 540],
        21: [540, 540]
      },
      "Versailles": {
        1: [165, 175], 2: [165, 175], 3: [165, 175], 4: [165, 175],
        5: [175, 185], 6: [175, 185], 7: [175, 185], 8: [175, 185],
        9: [330, 350], 10: [330, 350], 11: [330, 350], 12: [330, 350],
        13: [330, 350], 14: [330, 350], 15: [330, 350], 16: [330, 350],
        17: [330, 350], 18: [330, 350], 19: [330, 350], 20: [330, 350], 21: [330, 350]
      }
    },
    "Disneyland and Hotels": {
      "Charles de Gaulle Airport (CDG)": {
        1: [70, 85], 2: [70, 85], 3: [70, 85], 4: [80, 90],
        5: [85, 95], 6: [90, 100], 7: [90, 100], 8: [95, 105],
        9: [160, 160], 10: [170, 170], 11: [170, 170], 12: [180, 180],
        13: [180, 180], 14: [180, 180], 15: [190, 190], 16: [190, 190],
        17: [270, 270], 18: [270, 270], 19: [270, 270], 20: [270, 270], 21: [270, 270]
      },
      "Orly Airport (ORY)": {
        1: [90, 100], 2: [90, 100], 3: [90, 100], 4: [100, 110],
        5: [105, 115], 6: [110, 120], 7: [110, 120], 8: [115, 125],
        9: [200, 200], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [210, 210], 14: [210, 210], 15: [220, 220], 16: [220, 220],
        17: [300, 300], 18: [300, 300], 19: [300, 300], 20: [300, 300],
        21: [300, 300]
      },
      "Beauvais Airport (BVA)": {
        1: [160, 180], 2: [160, 180], 3: [160, 180], 4: [170, 180],
        5: [170, 180], 6: [170, 180], 7: [170, 180], 8: [180, 190],
        9: [320, 320], 10: [320, 320], 11: [320, 320], 12: [340, 340],
        13: [340, 340], 14: [340, 340], 15: [360, 360], 16: [360, 360],
        17: [510, 510], 18: [510, 510], 19: [540, 540], 20: [540, 540],
        21: [540, 540]
      },
      "Paris (Hotels, Apartments, Historic monuments)": {
        1: [90, 110], 2: [90, 110], 3: [90, 110], 4: [100, 110],
        5: [105, 115], 6: [110, 120], 7: [115, 125], 8: [120, 130],
        9: [200, 200], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [220, 220], 14: [220, 220], 15: [230, 230], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Paris Train Stations": {
        1: [90, 110], 2: [90, 110], 3: [90, 110], 4: [100, 110],
        5: [105, 115], 6: [110, 120], 7: [115, 125], 8: [120, 130],
        9: [200, 200], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [220, 220], 14: [220, 220], 15: [230, 230], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Versailles": {
        1: [120, 130], 2: [120, 130], 3: [120, 130], 4: [120, 130],
        5: [130, 140], 6: [130, 140], 7: [130, 140], 8: [130, 140],
        9: [240, 260], 10: [240, 260], 11: [240, 260], 12: [240, 260],
        13: [240, 260], 14: [240, 260], 15: [240, 260], 16: [240, 260],
        17: [240, 260], 18: [240, 260], 19: [240, 260], 20: [240, 260], 21: [240, 260]
      }
    },
    "Paris (Hotels, Apartments, Historic monuments)": {
      "Charles de Gaulle Airport (CDG)": {
        1: [80, 100], 2: [80, 100], 3: [80, 100], 4: [100, 110],
        5: [100, 110], 6: [100, 120], 7: [100, 120], 8: [110, 125],
        9: [195, 195], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [210, 210], 14: [220, 220], 15: [240, 240], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Disneyland and Hotels": {
        1: [90, 110], 2: [90, 110], 3: [90, 110], 4: [100, 110],
        5: [105, 115], 6: [110, 120], 7: [115, 125], 8: [120, 130],
        9: [200, 200], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [220, 220], 14: [220, 220], 15: [230, 230], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Orly Airport (ORY)": {
        1: [80, 90], 2: [80, 90], 3: [80, 90], 4: [90, 100],
        5: [95, 105], 6: [95, 105], 7: [95, 105], 8: [100, 110],
        9: [170, 170], 10: [180, 180], 11: [185, 185], 12: [190, 190],
        13: [195, 195], 14: [200, 200], 15: [210, 210], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Beauvais Airport (BVA)": {
        1: [160, 180], 2: [160, 180], 3: [160, 180], 4: [170, 180],
        5: [170, 180], 6: [170, 180], 7: [170, 180], 8: [180, 190],
        9: [320, 320], 10: [320, 320], 11: [320, 320], 12: [340, 340],
        13: [340, 340], 14: [340, 340], 15: [360, 360], 16: [360, 360],
        17: [510, 510], 18: [510, 510], 19: [540, 540], 20: [540, 540],
        21: [540, 540]
      },
      "Paris (Hotels, Apartments, Historic monuments)": {
        1: [50, 70], 2: [50, 70], 3: [50, 70], 4: [70, 80],
        5: [75, 80], 6: [80, 85], 7: [80, 85], 8: [85, 90],
        9: [150, 160], 10: [150, 160], 11: [160, 170], 12: [160, 170],
        13: [160, 170], 14: [160, 170], 15: [170, 180], 16: [180, 190],
        17: [240, 250], 18: [240, 250], 19: [240, 250], 20: [250, 260],
        21: [250, 260]
      },
      "Paris Train Stations": {
        1: [70, 75], 2: [70, 75], 3: [70, 75], 4: [70, 75],
        5: [80, 85], 6: [80, 85], 7: [80, 85], 8: [80, 85],
        9: [140, 150], 10: [140, 150], 11: [140, 150], 12: [140, 150],
        13: [140, 150], 14: [140, 150], 15: [140, 150], 16: [140, 150],
        17: [140, 150], 18: [140, 150], 19: [140, 150], 20: [140, 150], 21: [140, 150]
      },
      "Versailles": {
        1: [80, 90], 2: [80, 90], 3: [80, 90], 4: [80, 90],
        5: [90, 100], 6: [90, 100], 7: [90, 100], 8: [90, 100],
        9: [160, 180], 10: [160, 180], 11: [160, 180], 12: [160, 180],
        13: [160, 180], 14: [160, 180], 15: [160, 180], 16: [160, 180],
        17: [160, 180], 18: [160, 180], 19: [160, 180], 20: [160, 180], 21: [160, 180]
      }
    },
    "Paris Train Stations": {
      "Charles de Gaulle Airport (CDG)": {
        1: [80, 100], 2: [80, 100], 3: [80, 100], 4: [100, 110],
        5: [100, 110], 6: [100, 120], 7: [100, 120], 8: [110, 125],
        9: [195, 195], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [210, 210], 14: [220, 220], 15: [240, 240], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Orly Airport (ORY)": {
        1: [80, 90], 2: [80, 90], 3: [80, 90], 4: [90, 100],
        5: [95, 105], 6: [95, 105], 7: [95, 105], 8: [100, 110],
        9: [170, 170], 10: [180, 180], 11: [185, 185], 12: [190, 190],
        13: [195, 195], 14: [200, 200], 15: [210, 210], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Beauvais Airport (BVA)": {
        1: [160, 180], 2: [160, 180], 3: [160, 180], 4: [170, 180],
        5: [170, 180], 6: [170, 180], 7: [170, 180], 8: [180, 190],
        9: [320, 320], 10: [320, 320], 11: [320, 320], 12: [340, 340],
        13: [340, 340], 14: [340, 340], 15: [360, 360], 16: [360, 360],
        17: [510, 510], 18: [510, 510], 19: [540, 540], 20: [540, 540],
        21: [540, 540]
      },
      "Disneyland and Hotels": {
        1: [90, 110], 2: [90, 110], 3: [90, 110], 4: [100, 110],
        5: [105, 115], 6: [110, 120], 7: [115, 125], 8: [120, 130],
        9: [200, 200], 10: [200, 200], 11: [200, 200], 12: [210, 210],
        13: [220, 220], 14: [220, 220], 15: [230, 230], 16: [240, 240],
        17: [300, 300], 18: [300, 300], 19: [330, 330], 20: [330, 330],
        21: [330, 330]
      },
      "Paris (Hotels, Apartments, Historic monuments)": {
        1: [70, 75], 2: [70, 75], 3: [70, 75], 4: [70, 75],
        5: [80, 85], 6: [80, 85], 7: [80, 85], 8: [80, 85],
        9: [140, 150], 10: [140, 150], 11: [140, 150], 12: [140, 150],
        13: [140, 150], 14: [140, 150], 15: [140, 150], 16: [140, 150],
        17: [140, 150], 18: [140, 150], 19: [140, 150], 20: [140, 150], 21: [140, 150]
      },
      "Paris Train Stations": {
        1: [50, 70], 2: [50, 70], 3: [50, 70], 4: [70, 80],
        5: [75, 80], 6: [80, 85], 7: [80, 85], 8: [85, 90],
        9: [150, 160], 10: [150, 160], 11: [160, 170], 12: [160, 170],
        13: [160, 170], 14: [160, 170], 15: [170, 180], 16: [180, 190],
        17: [240, 250], 18: [240, 250], 19: [240, 250], 20: [250, 260],
        21: [250, 260]
      },
      "Versailles": {
        1: [80, 90], 2: [80, 90], 3: [80, 90], 4: [80, 90],
        5: [90, 100], 6: [90, 100], 7: [90, 100], 8: [90, 100],
        9: [160, 180], 10: [160, 180], 11: [160, 180], 12: [160, 180],
        13: [160, 180], 14: [160, 180], 15: [160, 180], 16: [160, 180],
        17: [160, 180], 18: [160, 180], 19: [160, 180], 20: [160, 180], 21: [160, 180]
      }
    },
    "Versailles": {
      "Charles de Gaulle Airport (CDG)": {
        1: [115, 125], 2: [115, 125], 3: [115, 125], 4: [115, 125],
        5: [125, 135], 6: [125, 135], 7: [125, 135], 8: [125, 135],
        9: [230, 250], 10: [230, 250], 11: [230, 250], 12: [230, 250],
        13: [230, 250], 14: [230, 250], 15: [230, 250], 16: [230, 250],
        17: [230, 250], 18: [230, 250], 19: [230, 250], 20: [230, 250], 21: [230, 250]
      },
      "Orly Airport (ORY)": {
        1: [100, 110], 2: [100, 110], 3: [100, 110], 4: [100, 110],
        5: [110, 120], 6: [110, 120], 7: [110, 120], 8: [110, 120],
        9: [200, 220], 10: [200, 220], 11: [200, 220], 12: [200, 220],
        13: [200, 220], 14: [200, 220], 15: [200, 220], 16: [200, 220],
        17: [200, 220], 18: [200, 220], 19: [200, 220], 20: [200, 220], 21: [200, 220]
      },
      "Beauvais Airport (BVA)": {
        1: [165, 175], 2: [165, 175], 3: [165, 175], 4: [165, 175],
        5: [175, 185], 6: [175, 185], 7: [175, 185], 8: [175, 185],
        9: [330, 350], 10: [330, 350], 11: [330, 350], 12: [330, 350],
        13: [330, 350], 14: [330, 350], 15: [330, 350], 16: [330, 350],
        17: [330, 350], 18: [330, 350], 19: [330, 350], 20: [330, 350], 21: [330, 350]
      },
      "Disneyland and Hotels": {
        1: [120, 130], 2: [120, 130], 3: [120, 130], 4: [120, 130],
        5: [130, 140], 6: [130, 140], 7: [130, 140], 8: [130, 140],
        9: [240, 260], 10: [240, 260], 11: [240, 260], 12: [240, 260],
        13: [240, 260], 14: [240, 260], 15: [240, 260], 16: [240, 260],
        17: [240, 260], 18: [240, 260], 19: [240, 260], 20: [240, 260], 21: [240, 260]
      },
      "Paris (Hotels, Apartments, Historic monuments)": {
        1: [80, 90], 2: [80, 90], 3: [80, 90], 4: [80, 90],
        5: [90, 100], 6: [90, 100], 7: [90, 100], 8: [90, 100],
        9: [160, 180], 10: [160, 180], 11: [160, 180], 12: [160, 180],
        13: [160, 180], 14: [160, 180], 15: [160, 180], 16: [160, 180],
        17: [160, 180], 18: [160, 180], 19: [160, 180], 20: [160, 180], 21: [160, 180]
      },
      "Paris Train Stations": {
        1: [80, 90], 2: [80, 90], 3: [80, 90], 4: [80, 90],
        5: [90, 100], 6: [90, 100], 7: [90, 100], 8: [90, 100],
        9: [160, 180], 10: [160, 180], 11: [160, 180], 12: [160, 180],
        13: [160, 180], 14: [160, 180], 15: [160, 180], 16: [160, 180],
        17: [160, 180], 18: [160, 180], 19: [160, 180], 20: [160, 180], 21: [160, 180]
      }
    },
  };

  function checkAddressInputSelection() {
    let pickup = $('#pickup_location').val();
    let destination = $('#destination_location').val();

    // Handle array values from VirtualSelect
    if (Array.isArray(pickup)) pickup = pickup[0];
    if (Array.isArray(destination)) destination = destination[0];

    const PARIS_LOC = "Paris (Hotels, Apartments, Historic monuments)";
    const PARIS_STATION = "Paris Train Stations";
    const DISNEY_LOC = "Disneyland and Hotels";

    console.log('Checking for Address selection:', { pickup, destination });

    const isParisHotelToStation = (pickup === PARIS_LOC && destination === PARIS_STATION);
    const isStationToParisHotel = (pickup === PARIS_STATION && destination === PARIS_LOC);

    // Show pickup address if Paris is selected, or if Disneyland is selected AND "Others" is picked in the Disney dropdown
    const isPickupDisneyOther = (pickup === DISNEY_LOC && $('#pickup_disney_hotel').val() === 'Others');
    if (pickup === PARIS_LOC || isPickupDisneyOther || isStationToParisHotel) {
      $('#pickup_address_wrapper').show();
    } else {
      $('#pickup_address_wrapper').hide();
      $('#pickup_address').val('');
    }

    // Show destination address if Paris is selected, or if Disneyland is selected AND "Others" is picked in the Disney dropdown
    const isDestDisneyOther = (destination === DISNEY_LOC && $('#destination_disney_hotel').val() === 'Others');
    if (destination === PARIS_LOC || isDestDisneyOther || isParisHotelToStation) {
      $('#destination_address_wrapper').show();
    } else {
      $('#destination_address_wrapper').hide();
      $('#destination_address').val('');
    }
  }

  function updatePrices() {
    let pickup = $('#pickup_location').val();
    let destination = $('#destination_location').val();
    const journeyType = $('input[name="journey_type"]:checked').val();
    const payment = $('#payment_method').val();
    const selectedVehicle = $('input[name="vehicle"]:checked').val();
    const passengers = parseInt($('#passengers_count').val()) || 1;

    // Handle array values from VirtualSelect
    if (Array.isArray(pickup)) pickup = pickup[0];
    if (Array.isArray(destination)) destination = destination[0];

    console.log('Updating prices for:', { pickup, destination, journeyType, payment, passengers });

    // Removed the hiding logic for 9+ passengers as per user request
    $('#vehicle_selection_section').show();
    $('#large_group_message').hide();

    // Vehicle Visibility Logic based on passengers
    if (passengers >= 5) {
      $('#vehicle_car_wrapper').hide();
      $('#vehicle_economy_van_wrapper').show();

      // If car was selected, switch to economy_van
      if ($('input[name="vehicle"]:checked').val() === 'car') {
        $('input[name="vehicle"][value="economy_van"]').prop('checked', true).trigger('change');
      }
    } else {
      $('#vehicle_car_wrapper').show();
      $('#vehicle_economy_van_wrapper').hide();

      // If economy_van was selected, switch back to car
      if ($('input[name="vehicle"]:checked').val() === 'economy_van') {
        $('input[name="vehicle"][value="car"]').prop('checked', true).trigger('change');
      }
    }

    let carPrice = 0;
    let vanPrice = 0;

    if (pickup && destination && pricingMatrix[pickup] && pricingMatrix[pickup][destination]) {
      const routeData = pricingMatrix[pickup][destination];
      const basePrices = routeData[passengers] || routeData[1]; // Fallback to 1 if not found
      carPrice = basePrices[0];
      vanPrice = basePrices[1];

      // Round trip: double the price
      if (journeyType === 'round') {
        carPrice *= 2;
        vanPrice *= 2;
      }

      // Card / UPI: add €5
      if (payment === 'Pay by credit card to the driver') {
        carPrice += 5;
        vanPrice += 5;
      }
    }

    if (carPrice > 0) {
      $('.car-price').text('€' + carPrice);
      $('.economy-van-price').text('€' + carPrice);
      $('.van-price').text('€' + vanPrice);

      // Set hidden amount field
      const finalAmount = (selectedVehicle === 'van') ? vanPrice : carPrice;
      $('#booking_amount').val(finalAmount);
    } else {
      $('.car-price').text('€--');
      $('.economy-van-price').text('€--');
      $('.van-price').text('€--');
      $('#booking_amount').val('');
    }
  }


  const passengersEle = document.querySelector('#passengers_count');
  if (passengersEle) {
    passengersEle.addEventListener('change', updatePrices);
  }

  // Using direct event listener which is more reliable with VirtualSelect
  const pickupEle = document.querySelector('#pickup_location');
  if (pickupEle) {
    pickupEle.addEventListener('change', function () {
      const pickup = this.value;
      console.log('Pickup selection changed to:', pickup);

      let destinations = [];

      // Explicit if-else conditions for dynamic destinations based on mappings
      if (typeof JB_Ajax !== 'undefined' && JB_Ajax.destinationMappings) {
        destinations = JB_Ajax.destinationMappings[pickup] || [];
      }

      // Hotel logic for Disneyland removed as requested - using address input instead

      // Show/Hide train station wrapper
      if (pickup === 'Paris Train Stations') {
        const wrapper = $('#pickup_train_station_wrapper');
        wrapper.show();
        const station = document.querySelector('#pickup_train_station');
        if (station && station.refresh) station.refresh();
      } else {
        $('#pickup_train_station_wrapper').hide();
        const station = document.querySelector('#pickup_train_station');
        if (station && station.reset) station.reset();
      }

      // Show/Hide Disney Hotel wrapper
      if (pickup === 'Disneyland and Hotels') {
        $('#pickup_disney_hotel_wrapper').show();
        const hotel = document.querySelector('#pickup_disney_hotel');
        if (hotel && hotel.refresh) hotel.refresh();
      } else {
        $('#pickup_disney_hotel_wrapper').hide();
        const hotel = document.querySelector('#pickup_disney_hotel');
        if (hotel && hotel.reset) hotel.reset();
      }

      // Format options for VirtualSelect
      const options = destinations.map(dest => ({
        label: dest,
        value: dest
      }));

      // Update destination dropdown
      const destSelect = document.querySelector('#destination_location');
      if (destSelect && destSelect.setOptions) {
        destSelect.setOptions(options);
        destSelect.reset();
      }

      checkAddressInputSelection();
      updatePrices();
    });
  }

  const destEle = document.querySelector('#destination_location');
  if (destEle) {
    destEle.addEventListener('change', function () {
      const destination = Array.isArray(this.value) ? this.value[0] : this.value;
      // Hotel logic for Disneyland removed as requested - using address input instead

      // Show/Hide train station wrapper
      if (destination === 'Paris Train Stations') {
        const wrapper = $('#destination_train_station_wrapper');
        wrapper.show();
        const station = document.querySelector('#destination_train_station');
        if (station && station.refresh) station.refresh();
      } else {
        $('#destination_train_station_wrapper').hide();
        const station = document.querySelector('#destination_train_station');
        if (station && station.reset) station.reset();
      }

      // Show/Hide Disney Hotel wrapper
      if (destination === 'Disneyland and Hotels') {
        $('#destination_disney_hotel_wrapper').show();
        const hotel = document.querySelector('#destination_disney_hotel');
        if (hotel && hotel.refresh) hotel.refresh();
      } else {
        $('#destination_disney_hotel_wrapper').hide();
        const hotel = document.querySelector('#destination_disney_hotel');
        if (hotel && hotel.reset) hotel.reset();
      }
      checkAddressInputSelection();
      updatePrices();
    });
  }

  const paymentEle = document.querySelector('#payment_method');
  if (paymentEle) {
    paymentEle.addEventListener('change', updatePrices);
  }

  $('input[name="journey_type"]').on('change', function () {
    const isRound = $(this).val() === 'round';
    const wrapper = $('#return_journey_details');
    if (isRound) {
      wrapper.show();
      wrapper.find('input').prop('disabled', false);
    } else {
      wrapper.hide();
      wrapper.find('input').prop('disabled', true).val('');
    }
    updatePrices();
  });
  $('input[name="vehicle"]').on('change', updatePrices);

  // Add event listeners for Disney Hotel dropdowns to show address if "Others" is selected
  const pickupDisneyEle = document.querySelector('#pickup_disney_hotel');
  if (pickupDisneyEle) {
    pickupDisneyEle.addEventListener('change', function () {
      checkAddressInputSelection();
    });
  }

  const destDisneyEle = document.querySelector('#destination_disney_hotel');
  if (destDisneyEle) {
    destDisneyEle.addEventListener('change', function () {
      checkAddressInputSelection();
    });
  }

  // Trigger initial price update
  updatePrices();

  // Sync details UI state on load
  $('#need_luggage, #need_baby').trigger('change');
  $('input[name="journey_type"]:checked').trigger('change');
  checkAddressInputSelection();

  // Hide email mismatch error while typing
  $('#email, #confirm_email').on('input', function () {
    $('#email_mismatch_error').hide();
  });

  $('#journeyBookingForm').on('submit', function (e) {
    e.preventDefault();

    const $btn = $('#submit_button');

    // Validate Step 3
    if (!validateStep(2)) {
      return;
    }

    // Disable button + change text
    $btn.prop('disabled', true).text('Processing...');

    if (typeof JB_Ajax === 'undefined') {
      alert('Error: Booking data not loaded.');
      $btn.prop('disabled', false).text('Complete Booking');
      return;
    }

    let formData = $(this).serialize();

    if (iti) {
      const dialCode = '+' + iti.getSelectedCountryData().dialCode;
      formData += '&dial_code=' + encodeURIComponent(dialCode);
    }

    // Ensure optional fields
    if (!$('#need_luggage').is(':checked')) {
      formData += '&has_luggage=No';
    }
    if (!$('#need_baby').is(':checked')) {
      formData += '&has_baby=No';
    }

    $.post(JB_Ajax.ajaxurl, {
      action: 'submit_journey_booking',
      data: formData
    })
      .done(function (res) {
        if (res.success) {
          // Hide form
          $('#journeyBookingForm').hide();
          $('#journeyBookingSteps').hide();

          // Show success UI
          $('#bookingRef').text(res.data.id);
          $('#bookingTotalAmount').text('€' + res.data.amount);
          $('#bookingSuccess').fadeIn();
        } else {
          alert(res.data?.message || 'Something went wrong');
          $btn.prop('disabled', false).text('Complete Booking');
        }
      })
      .fail(function () {
        alert('Server error. Please try again.');
        $btn.prop('disabled', false).text('Complete Booking');
      });
  });

});
