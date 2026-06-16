document.addEventListener('DOMContentLoaded', () => {
    // 1. Password Visibility Toggle
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const parent = input.parentElement;
        if (parent) {
            const floatGroup = input.closest('.float-group');
            const isFloatGroup = floatGroup !== null;

            // Create a wrapper for the input field to align toggle icon perfectly inside input
            const wrapper = document.createElement('div');
            wrapper.className = 'relative-input-wrapper';
            wrapper.style.position = 'relative';
            wrapper.style.display = 'block';
            wrapper.style.width = '100%';

            // Check if there is an adjacent sibling label (typical for floating labels)
            const nextSibling = input.nextElementSibling;
            const hasFloatingLabel = nextSibling && nextSibling.tagName.toLowerCase() === 'label';

            // Insert wrapper in DOM and wrap the input (and label if floating)
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
            if (hasFloatingLabel) {
                wrapper.appendChild(nextSibling);
            }

            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            
            toggleBtn.className = 'focus:outline-none';
            toggleBtn.style.position = 'absolute';
            toggleBtn.style.right = '16px';
            toggleBtn.style.top = '50%';
            toggleBtn.style.transform = 'translateY(-50%)';
            toggleBtn.style.zIndex = '10';
            toggleBtn.style.color = isFloatGroup ? 'rgba(255, 255, 255, 0.4)' : 'rgba(0, 0, 0, 0.4)';
            toggleBtn.style.background = 'none';
            toggleBtn.style.border = 'none';
            toggleBtn.style.cursor = 'pointer';
            toggleBtn.style.padding = '0';
            toggleBtn.style.width = '24px';
            toggleBtn.style.height = '24px';
            toggleBtn.style.display = 'flex';
            toggleBtn.style.alignItems = 'center';
            toggleBtn.style.justifyContent = 'center';
            
            const eyeOpenSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5M12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5s5 2.24 5 5s-2.24 5-5 5m0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3s3-1.34 3-3s-1.34-3-3-3"/></svg>';
            const eyeClosedSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M12 7c2.76 0 5 2.24 5 5c0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75c-1.73-4.39-6-7.5-11-7.5c-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7M2 4.27l2.28 2.28l.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5c1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22L21 20.73L3.27 3zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65a3 3 0 0 0 3 3c.22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53a5 5 0 0 1-5-5c0-.79.2-1.53.53-2.2m4.31-.78l3.15 3.15l.02-.16a3 3 0 0 0-3-3z"/></svg>';

            toggleBtn.innerHTML = eyeOpenSVG;

            // Pad input so text doesn't overlap eye
            input.style.paddingRight = '45px';
            wrapper.appendChild(toggleBtn);

            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (input.type === 'password') {
                    input.type = 'text';
                    toggleBtn.innerHTML = eyeClosedSVG;
                    toggleBtn.style.color = isFloatGroup ? 'rgba(255, 255, 255, 0.4)' : 'rgba(0, 0, 0, 0.4)';
                } else {
                    input.type = 'password';
                    toggleBtn.innerHTML = eyeOpenSVG;
                    toggleBtn.style.color = isFloatGroup ? 'rgba(255, 255, 255, 0.4)' : 'rgba(0, 0, 0, 0.4)';
                }
            });
        }
    });

    // 2. Real-time Register Form Validation
    const regForm = document.getElementById('register-form') || document.querySelector('form[action="register.php"]');
    if (regForm) {
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');

        const createFeedbackElement = (input) => {
            const container = input.closest('.float-group') || input.closest('.space-y-4') || input.parentElement;
            let fb = container.querySelector('.feedback-msg');
            if (!fb) {
                fb = document.createElement('div');
                fb.className = 'feedback-msg text-xs mt-1 transition-all duration-200';
                fb.style.fontSize = '11px';
                container.appendChild(fb);
            }
            return fb;
        };

        const validateUsername = () => {
            if (!usernameInput) return true;
            const val = usernameInput.value.trim();
            const fb = createFeedbackElement(usernameInput);
            if (val === '') {
                fb.textContent = '';
                usernameInput.style.borderColor = '';
                return false;
            }
            if (val.length < 3) {
                fb.innerHTML = '<svg class="inline-block w-4 h-4 mr-1 text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> Username minimal 3 karakter';
                fb.style.color = '#fca5a5';
                usernameInput.style.borderColor = '#dc2626';
                return false;
            }
            fb.innerHTML = '<svg class="inline-block w-4 h-4 mr-1 text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Username valid';
            fb.style.color = '#86efac';
            usernameInput.style.borderColor = '#22c55e';
            return true;
        };

        const validateEmail = () => {
            if (!emailInput) return true;
            const val = emailInput.value.trim();
            const fb = createFeedbackElement(emailInput);
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (val === '') {
                fb.textContent = '';
                emailInput.style.borderColor = '';
                return false;
            }
            if (!regex.test(val)) {
                fb.innerHTML = '<svg class="inline-block w-4 h-4 mr-1 text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> Format email tidak valid';
                fb.style.color = '#fca5a5';
                emailInput.style.borderColor = '#dc2626';
                return false;
            }
            fb.innerHTML = '<svg class="inline-block w-4 h-4 mr-1 text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Email valid';
            fb.style.color = '#86efac';
            emailInput.style.borderColor = '#22c55e';
            return true;
        };

        const validatePassword = () => {
            if (!passwordInput) return true;
            const val = passwordInput.value;
            const fb = createFeedbackElement(passwordInput);
            if (val === '') {
                fb.textContent = '';
                passwordInput.style.borderColor = '';
                return false;
            }
            if (val.length < 6) {
                fb.innerHTML = '<svg class="inline-block w-4 h-4 mr-1 text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> Password minimal 6 karakter';
                fb.style.color = '#fca5a5';
                passwordInput.style.borderColor = '#dc2626';
                return false;
            }
            fb.innerHTML = '<svg class="inline-block w-4 h-4 mr-1 text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Password kuat';
            fb.style.color = '#86efac';
            passwordInput.style.borderColor = '#22c55e';
            return true;
        };

        const validateConfirm = () => {
            if (!confirmInput || !passwordInput) return true;
            const val = confirmInput.value;
            const fb = createFeedbackElement(confirmInput);
            if (val === '') {
                fb.textContent = '';
                confirmInput.style.borderColor = '';
                return false;
            }
            if (val !== passwordInput.value) {
                fb.innerHTML = '<svg class="inline-block w-4 h-4 mr-1 text-red-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10M8.97 8.97a.75.75 0 0 1 1.06 0L12 10.94l1.97-1.97a.75.75 0 0 1 1.06 1.06L13.06 12l1.97 1.97a.75.75 0 0 1-1.06 1.06L12 13.06l-1.97 1.97a.75.75 0 0 1-1.06-1.06L10.94 12l-1.97-1.97a.75.75 0 0 1 0-1.06" clip-rule="evenodd"/></svg> Konfirmasi password tidak cocok';
                fb.style.color = '#fca5a5';
                confirmInput.style.borderColor = '#dc2626';
                return false;
            }
            fb.innerHTML = '<svg class="inline-block w-4 h-4 mr-1 text-green-500 fill-current" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10m-5.97-3.03a.75.75 0 0 1 0 1.06l-5 5a.75.75 0 0 1-1.06 0l-2-2a.75.75 0 1 1 1.06-1.06l1.47 1.47l2.235-2.235L14.97 8.97a.75.75 0 0 1 1.06 0" clip-rule="evenodd"/></svg> Password cocok';
            fb.style.color = '#86efac';
            confirmInput.style.borderColor = '#22c55e';
            return true;
        };

        if (usernameInput) usernameInput.addEventListener('input', validateUsername);
        if (emailInput) emailInput.addEventListener('input', validateEmail);
        if (passwordInput) {
            passwordInput.addEventListener('input', () => {
                validatePassword();
                if (confirmInput && confirmInput.value !== '') {
                    validateConfirm();
                }
            });
        }
        if (confirmInput) confirmInput.addEventListener('input', validateConfirm);

        regForm.addEventListener('submit', (e) => {
            const isUNameValid = validateUsername();
            const isEmailValid = validateEmail();
            const isPassValid = validatePassword();
            const isConfirmValid = validateConfirm();

            if (!isUNameValid || !isEmailValid || !isPassValid || !isConfirmValid) {
                e.preventDefault();
                // Find first invalid input and focus it
                const firstInvalid = [usernameInput, emailInput, passwordInput, confirmInput].find(input => {
                    return input && input.style.borderColor === 'rgb(220, 38, 38)'; // '#dc2626'
                });
                if (firstInvalid) firstInvalid.focus();
            }
        });
    }
});

// 3. Forgot Password Info Warning Box
window.showForgotPasswordInfo = function() {
    const msg = document.getElementById('info-msg');
    if (msg) {
        msg.style.display = 'flex';
        msg.style.opacity = '0';
        msg.style.transition = 'opacity 0.4s ease';
        setTimeout(() => {
            msg.style.opacity = '1';
        }, 50);
    }
};
