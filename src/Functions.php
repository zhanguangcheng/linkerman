<?php

/**
 * This file is part of linkerman.
 *
 * @author  zhanguangcheng<14712905@qq.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @noinspection PhpRedeclarationStdlibFunctionInspection
 * @noinspection PhpUnusedParameterInspection
 * @noinspection PhpUnused
 */

use Linkerman\ExitException;
use Linkerman\Http;
use Linkerman\Request;
use Workerman\Protocols\Http\Session;

/**
 * Register a function for execution on shutdown
 * Since the resident memory runs, the registered callback function is not actually executed, which may lead to memory leaks
 *
 * @param callable $callback
 * @param mixed ...$args
 * @return void
 */
function register_shutdown_function_user(callable $callback, mixed ...$args): void
{
    if (!(\is_array($callback) && $callback[0] === '\\Workerman\\Worker')) {
        // Prevent memory leaks caused by callback functions that are not executed normally
        if (\count(Http::$shutdownCallbacks) < 100) {
            Http::$shutdownCallbacks[] = [$callback, $args];
        }
    }
}

/**
 * Call the registered shutdown functions on the end of the request
 *
 * @return void
 */
function call_shutdown_function(): void
{
    while (true) {
        $call = \array_shift(Http::$shutdownCallbacks);
        if (!$call) {
            break;
        }
        try {
            \call_user_func_array($call[0], $call[1] ?? []);
        } catch (\Exception|\Throwable) {
        }
    }
}

/**
 * Set the maximum execution time
 *
 * @param int $seconds
 * @return bool
 */
function set_time_limit(int $seconds): bool
{
    return true;
}

/**
 * Send a raw HTTP header
 *
 * @param string $string
 * @param bool $replace
 * @param int|null $response_code
 * @return void
 */
function header(string $string, bool $replace = true, ?int $response_code = null): void
{
    $response = Http::$response;
    if (\str_starts_with($string, 'HTTP/')) {
        [$protocol, $code, $reason] = \explode(' ', $string, 3);
        $response->withProtocolVersion(\substr($protocol, 5));
        $response->withStatus((int)$code, $reason);
        return;
    }

    if (!str_contains($string, ':')) {
        return;
    }
    if ($response_code !== null) {
        $response->withStatus($response_code);
    }
    [$key, $value] = explode(':', $string, 2);
    $value = ltrim($value);
    if (\strtolower($key) === 'set-cookie') {
        $response->withCookieString($value);
        return;
    }
    if (\strtolower($key) === 'location') {
        $response_code ??= 302;
        $response->withStatus($response_code);
    }
    $response->header($key, $value);
}

/**
 * @param string|null $name
 * @return void
 */
function header_remove(?string $name = null): void
{
    Http::$response->withoutHeader($name);
}

/**
 * @param string|null $filename
 * @param int|null $line
 * @return bool
 */
function headers_sent(string &$filename = null, int &$line = null): bool
{
    return false;
}

/**
 * Get the list of headers as a numeric array
 *
 * @return array
 */
function headers_list(): array
{
    $headers = Http::$response->getHeaders();
    $result = [];
    foreach ($headers as $key => $value) {
        $result[] = "$key: " . (is_array($value) ? implode('; ', $value) : $value);
    }
    return $result;
}

/**
 * Get or set the HTTP response code
 *
 * @param int|null $code
 * @return int|bool
 */
function http_response_code(?int $code = null): int|bool
{
    $response = Http::$response;
    if ($code === null) {
        return $response->getStatusCode();
    }
    $response->withStatus($code);
    return true;
}

if (!function_exists('getallheaders')) {
    function getallheaders(): array
    {
        return Http::$request->header();
    }
}

/**
 * Set a cookie
 *
 * @param string $name
 * @param string $value
 * @param int|array $expires_or_options
 * @param string $path
 * @param string $domain
 * @param bool $secure
 * @param bool $httponly
 * @param string $samesite
 * @return bool
 */
function setcookie(
    string    $name,
    string    $value = "",
    int|array $expires_or_options = 0,
    string    $path = "",
    string    $domain = "",
    bool      $secure = false,
    bool      $httponly = false,
    string    $samesite = ''
): bool
{
    Http::$response->withCookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly, $samesite);
    return true;
}

/**
 * Set a raw cookie
 *
 * @param string $name
 * @param string $value
 * @param int|array $expires_or_options
 * @param string $path
 * @param string $domain
 * @param bool $secure
 * @param bool $httponly
 * @param string $samesite
 * @return bool
 */
function setrawcookie(
    string    $name,
    string    $value = "",
    int|array $expires_or_options = 0,
    string    $path = "",
    string    $domain = "",
    bool      $secure = false,
    bool      $httponly = false,
    string    $samesite = ''
): bool
{
    Http::$response->withCookie($name, $value, $expires_or_options, $path, $domain, $secure, $httponly, $samesite, true);
    return true;
}

/**
 * Get and/or set the current session id
 *
 * @param string|null $id
 * @return string|false
 */
function session_id(?string $id = null): string|false
{
    $session = Http::$request->session;
    if ($id === null) {
        return $session ? $session->getId() : "";
    }
    if ($session && Http::$sessionIsStarted) {
        // Return the current session ID when setting the session ID again after the session has been started
        return $session->getId();
    }
    Http::$request->session($id);
    return "";
}

/**
 * Get and/or set the current session name
 *
 * @param string|null $name
 * @return string|false
 */
function session_name(?string $name = null): string|false
{
    if ($name === null) {
        return Session::$name;
    }
    if (!Http::$sessionIsStarted && !ctype_digit($name) && ctype_alnum($name)) {
        $oldName = Session::$name;
        Session::$name = $name;
        return $oldName;
    }
    return false;
}

/**
 * Get and/or set the current session save path
 *
 * @param string|null $path
 * @return string|false
 */
function session_save_path(?string $path = null): string|false
{
    if ($path === null) {
        if (Http::$sessionSavePath === null) {
            /**
             * Possible values of $savePath: /tmp | 2;/tmp | 2;0777;/tmp
             * @see https://www.php.net/manual/zh/session.configuration.php#ini.session.save-path
             */
            $savePath = \ini_get('session.save_path');
            if ($savePath && \preg_match('/;?([-:\/\\\w]+?)$/', $savePath, $match)) {
                $savePath = $match[1];
            } else {
                $savePath = \sys_get_temp_dir();
            }
            Http::$sessionSavePath = $savePath;
        }
        return Http::$sessionSavePath;
    }
    if (Http::$sessionIsStarted) {
        return false;
    }
    Http::$sessionSavePath = $path;
    Session\FileSessionHandler::sessionSavePath($path);
    return Http::$sessionSavePath;
}

/**
 * Get the current session status
 *
 * @return int
 */
function session_status(): int
{
    $session = Http::$request->session;
    if ($session && Http::$sessionIsStarted) {
        return $session->getId() ? \PHP_SESSION_ACTIVE : \PHP_SESSION_NONE;
    }
    return \PHP_SESSION_DISABLED;
}

/**
 * Start a new session
 *
 * @param array $options
 * @return bool
 */
function session_start(array $options = []): bool
{
    if (Http::$sessionIsStarted) {
        return true;
    }
    $session = Http::$request->session();
    if (!$session) {
        return false;
    }
    Http::$sessionIsStarted = true;
    $_SESSION = $session->all();
    return true;
}

/**
 * Write session data and end the session
 *
 * @return bool
 */
function session_write_close(): bool
{
    if (!Http::$sessionIsStarted) {
        return false;
    }
    $session = Http::$request->session();
    if (!$session) {
        return false;
    }
    if ($session->all() !== $_SESSION) {
        $session->setData($_SESSION);
        $session->save();
    }
    Http::$sessionIsStarted = false;
    return true;
}

/**
 * Regenerate the session ID
 *
 * @param bool $delete_old_session
 * @return bool
 */
function session_regenerate_id(bool $delete_old_session = false): bool
{
    $request = Http::$request;
    if ($delete_old_session && $request->session) {
        $request->session->flush();
        $request->session->save();
    }
    $id = Request::generateSessionId();
    $request->sid = null;
    if (!$request->sessionId($id)) {
        return false;
    }
    $request->session?->setId($id);
    return true;
}

/**
 * Unset all session variables
 *
 * @return bool
 */
function session_unset(): bool
{
    if (\session_status() === \PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        return true;
    }
    return false;
}

/**
 * Destroy session
 *
 * @return bool
 */
function session_destroy(): bool
{
    if (\session_status() === \PHP_SESSION_ACTIVE) {
        $request = Http::$request;
        $request->session->flush();
        $request->session->save();
        Http::$sessionIsStarted = false;
        return true;
    }
    return false;
}

/**
 * Check the file was uploaded via HTTP POST
 *
 * @param string $filename
 * @return bool
 */
function is_uploaded_file(string $filename): bool
{
    $files = Http::$request->file();
    foreach ($files as $file) {
        if (isset($file['error']) && $file['error'] === 0
            && isset($file['tmp_name']) && $file['tmp_name'] === $filename
            && \is_file($file['tmp_name'])
        ) {
            return true;
        }
    }
    return false;
}

/**
 * Moves an uploaded file to a new location
 *
 * @param string $from
 * @param string $to
 * @return bool
 */
function move_uploaded_file(string $from, string $to): bool
{
    if (!\is_uploaded_file($from)) {
        return false;
    }
    $path = \pathinfo($to, PATHINFO_DIRNAME);
    if (!\is_writeable($path)) {
        return false;
    }
    return \rename($from, $to);
}

/**
 * Replace the exit() call with exit_exception() to avoid program exit
 * If status is a string, function exit() will print the status just before exiting.
 *
 * @param int|string $status
 * @throws ExitException
 */
function exit_exception(int|string $status = 0): void
{
    if (\is_int($status)) {
        throw new ExitException('', $status);
    } else {
        throw new ExitException($status, 0);
    }
}

/**
 * Replace file_get_contents('php://input') Get the original request body
 *
 * @return string
 */
function request_raw_body(): string
{
    return Http::$request->rawBody();
}
