<?php

use App\Validator;

class Request {

    protected static ?array $headerCache = null;

    /**
     * Return all headers (cached).
     * 
     * @return array of headers in key value
     */
    public static function headers(): array
    {
        if (self::$headerCache !== null) {
            return self::$headerCache;
        }

        $headers = [];

        foreach ($_SERVER as $key => $value) {
            // HTTP_*
            if (strpos($key, 'HTTP_') === 0) {
                $name = substr($key, 5);
            }
            // CONTENT_* headers (not prefixed by HTTP_)
            elseif (preg_match('/^CONTENT_(TYPE|LENGTH|MD5)$/', $key)) {
                $name = $key;
            } else {
                continue;
            }

            $name = self::normalizeHeader($name);
            $headers[$name] = $value;
        }

        return self::$headerCache = $headers;
    }

    /**
     * Get a single header by name.
     * @param $name Name of the header
     */
    public static function header(string $name, $default = null)
    {
        $headers = self::headers();
        $key = self::normalizeHeader($name);

        return $headers[$key] ?? $default;
    }

    /**
     * Normalize header names to "Header-Name" case.
     * @param $name Header name to normalize
     * @return string Normalized header
     */
    protected static function normalizeHeader(string $name): string
    {
        $name = str_replace('_', '-', strtolower($name));
        return implode('-', array_map('ucfirst', explode('-', $name)));
    }

    public static function verifyCSRF(): bool 
    {
        assert($_SERVER['REQUEST_METHOD'] !== 'POST', "Verifying CSRF should only be via POST, GET should be idempotent.");

        $verified = $_POST['csrf-token'] === $_SESSION['csrf-token'];
        unset($_SESSION['csrf-token']);
        return $verified;
    }

    public static function getVal(string $key, $default = null) {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method === 'POST')
        {
            return $_POST[$key] ?? $default;
        } else {
            return $_GET[$key] ?? $default;
        }
    }

    public static function getAll()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if($method === 'POST')
        {
            return $_POST;
        } else {
            return $_GET;
        }
    }

    public static function validate(array $rules)
    {
        Validator::validate(self::getAll(), $rules);
    }

    /**
     * Get the client's IP address in a proxy-aware way.
     *
     * WARNING:
     *   Only trust X-Forwarded-For / X-Real-IP if you're behind a known trusted proxy.
     * 
     * @return string Possible IP of the client
     */
    public static function ip(): string
    {
        // Trusted proxy handling — only enable if your environment uses them!
        $trustedHeaders = [
            'X-Forwarded-For',
            'X-Real-IP',
        ];

        foreach ($trustedHeaders as $h) {
            $value = self::header($h);
            if ($value) {
                // X-Forwarded-For may contain multiple addresses
                if ($h === 'X-Forwarded-For') {
                    $parts = explode(',', $value);
                    return trim($parts[0]);
                }

                return $value;
            }
        }

        // Default
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function path(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Strip query string
        $path = parse_url($uri, PHP_URL_PATH);

        // Guarantee leading slash, prevent trailing slash issues
        return '/' . trim($path, '/');
    }

    public static function isAjax(): bool
    {
        return strtolower(self::header('X-Requested-With')) === 'xmlhttprequest';
    }

    public static function isJson(): bool
    {
        $accept = strtolower(self::header('Accept', ''));

        return str_contains($accept, 'application/json');
    }

    public static function contentTypeIs(string $type): bool
    {
        return strtolower(self::header('Content-Type', '')) === strtolower($type);
    }

    public static function bearerToken(): ?string
    {
        $h = self::header('Authorization');
        if (!$h) return null;

        if (preg_match('/Bearer\s+(.+)/i', $h, $m)) {
            return trim($m[1]);
        }

        return null;
    }
}

?>