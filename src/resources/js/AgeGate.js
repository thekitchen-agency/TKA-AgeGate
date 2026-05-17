document.addEventListener('DOMContentLoaded', function () {
  console.log('loaded');
  window.scrollTo(0, 0);

  // Handle separate character inputs
  const charInputs = document.querySelectorAll('.ag-char-input');
  if (charInputs.length > 0) {
    function updateHiddenInputs() {
      // Handle Year
      document.querySelectorAll('.ag-year-inputs').forEach(yearWrapper => {
        const inputs = yearWrapper.querySelectorAll('.ag-char-input');
        const hidden = yearWrapper.querySelector('input[type="hidden"]');
        if (hidden) {
          let val = '';
          inputs.forEach(i => val += i.value);
          hidden.value = val;
        }
      });

      // Handle Birthdate
      document.querySelectorAll('.ag-birthdate-inputs').forEach(bdWrapper => {
        const hidden = bdWrapper.querySelector('input[type="hidden"]');
        if (hidden) {
          const dInputs = bdWrapper.querySelectorAll('.ag-d');
          const mInputs = bdWrapper.querySelectorAll('.ag-m');
          const yInputs = bdWrapper.querySelectorAll('.ag-y');
          
          let d = '', m = '', y = '';
          if (dInputs.length) dInputs.forEach(i => d += i.value);
          if (mInputs.length) mInputs.forEach(i => m += i.value);
          if (yInputs.length) yInputs.forEach(i => y += i.value);
          
          if (d.length === 2 && m.length === 2 && (y.length === 2 || y.length === 4)) {
            if (y.length === 2) {
              if (parseInt(y, 10) > 50) y = '19' + y;
              else y = '20' + y;
            }
            hidden.value = `${y}-${m}-${d}`;
          } else {
            // Just join everything so validation fails normally if incomplete
            const allInputs = bdWrapper.querySelectorAll('.ag-char-input');
            let val = '';
            allInputs.forEach(i => val += i.value);
            hidden.value = val;
          }
        }
      });
    }

    charInputs.forEach((input, index) => {
      input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 1);
        if (this.value.length === 1 && index < charInputs.length - 1) {
          charInputs[index + 1].focus();
        }
        updateHiddenInputs();
      });

      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
          charInputs[index - 1].focus();
        }
      });
    });
  }

  if (document.getElementById('agegate')) {

    document.body.classList.add('agegate-active');

    document.querySelector('#agegate .decline-ag').addEventListener('click', function (e) {
      e.preventDefault();
      window.location = window.agegatesettings.declineUrl;
    });

    const modalForm = document.getElementById('agegate-modal-form');
    if (modalForm) {
      modalForm.addEventListener('submit', function (e) {
        e.preventDefault();

        let isValid = true;
        const valType = window.agegatesettings.validationType || 'simple';
        const minAge = parseInt(window.agegatesettings.minimumAgeAllowed, 10);

        if (valType === 'year_only') {
          const yearInput = document.getElementById('ag-year');
          if (!yearInput || !yearInput.value) return;
          const age = new Date().getFullYear() - parseInt(yearInput.value, 10);
          if (age < minAge) isValid = false;
        } else if (valType === 'birthdate') {
          const dateInput = document.getElementById('ag-birthdate');
          if (!dateInput || !dateInput.value) return;
          const birth = new Date(dateInput.value);
          const today = new Date();
          let age = today.getFullYear() - birth.getFullYear();
          if (today.getMonth() < birth.getMonth() || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())) {
            age--;
          }
          if (age < minAge) isValid = false;
        }

        if (isValid) {
          setCookie();
          document.body.classList.remove('agegate-active');
          document.getElementById('agegate').remove();
          const overlay = document.querySelector('.ag-verifyOverlay');
          if (overlay) overlay.remove();
        } else {
          const errorMsg = document.getElementById('modal-age-error');
          if (errorMsg) errorMsg.style.display = 'block';
        }
      });
    } else {
      // Fallback if no form exists
      const confirmBtn = document.querySelector('#agegate .confirm-ag');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', function (e) {
          e.preventDefault();
          setCookie();
          document.body.classList.remove('agegate-active');

          if (window.agegatesettings.displayType === 'modal') {
            document.getElementById('agegate').remove();
            const overlay = document.querySelector('.ag-verifyOverlay');
            if (overlay) overlay.remove();
          }

          if (window.agegatesettings.displayType === 'redirect') {
            if (window.riveinstance) {
              window.playAgeGate();
              setTimeout(() => {
                window.location.pathname = '/';
              }, 750);
            }
          }
        });
      }
    }
  }
});

function setCookie() {
  let cookieName = window.agegatesettings.cookieName;
  document.cookie = `${cookieName}=1; SameSite=lax; Secure; path=/`;
}

// Age Gate Helper Functions for Custom Pages
window.AgeGateHelpers = {
  /**
   * Set a cookie with specified name, value, and expiration days
   * @param {string} name - Cookie name
   * @param {string} value - Cookie value
   * @param {number} days - Number of days until expiration
   */
  setCookie: function(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=lax;Secure';
  },

  /**
   * Get a cookie value by name
   * @param {string} name - Cookie name
   * @returns {string|null} Cookie value or null if not found
   */
  getCookie: function(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) === ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  },

  /**
   * Calculate age from birthdate
   * @param {Date|string} birthdate - Birth date
   * @returns {number} Calculated age
   */
  calculateAge: function(birthdate) {
    const today = new Date();
    const birth = new Date(birthdate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
      age--;
    }
    return age;
  },

  /**
   * Verify if user meets minimum age requirement
   * @param {Date|string} birthdate - Birth date
   * @returns {boolean} True if age requirement is met
   */
  verifyAge: function(birthdate) {
    const age = this.calculateAge(birthdate);
    return age >= (window.agegatesettings ? window.agegatesettings.minimumAgeAllowed : 18);
  },

  /**
   * Redirect to original URL or fallback
   * @param {string} fallback - Fallback URL if original URL is not available
   */
  redirectToOriginal: function(fallback = '/') {
    const originalUrl = window.originalSrcUrl || fallback;
    window.location.href = originalUrl;
  },

  /**
   * Redirect to decline URL
   */
  redirectToDecline: function() {
    const declineUrl = window.agegatesettings ? window.agegatesettings.declineUrl : 'https://www.google.com';
    window.location.href = declineUrl;
  },

  /**
   * Set age verification cookie and redirect
   * @param {string} fallback - Fallback URL if original URL is not available
   */
  verifyAndRedirect: function(fallback = '/') {
    const cookieName = window.agegatesettings ? window.agegatesettings.cookieName : 'agegate';
    this.setCookie(cookieName, 'verified', 365);
    this.redirectToOriginal(fallback);
  },

  /**
   * Check if age has already been verified
   * @returns {boolean} True if already verified
   */
  isAgeVerified: function() {
    const cookieName = window.agegatesettings ? window.agegatesettings.cookieName : 'agegate';
    return this.getCookie(cookieName) !== null;
  },

  /**
   * Format age gate text with placeholders replaced
   * @param {string} text - Text with placeholders
   * @returns {string} Formatted text
   */
  formatText: function(text) {
    if (!window.agegatesettings) return text;
    
    let formatted = text;
    formatted = formatted.replace(/\[minAge\]/g, window.agegatesettings.minimumAgeAllowed);
    
    // Replace privacy policy link if available
    if (window.agegatesettings.privacyPolicyUrl) {
      formatted = formatted.replace(/\[privacy_page\]/g, 
        '<a href="' + window.agegatesettings.privacyPolicyUrl + '">Privacy Policy</a>');
    }
    
    // Replace cookie policy link if available
    if (window.agegatesettings.cookiePolicyUrl) {
      formatted = formatted.replace(/\[cookie_page\]/g, 
        '<a href="' + window.agegatesettings.cookiePolicyUrl + '">Cookie Policy</a>');
    }
    
    return formatted;
  },

  /**
   * Initialize age gate form with automatic validation
   * @param {string} formSelector - CSS selector for the form
   * @param {string} dateInputSelector - CSS selector for the date input
   * @param {string} submitButtonSelector - CSS selector for the submit button
   */
  initAgeGateForm: function(formSelector, dateInputSelector, submitButtonSelector) {
    const form = document.querySelector(formSelector);
    const dateInput = document.querySelector(dateInputSelector);
    const submitButton = document.querySelector(submitButtonSelector);
    
    if (!form || !dateInput || !submitButton) return;
    
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const birthdate = dateInput.value;
      if (!birthdate) {
        alert('Please enter your birth date.');
        return;
      }
      
      if (window.AgeGateHelpers.verifyAge(birthdate)) {
        window.AgeGateHelpers.verifyAndRedirect();
      } else {
        window.AgeGateHelpers.redirectToDecline();
      }
    });
  }
};
