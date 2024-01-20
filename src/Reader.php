<?php

declare(strict_types=1);

namespace ClitorIsProtocol\Kevacoin;

class Reader
{
    private int    $_protocol = 1;

    private array  $_errors   = [];
    private array  $_meta     = [];

    public function __construct(string $value)
    {
        if (!$meta = @json_decode($value, true))
        {
            $this->_errors[] = _('[_CLITOR_IS_] decode error');
        }

        if // version valid
        (!
            (
                isset($meta['version']) && preg_match(sprintf('/^%s\./', $this->_protocol), $meta['version'])
            )
        ) $this->_errors[] = _('[_CLITOR_IS_] version not compatible');

        if // model valid
        (!
            (
                isset($meta['model'])                         && is_array($meta['model'])                          &&
                isset($meta['model']['name'])                 && strtolower($meta['model']['name']) === 'kevacoin' &&
                isset($meta['model']['software'])             && is_array($meta['model']['software'])              &&
                isset($meta['model']['software']['version'])  && is_int($meta['model']['software']['version'])     &&
                isset($meta['model']['software']['protocol']) && is_int($meta['model']['software']['protocol'])
            )
        ) $this->_errors[] = _('[_CLITOR_IS_] model not compatible');

        // pieces valid
        if
        (!
            (
                isset($meta['pieces'])          && is_array($meta['pieces'])        &&
                isset($meta['pieces']['total']) && is_int($meta['pieces']['total']) && $meta['pieces']['total'] > 0 &&
                isset($meta['pieces']['size'])  && is_int($meta['pieces']['size'])  && $meta['pieces']['size'] >= 1
                                                                                    && $meta['pieces']['size'] <= 3072
            )
        ) $this->_errors[] = _('[_CLITOR_IS_] pieces not compatible');

        if // file valid
        (!
            (
                isset($meta['file'])         && is_array($meta['file'])          &&
                isset($meta['file']['name']) && is_string($meta['file']['name']) &&
                isset($meta['file']['mime']) && is_string($meta['file']['mime']) &&
                isset($meta['file']['size']) && is_int($meta['file']['size'])    &&
                isset($meta['file']['md5'])  && is_string($meta['file']['md5'])
            )
        ) $this->_errors[] = _('[_CLITOR_IS_] file not compatible');

        if (!$this->_errors) $this->_meta = $meta;
    }

    // Decode pieces data (by keva_get namespace response)
    public function data(array $pieces, bool $decode = true): ?string
    {
        $chain = [];

        foreach ($pieces as $piece)
        {
            // validate piece
            if (!isset($piece['key']) || !isset($piece['value']) || !isset($piece['height']))
            {
                $this->_errors[] = _('invalid piece format');

                // free mem
                $chain  = [];
                $pieces = [];

                return null;
            }

            // skip meta keys
            if (false !== stripos($piece['key'], '_'))
            {
                continue;
            }

            $chain[$piece['key']][$piece['height']] = $piece['value'];
        }

        $pieces = [];

        foreach ($chain as $key => $height)
        {
            ksort(
                $height
            );

            $pieces[$key] = $height[array_key_last($height)];
        }

        ksort(
            $pieces
        );

        $data = implode('', $pieces);

        if ($decode)
        {
            if (!$data = @base64_decode($data))
            {
                $this->_errors[] = _('could not decode content data');

                // free mem
                $chain  = [];
                $pieces = [];

                return null;
            }
        }

        return $data;
    }

    // Get File MIME
    public function fileMime(): ?string
    {
        return !empty($this->_meta['file']['mime']) ? $this->_meta['file']['mime'] : null;
    }

    // Get File Size
    public function fileSize(): ?int
    {
        return !empty($this->_meta['file']['size']) ? $this->_meta['file']['size'] : null;
    }

    // Get File Name
    public function fileName(): ?string
    {
        return !empty($this->_meta['file']['name']) ? $this->_meta['file']['name'] : null;
    }

    // Validate _CLITOR_IS_
    public function valid(): bool
    {
        return !$this->_errors;
    }

    // Dump tool
    public function errors(): array
    {
        return $this->_errors;
    }
}