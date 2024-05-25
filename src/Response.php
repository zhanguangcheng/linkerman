<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace Linkerman;

class Response extends \Workerman\Protocols\Http\Response
{
    public function withCookieString(string $cookie): self
    {
        $this->_header['Set-Cookie'][] = $cookie;
        return $this;
    }

    public function withoutHeader($name = null): self
    {
        if ($name === null) {
            $this->_header = [];
            return $this;
        }
        unset($this->_header[$name]);
        return $this;
    }

    public function withCookie(
        string    $name,
        string    $value = "",
        int|array $expires_or_options = 0,
        string    $path = "",
        string    $domain = "",
        bool      $secure = false,
        bool      $httponly = false,
        string    $samesite = '',
                  $raw = false
    ): self
    {
        if (\is_array($expires_or_options)) {
            $maxAge = $expires_or_options['expires'] ?? 0;
            $path = $expires_or_options['path'] ?? "";
            $domain = $expires_or_options['domain'] ?? "";
            $secure = $expires_or_options['secure'] ?? false;
            $httponly = $expires_or_options['httponly'] ?? false;
            $samesite = $expires_or_options['samesite'] ?? '';
            if (!\in_array($samesite, ['None', 'Lax', 'Strict'])) {
                $samesite = '';
            }
        } else {
            $maxAge = $expires_or_options;
        }
        if ($maxAge === null || $maxAge === false || $maxAge === 0) {
            $maxAge = 0;
        } else {
            $maxAge -= time();
        }
        $this->_header['Set-Cookie'][] = $name . '=' . ($raw ? $value : \rawurlencode($value))
            . (empty($domain) ? '' : '; Domain=' . $domain)
            . ($maxAge === 0 ? '' : '; Max-Age=' . $maxAge)
            . (empty($path) ? '' : '; Path=' . $path)
            . (!$secure ? '' : '; Secure')
            . (!$httponly ? '' : '; HttpOnly')
            . (empty($samesite) ? '' : '; SameSite=' . $samesite);
        return $this;
    }
}