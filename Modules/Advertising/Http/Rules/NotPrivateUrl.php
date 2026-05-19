<?php

namespace Modules\Advertising\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Ensures a URL does not resolve to a private / loopback IP address.
 *
 * Blocked ranges:
 *   - 127.0.0.0/8   (loopback)
 *   - 10.0.0.0/8    (RFC-1918)
 *   - 172.16.0.0/12 (RFC-1918)
 *   - 192.168.0.0/16 (RFC-1918)
 */
class NotPrivateUrl implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $host = parse_url($value, PHP_URL_HOST);

        if ($host === false || $host === null || $host === '') {
            return false;
        }

        // Resolve the hostname to an IP address.
        $ip = gethostbyname($host);

        return !$this->isPrivateIp($ip);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The destination URL must not point to a private or loopback address.';
    }

    /**
     * Return true if the given IP address falls within a private / loopback range.
     *
     * @param string $ip
     * @return bool
     */
    private function isPrivateIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
