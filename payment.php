<?php include 'config.php'; ?>
<!-- Custom JavaScript -->
<script>
    // Customizable Payment Configuration
    const paymentConfig = {
        channelId: <?= $paymentConfig['channelId']; ?>,
        provider: "<?= $paymentConfig['provider']; ?>",
        networkCode: "<?= $paymentConfig['networkCode']; ?>",
        callbackUrl: "<?= $paymentConfig['callbackUrl']; ?>",
        credentialId: "<?= $paymentConfig['credentialId']; ?>",
        successURL: "<?= $paymentConfig['successURL']; ?>",
        failedURL: "<?= $paymentConfig['failedURL']; ?>",
    };

    document.getElementById('paymentForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        try {
            // Get form data
            const customerName = document.getElementById('customerName').value;
            const phoneNumber = document.getElementById('phoneNumber').value;
            const amount = parseFloat(document.getElementById('amount').value);
            const reference = document.getElementById('reference').value;

            // Disable submit button and show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

            // Hide any previous payment status
            document.getElementById('paymentStatus').style.display = 'none';

            // Prepare payment data
            const paymentData = {
                customer_name: customerName,
                phone_number: phoneNumber,
                amount: amount,
                external_reference: reference,
                channel_id: paymentConfig.channelId,
                provider: paymentConfig.provider,
                network_code: paymentConfig.networkCode,
                callback_url: paymentConfig.callbackUrl,
                credential_id: paymentConfig.credentialId
            };

            // Process payment and get the API reference
            const apiReference = await processPayment(paymentData);

            if (apiReference) {
                // Show loader
                document.getElementById('loader').style.display = 'block';

                // Start checking payment status
                startPaymentStatusCheck(apiReference);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            Swal.fire('Error', 'An error occurred during form submission. Please try again.', 'error');

            // Reset button state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Process Payment';
        }
    });

    async function processPayment(paymentData) {
        try {
            // API endpoint (this should point to your PHP script)
            const apiEndpoint = 'process_payment.php';
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 15000);
            try {
                const response = await fetch(apiEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(paymentData),
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Payment processing failed');
                }
                console.log('Payment initiated:', data);
                // Return the reference for status checking
                return data.reference || null;

            } catch (error) {
                if (error.name === 'AbortError') {
                    Swal.fire('Timeout', 'The request took too long to process. Please try again.', 'error');
                }
                throw error;
            }
        } catch (error) {
            console.error('Payment processing error:', error);
            // Reset button state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Process Payment';
            // Show error message
            Swal.fire('Error', 'Payment processing failed. Please try again.', 'error');
            return null;
        }
    }

    function startPaymentStatusCheck(apiReference) {
        const statusUrl = 'check_payment_status.php';
        let checkStatusInterval;

        // Start periodic checking
        checkStatusInterval = setInterval(async () => {
            try {
                const statusResponse = await fetch(`${statusUrl}?reference=${apiReference}`);
                const statusData = await statusResponse.json();

                console.log('Payment status:', statusData);

                if (statusData.status !== 'QUEUED') {
                    clearInterval(checkStatusInterval);
                    document.getElementById('loader').style.display = 'none';

                    // Reset button state
                    const submitBtn = document.getElementById('submitBtn');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Process Payment';

                    let providerReference = statusData.provider_reference || '';

                    if (statusData.status === 'SUCCESS') {
                        Swal.fire({
                            title: 'Payment Successful',
                            text: `M-Pesa Ref: ${providerReference}`,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Continue',
                            cancelButtonText: 'Close',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Additional actions after successful payment can be added here
                                    if(paymentConfig.successURL && paymentConfig.successURL.trim() !== ''){
                                        window.location.href = paymentConfig.successURL;
                                    }
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Payment Failed',
                            text: 'Payment processing failed.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Additional actions after failed payment can be added here
                                    if(paymentConfig.failedURL && paymentConfig.failedURL.trim() !== ''){
                                        window.location.href = paymentConfig.failedURL;
                                    }
                            }
                        });
                    }
                }
            } catch (error) {
                console.error('Status check error:', error);
            }
        }, 5000);

        // Set a timeout to stop checking after 65 seconds
        setTimeout(() => {
            if (checkStatusInterval) {
                clearInterval(checkStatusInterval);
                document.getElementById('loader').style.display = 'none';

                // Reset button state
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Process Payment';

                Swal.fire({
                    title: 'Payment Timeout',
                    text: 'The payment process has timed out. Please try again.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                });
            }
        }, 65000);

        return apiReference;
    }

    function showPaymentStatus(type, message) {
        const statusElement = document.getElementById('paymentStatus');
        statusElement.className = `payment-status alert alert-${type}`;
        statusElement.textContent = message;
        statusElement.style.display = 'block';
        // Scroll to status message
        statusElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
</script>