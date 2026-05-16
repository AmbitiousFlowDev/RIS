<?php

final class Security
{
    private const CSRF_SESSION_KEY = 'csrf_token';

    private static ?string $cspNonce = null;

    public static function bootstrap(): void
    {
        self::removeServerDisclosure();
        self::configureSession();
        self::startSecureSession();
        self::csrfToken();
        self::sendSecurityHeaders();
    }

    public static function removeServerDisclosure(): void
    {
        @ini_set('expose_php', '0');

        if (!headers_sent()) {
            header_remove('X-Powered-By');
        }
    }

    public static function configureSession(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $isHttps = self::isHttps();

        session_name('RISSESSID');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        @ini_set('session.use_strict_mode', '1');
        @ini_set('session.use_only_cookies', '1');
        @ini_set('session.cookie_httponly', '1');
        @ini_set('session.cookie_samesite', 'Lax');
        @ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    }

    public static function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        $nonce = self::cspNonce();

        header_remove('X-Powered-By');

        header('X-Content-Type-Options: nosniff', true);
        header('X-Frame-Options: DENY', true);
        header('Referrer-Policy: strict-origin-when-cross-origin', true);
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), fullscreen=(self)', true);
        header('Cross-Origin-Opener-Policy: same-origin', true);
        header('Cross-Origin-Resource-Policy: same-origin', true);

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains', true);
        }

        header(
            "Content-Security-Policy: "
            . "default-src 'self'; "
            . "script-src 'self' 'nonce-$nonce'; "
            . "style-src 'self' 'nonce-$nonce'; "
            . "img-src 'self' data:; "
            . "font-src 'self' data:; "
            . "object-src 'none'; "
            . "base-uri 'self'; "
            . "form-action 'self'; "
            . "frame-ancestors 'none'; "
            . "connect-src 'self';",
            true
        );
    }

    public static function prepareHtmlResponse(string $html): string
    {
        $html = self::injectCsrfIntoForms($html);
        $html = self::stripHtmlComments($html);
        $html = self::moveInlineEventHandlersToNonceScripts($html);
        $html = self::addCspNonces($html);

        self::sendSecurityHeaders();

        return $html;
    }

    public static function cspNonce(): string
    {
        if (self::$cspNonce === null) {
            self::$cspNonce = base64_encode(random_bytes(16));
        }

        return self::$cspNonce;
    }

    private static function addCspNonces(string $html): string
    {
        $nonce = self::cspNonce();

        $html = preg_replace(
            '/<script(?![^>]*\bnonce=)([^>]*)>/i',
            '<script nonce="' . $nonce . '"$1>',
            $html
        ) ?? $html;

        $html = preg_replace(
            '/<style(?![^>]*\bnonce=)([^>]*)>/i',
            '<style nonce="' . $nonce . '"$1>',
            $html
        ) ?? $html;

        return $html;
    }

    private static function moveInlineEventHandlersToNonceScripts(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $bindings = [];
        $counter = 0;

        $html = preg_replace_callback(
            '/\s(on[a-z]+)\s*=\s*("([^"]*)"|\'([^\']*)\')/i',
            static function (array $match) use (&$bindings, &$counter): string {
                $eventAttribute = strtolower($match[1]);
                $eventName = substr($eventAttribute, 2);

                $handler = html_entity_decode(
                    $match[3] !== '' ? $match[3] : $match[4],
                    ENT_QUOTES | ENT_HTML5,
                    'UTF-8'
                );

                $id = 'csp-event-' . (++$counter);

                $bindings[] = [
                    'id' => $id,
                    'event' => $eventName,
                    'handler' => $handler,
                ];

                return ' data-csp-event-id="' . $id . '"';
            },
            $html
        ) ?? $html;

        if ($bindings === []) {
            return $html;
        }

        $script = "\n<script>\n";
        $script .= "document.addEventListener('DOMContentLoaded', function () {\n";

        foreach ($bindings as $binding) {
            $selector = '[data-csp-event-id="' . addslashes($binding['id']) . '"]';
            $event = addslashes($binding['event']);
            $handler = str_replace('</script', '<\\/script', $binding['handler']);

            $script .= "  document.querySelectorAll('" . $selector . "').forEach(function (element) {\n";
            $script .= "    element.addEventListener('" . $event . "', function (event) {\n";
            $script .= "      " . $handler . "\n";
            $script .= "    });\n";
            $script .= "  });\n";
        }

        $script .= "});\n";
        $script .= "</script>\n";

        if (stripos($html, '</body>') !== false) {
            return preg_replace('/<\/body>/i', $script . '</body>', $html, 1) ?? ($html . $script);
        }

        return $html . $script;
    }

    public static function serveStaticFile(string $file, string $extension): void
    {
        if (!is_file($file) || !is_readable($file)) {
            self::sendSecurityHeaders();

            http_response_code(404);
            header('Content-Type: text/plain; charset=UTF-8', true);

            exit('Static file not found.');
        }

        self::removeServerDisclosure();

        if (!headers_sent()) {
            header_remove('X-Powered-By');

            header('X-Content-Type-Options: nosniff', true);
            header('Referrer-Policy: strict-origin-when-cross-origin', true);
            header('Cross-Origin-Resource-Policy: same-origin', true);
            header('Cache-Control: public, max-age=86400', true);
            header('Content-Type: ' . self::contentTypeForExtension($extension), true);
            header('Content-Length: ' . filesize($file), true);

            /*
             * Static assets do not need CSP, but ZAP sometimes requests robots.txt
             * and sitemap.xml as static files. Sending CSP here avoids false positives
             * for text/xml/txt static responses.
             */
            $nonce = self::cspNonce();

            header(
                "Content-Security-Policy: "
                . "default-src 'self'; "
                . "script-src 'self' 'nonce-$nonce'; "
                . "style-src 'self' 'nonce-$nonce'; "
                . "img-src 'self' data:; "
                . "font-src 'self' data:; "
                . "object-src 'none'; "
                . "base-uri 'self'; "
                . "form-action 'self'; "
                . "frame-ancestors 'none'; "
                . "connect-src 'self';",
                true
            );
        }

        readfile($file);
        exit;
    }

    private static function contentTypeForExtension(string $extension): string
    {
        return match (strtolower($extension)) {
            'css' => 'text/css; charset=UTF-8',
            'js', 'mjs' => 'application/javascript; charset=UTF-8',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'map' => 'application/json; charset=UTF-8',
            'txt' => 'text/plain; charset=UTF-8',
            'xml' => 'application/xml; charset=UTF-8',
            default => 'application/octet-stream',
        };
    }

    public static function csrfToken(): string
    {
        self::startSecureSession();

        if (empty($_SESSION[self::CSRF_SESSION_KEY])) {
            $_SESSION[self::CSRF_SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CSRF_SESSION_KEY];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::e(self::csrfToken()) . '">';
    }

    public static function validateCsrfFromPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $postedToken = $_POST[self::CSRF_SESSION_KEY] ?? '';
        $sessionToken = $_SESSION[self::CSRF_SESSION_KEY] ?? '';

        if (
            empty($postedToken)
            || empty($sessionToken)
            || !hash_equals($sessionToken, $postedToken)
        ) {
            self::sendSecurityHeaders();

            http_response_code(403);
            header('Content-Type: text/plain; charset=UTF-8', true);

            exit('Invalid CSRF token.');
        }
    }

    public static function injectCsrfIntoForms(string $html): string
    {
        $tokenField = self::csrfField();

        return preg_replace_callback(
            '/<form\b([^>]*\bmethod\s*=\s*["\']?post["\']?[^>]*)>/i',
            static function (array $matches) use ($tokenField): string {
                $formTag = $matches[0];

                if (stripos($formTag, 'csrf_token') !== false) {
                    return $formTag;
                }

                return $formTag . "\n" . $tokenField;
            },
            $html
        ) ?? $html;
    }

    public static function stripHtmlComments(string $html): string
    {
        return preg_replace('/<!--(?!\[if).*?-->/s', '', $html) ?? $html;
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';

        if (strtolower((string) $forwardedProto) === 'https') {
            return true;
        }

        $forwardedSsl = $_SERVER['HTTP_X_FORWARDED_SSL'] ?? '';

        return strtolower((string) $forwardedSsl) === 'on';
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return Security::e($value);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Security::csrfField();
    }
}