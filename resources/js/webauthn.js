/**
 * WebAuthn Helper (SOLID: Encapsulated Logic)
 */
const WebAuthn = {
    // Check for Platform Authenticator Support (Fingerprint, FaceID, Windows Hello)
    async isAvailable() {
        if (!window.isSecureContext) return false;
        if (!navigator.credentials) return false;
        if (!window.PublicKeyCredential) return false;
        
        try {
            return await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
        } catch (e) {
            console.warn("WebAuthn capability check failed", e);
            return false;
        }
    },

    // Register (Attestation)
    async register() {
        console.log("WebAuthn: Register initiated");

        // 0. Check Environment
        if (!window.isSecureContext) {
            window.Swal.fire({
                icon: 'error',
                title: 'Security Error',
                text: 'Fingerprint authentication requires a secure HTTPS connection or localhost. It will not work on HTTP (except localhost).',
                footer: '<a href="https://developer.mozilla.org/en-US/Web/Security/Secure_Contexts" target="_blank">Why is this happening?</a>'
            });
            return;
        }

        if (!navigator.credentials) {
            window.Swal.fire('Error', 'Your browser does not support WebAuthn or it is disabled.', 'error');
            return;
        }

        try {
            // 1. Get Base URL from Meta Tag (Robustness Fix)
            const baseUrlMeta = document.querySelector('meta[name="app-url"]');
            const baseUrl = baseUrlMeta ? baseUrlMeta.content : window.location.origin;

            // Construct absolute URL to ensure we hit the correct path (e.g. localhost/pos/webauthn...)
            // Remove trailing slash from base if present to avoid double slashes
            const cleanBase = baseUrl.replace(/\/$/, "");
            const url = `${cleanBase}/webauthn/register/options`;

            console.log("WebAuthn: Base URL:", cleanBase);
            console.log("WebAuthn: Final API URL:", url);

            const response = await axios.post(url);
            const options = response.data;
            console.log("WebAuthn: Options received", options);

            // 2. Encode for Browser
            options.challenge = this.bufferDecode(options.challenge);
            options.user.id = this.bufferDecode(options.user.id);

            console.log("WebAuthn: RP Config:", options.rp);

            if (options.excludeCredentials) {
                options.excludeCredentials.forEach(cred => {
                    cred.id = this.bufferDecode(cred.id);
                });
            }

            // 3. Create Credential
            console.log("WebAuthn: Creating credential...");
            const credential = await navigator.credentials.create({
                publicKey: options
            });
            console.log("WebAuthn: Credential created", credential);

            // 4. Decode for Server
            const attestation = {
                id: credential.id,
                rawId: this.bufferEncode(credential.rawId),
                type: credential.type,
                response: {
                    attestationObject: this.bufferEncode(credential.response.attestationObject),
                    clientDataJSON: this.bufferEncode(credential.response.clientDataJSON)
                }
            };
            if (credential.response.getTransports) {
                attestation.response.transports = credential.response.getTransports();
            }

            // 5. Send to Server
            console.log("WebAuthn: Sending validation to server...");
            await axios.post('/webauthn/register', attestation);

            window.Swal.fire('Success', 'Fingerprint registered successfully!', 'success');

        } catch (error) {
            console.error("WebAuthn Error:", error);
            let msg = 'Failed to register passkey.';

            if (error.response && error.response.data) {
                // Laragear often returns { message: "..." }
                if (error.response.data.message) msg = error.response.data.message;
            } else if (error.message) {
                msg = error.message;
            }

            if (error.name === 'NotAllowedError') {
                msg = 'Registration was canceled or timed out. Please try again.';
            }

            window.Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: msg,
                footer: 'Check console (F12) for more details.'
            });
        }
    },

    // Login (Assertion)
    async login(email = null) {
        try {
            // 1. Get Options
            // We can send email if we want to target a specific user, or empty for "Discoverable Credential" (Passkey) logic if supported
            const response = await axios.post('/webauthn/login/options', { email });
            const options = response.data;

            // 2. Encode
            options.challenge = this.bufferDecode(options.challenge);
            if (options.allowCredentials) {
                options.allowCredentials.forEach(cred => {
                    cred.id = this.bufferDecode(cred.id);
                });
            }

            // 3. Get Credential
            const credential = await navigator.credentials.get({
                publicKey: options
            });

            // 4. Decode
            const assertion = {
                id: credential.id,
                rawId: this.bufferEncode(credential.rawId),
                type: credential.type,
                response: {
                    authenticatorData: this.bufferEncode(credential.response.authenticatorData),
                    clientDataJSON: this.bufferEncode(credential.response.clientDataJSON),
                    signature: this.bufferEncode(credential.response.signature),
                    userHandle: credential.response.userHandle ? this.bufferEncode(credential.response.userHandle) : null
                }
            };

            // 5. Send to Server
            const verifyResponse = await axios.post('/webauthn/login', assertion);

            if (verifyResponse.data.redirect) {
                window.location.href = verifyResponse.data.redirect;
            }

        } catch (error) {
            console.error(error);
            window.Swal.fire('Error', 'Authentication failed. Please try again.', 'error');
        }
    },

    // Utilities
    bufferDecode(value) {
        return Uint8Array.from(atob(value.replace(/-/g, "+").replace(/_/g, "/")), c => c.charCodeAt(0));
    },

    bufferEncode(value) {
        return btoa(String.fromCharCode.apply(null, new Uint8Array(value)))
            .replace(/\+/g, "-")
            .replace(/\//g, "_")
            .replace(/=/g, "");
    }
};

window.WebAuthn = WebAuthn;
