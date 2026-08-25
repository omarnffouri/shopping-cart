export function useTracking() {
    const isLiveDomain = () => {
        if (!process.client) return false;
        const host = window.location.hostname;
        return host === 'givvo.ae' || host === 'www.givvo.ae';
    };

    const ensureGtag = () => {
        if (!process.client) return false;
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
        return typeof window.gtag === 'function';
    };

    const trackPurchase = ({ orderId, value, currency = 'AED' }) => {
        if (!isLiveDomain()) return;
        if (!ensureGtag()) return;

        window.gtag('event', 'conversion', {
            send_to: 'AW-959373833/Vfc2CPqD9PcbEInEu8kD',
            value: Number(value) || 1.0,
            currency: currency,
            transaction_id: `ORDER_${String(orderId || '')}`,
        });

        if (typeof window.fbq === 'function') {
            window.fbq('track', 'Purchase', {
                value: Number(value) || 1.0,
                currency: currency,
                order_id: String(orderId || ''),
            });
        }
    };

    const trackAddToCart = ({ value, currency = 'AED', items = [] }) => {
        if (!isLiveDomain()) return;
        if (!ensureGtag()) return;

        window.gtag('event', 'add_to_cart', { value: Number(value) || 0, currency, items });

        if (typeof window.fbq === 'function') {
            window.fbq('track', 'AddToCart', { value: Number(value) || 0, currency });
        }
    };

    const trackRemoveFromCart = ({ value, currency = 'AED', items = [] }) => {
        if (!isLiveDomain()) return;
        if (!ensureGtag()) return;

        window.gtag('event', 'remove_from_cart', { value: Number(value) || 0, currency, items });

        if (typeof window.fbq === 'function') {
            window.fbq('track', 'RemoveFromCart', { value: Number(value) || 0, currency });
        }
    };

    return { trackPurchase, trackAddToCart, trackRemoveFromCart };
}
