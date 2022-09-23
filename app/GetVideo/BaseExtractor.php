<?php

namespace App\GetVideo;

abstract class BaseExtractor
{
    const _default_output_tmpl = '%(title)s-%(id)s.%(ext)s';

    protected string $_valid_url;
    protected array $_formats;
    public readonly int $id;
    protected string $url;
    public readonly string $raw;
    public readonly array $parsed;

    protected $ytdl_args = [
        '--no-warnings',
        '--skip-download',
        '--print-json',
        '--restrict-filenames',
        '--no-playlist',
    ];

    protected $ytdl_args_w = [
        '--max-downloads' => 1,
        '--output' => self::_default_output_tmpl,
    ];

    abstract public static function is_valid(string $url): bool;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function get_url(): string
    {
        return $this->url;
    }

    public function get_identifier(): string
    {
        return sha1($this->url);
    }

    public function real_extract(): array
    {
        $info = $this->_get_ytdl($this->url);
        return $this->_process_formats($info);
    }

    protected function _match_id(string $u): int | string | bool
    {
        preg_match($this->_valid_url, $u, $m);
        if ($m['id']) {
            return $m['id'];
        } else if ($m['videoid']) {
            return $m['videoid'];
        }

        return false;
    }

    protected function _match_regex(string $u): int | string | bool
    {
        preg_match($this->_valid_url, $u, $m);
        if (count($m)) {
            return $m;
        }

        return false;
    }

    protected function _get_ytdl(): array
    {
        $query = '/usr/local/bin/python3.8 /usr/local/bin/yt-dlp ' . $this->_get_args() . ' ' . $this->_get_args_w() . ' ' . escapeshellarg($this->url) . ' 2>&1';

        $this->raw = shell_exec($query);
        $this->parsed = $this->_parse_json($this->raw);

        $this->_check_errors();

        return $this->parsed;
    }

    protected function _check_errors(): void
    {
        $error_patterns = [
            '/(This video contains content from.*, who has blocked it on copyright grounds)/i',
            '/(Video.*is only available for registered users)/i',
            '/(no longer available due to a copyright claim by a third party)/i',
            '/(This video is unavailable)/i',
            '/(The author of this video has not been found or is blocked)/i',
            '/(This video is not available in your region)/i',
            '/(This video is not available from your location due to geo restriction)/i',
            '/(Executing JS failed)/i',
            '/(Unsupported URL)/i',
            '/(Unable to extract)/i'
        ];
        foreach ($error_patterns as $pattern) {
            preg_match($pattern, $this->raw, $match);
            if (isset($match[1])) {
                throw new ExtractionFailedException(2004, ucfirst($match[1]));
            }
        }

        if (preg_match('/(Name or service not known|Unsupported URL|Unable to download webpage)/i', $this->raw)) {
            throw new ExtractionFailedException(2002);
        } else if (preg_match('/([Ff][Ii][Ll][Ee]\s*[Nn][Oo][Tt]\s*[Ff][Oo][Uu][Nn][Dd]|Incomplete YouTube ID)/i', $this->raw)) {
            throw new ExtractionFailedException(2003);
        } else if (preg_match('/ERROR:/', $this->raw)) {
            throw new ExtractionFailedException(2001);
        }

        if ($this->parsed) {

            // check for adult content
            $ytdl = $this->parsed;
            if (
                (isset($ytdl['age_limit']) && $ytdl['age_limit'] >= 18) ||
                preg_match('/(\+18|18\+|porn|xxx|sex|tits)/i', $ytdl['title'] . $ytdl['uploader'])
            ) {
                throw new ExtractionFailedException(2101);
            }
        }
    }

    public function get_raw(): string
    {
        return $this->raw;
    }

    protected function _fallback_process_formats()
    {
        if ($this->ytdl_parsed['extractor_key'] == str_replace('IE', '', end(explode('\\', get_class($this))))) {
            return false;
        } else {
            return array(
                '_type' => 'do_process',
                'ie_key' => $this->ytdl_parsed['extractor_key'],
                'json' => $this->ytdl_parsed,
            );
        }
    }

    protected function login($u, $p): void
    {
        $this->_add_arg('--username', $u);
        $this->_add_arg('--password', $p);
    }

    protected function _parse_json($s): array
    {
        return json_decode($s, true);
    }

    protected function str_to_bool($s): bool
    {
        if ($s == 'none') {
            return false;
        } else if ($s == '0') {
            return false;
        } else if ($s == 'false') {
            return false;
        } else if ($s == 'true') {
            return true;
        } else if ($s == '1') {
            return true;
        } else if ($s !== false || $s !== null) {
            return true;
        }
    }

    protected function determine_ext($text, $default = 'unknown'): string
    {
        $e = explode('.', $text);
        $last = end($e);
        preg_match('/^([a-zA-Z0-9]+)$/i', $last, $find);
        if (isset($find[1])) {
            return $find[1];
        }

        return $default;
    }

    public static function _parse_tmpl($tmpl, $fi): string
    {
        foreach ($fi as $fk => $fv) {
            $tmpl = str_replace('%(' . $fk . ')s', $fv, $tmpl);
        }
        return $tmpl;
    }

    protected function _download_webpage($url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    protected function _download_json($url): array
    {
        $json = $this->_download_webpage($url);
        return $this->_parse_json($json);
    }

    protected function _process_formats($info)
    {
        if ($info['_type']) {
            return $info;
        }

        $formats = array_keys($this->_formats);
        $fmts = [];

        foreach (array_reverse($info['formats']) as $fmt) {
            if (in_array($fmt['format_id'], $formats)) {
                if (!preg_match("/[a-zA-Z0-9\.\&\$\#\!\%\(\)\[\]\?\*-_\+\'\']+/i", $info['title'])) {
                    $name = $info['_filename'];
                } else {
                    $name = $info['title'] . '.' . $fmt['ext'];
                }

                array_push($fmts, [
                    'label' => $this->_formats[$fmt['format_id']] . ' - ' . strtoupper($fmt['ext']),
                    'name' => $name,
                    'ext' => $fmt['ext'] ?? $this->determine_ext($name),
                    'url' => $fmt['url'],
                ]);
            }
        }

        return array(
            'webpage_url' => $info['webpage_url'],
            'title' => $info['title'],
            'thumbnail_url' => $info['thumbnail'],
            'formats' => $fmts,
        );
    }

    public function _add_arg($key, $val = false): void
    {
        if (!$val) {
            $this->ytdl_args[] = $key;
            $this->ytdl_args = array_unique($this->ytdl_args);
        } else {
            $this->ytdl_args_w[$key] = escapeshellarg($val);
        }
    }

    protected function _get_args(): string
    {
        $args = '';
        foreach ($this->ytdl_args as $arg) {
            $args .= $arg . ' ';
        }

        return trim($args);
    }

    protected function _get_args_w(): string
    {
        $args_w = '';
        foreach ($this->ytdl_args_w as $arg_k => $arg_v) {
            $args_w .= $arg_k . " '" . $arg_v . "' ";
        }

        return trim($args_w);
    }
}
