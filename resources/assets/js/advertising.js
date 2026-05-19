/**
 * Advertising module JavaScript
 *
 * - Anchor text character counter for order-link form
 * - Month selector price preview (reads window.XMR_RATE)
 * - Payment confirmation modal trigger
 * - Copy-to-clipboard for XMR amount and wallet address
 */

(function () {
    'use strict';

    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Copy text to clipboard using the Clipboard API with execCommand fallback.
     *
     * @param {string} text
     */
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).catch(function () {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        var el = document.createElement('textarea');
        el.value = text;
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
    }

    // ──────────────────────────────────────────────────────────────
    // Anchor text character counter (order-link form)
    // ──────────────────────────────────────────────────────────────
    var anchorInput = document.getElementById('anchor_text');
    var charCount   = document.getElementById('char-count');

    if (anchorInput && charCount) {
        function updateCharCount() {
            var len = anchorInput.value.length;
            charCount.textContent = len;
            charCount.style.color = len > 60 ? 'red' : '';
        }

        anchorInput.addEventListener('input', updateCharCount);
        updateCharCount();
    }

    // ──────────────────────────────────────────────────────────────
    // Month selector price preview
    // ──────────────────────────────────────────────────────────────
    var monthSelect   = document.getElementById('month_count');
    var pricePreview  = document.getElementById('price-preview');
    var xmrRate       = window.XMR_RATE || 0;
    var pricePerMonth = window.BANNER_PRICE_USD || window.LINK_PRICE_USD || 0;

    if (monthSelect && pricePreview && xmrRate > 0 && pricePerMonth > 0) {
        function updatePricePreview() {
            var months    = parseInt(monthSelect.value, 10) || 1;
            var usdTotal  = pricePerMonth * months;
            var xmrTotal  = xmrRate > 0 ? (usdTotal / xmrRate).toFixed(6) : '—';

            pricePreview.textContent = 'Total: $' + usdTotal.toFixed(2) + ' ≈ ' + xmrTotal + ' XMR';
        }

        monthSelect.addEventListener('change', updatePricePreview);
        updatePricePreview();
    }

    // ──────────────────────────────────────────────────────────────
    // Copy buttons on pay page
    // ──────────────────────────────────────────────────────────────
    var copyAmountBtn = document.getElementById('copy-amount');
    var copyWalletBtn = document.getElementById('copy-wallet');
    var xmrAmountEl   = document.getElementById('xmr-amount');
    var walletAddrEl  = document.getElementById('wallet-address');

    if (copyAmountBtn && xmrAmountEl) {
        copyAmountBtn.addEventListener('click', function () {
            var amountText = xmrAmountEl.textContent.replace(/[^0-9.]/g, '');
            copyToClipboard(amountText);
            copyAmountBtn.textContent = 'Copied!';
            setTimeout(function () {
                copyAmountBtn.innerHTML = '<i class="fas fa-copy mr-1"></i>Copy Amount';
            }, 2000);
        });
    }

    if (copyWalletBtn && walletAddrEl) {
        copyWalletBtn.addEventListener('click', function () {
            copyToClipboard(walletAddrEl.textContent.trim());
            copyWalletBtn.textContent = 'Copied!';
            setTimeout(function () {
                copyWalletBtn.innerHTML = '<i class="fas fa-copy mr-1"></i>Copy Address';
            }, 2000);
        });
    }

    // ──────────────────────────────────────────────────────────────
    // "I've Sent My Payment" → open Bootstrap confirmation modal
    // ──────────────────────────────────────────────────────────────
    var paymentSentBtn = document.getElementById('payment-sent-btn');
    var confirmSubmit  = document.getElementById('confirm-submit');
    var payNotedForm   = document.getElementById('pay-noted-form');

    if (paymentSentBtn) {
        paymentSentBtn.addEventListener('click', function () {
            if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
                $('#payConfirmModal').modal('show');
            } else if (payNotedForm) {
                // Fallback when Bootstrap JS is unavailable
                if (window.confirm('Have you already sent the XMR payment? Submitting without sending will delay approval.')) {
                    payNotedForm.submit();
                }
            }
        });
    }

    if (confirmSubmit && payNotedForm) {
        confirmSubmit.addEventListener('click', function () {
            payNotedForm.submit();
        });
    }
})();
