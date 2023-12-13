<?php

declare(strict_types=1);

namespace ClitorIsProtocol\Kevacoin;

class Get
{
    private array $_errors = [];

    // Validate keva_get _CLITOR_IS_ value
    public function clitorIs(string $value): bool
    {
        if (!$meta = @json_decode($value))
        {
            $this->_errors[] = _('[_CLITOR_IS_] decode error');

            return false;
        }

        switch (false)
        {
            // version
            case isset($meta->version) && preg_match('/[\d]{2}\.[\d]{2}\.[\d]{2}/', $meta->version):

                $this->_errors[] = _('[_CLITOR_IS_] version not compatible');

            // model
            case isset($meta->model->name)               && $meta->model->name !== 'kevacoin'        &&
                 isset($meta->model->software)           && is_object($meta->model->software)        &&
                 isset($meta->model->software->version)  && is_int($meta->model->software->version)  &&
                 isset($meta->model->software->protocol) && is_int($meta->model->software->protocol) &&
                 isset($meta->model->namespace)          && preg_match('/^N[0-9A-z]{33}$/', $meta->model->namespace):

                $this->_errors[] = _('[_CLITOR_IS_] model not compatible');

            // pieces
            case isset($meta->pieces)        && is_object($meta->pieces)     &&
                 isset($meta->pieces->total) && is_int($meta->pieces->total) && $meta->pieces->total > 1 &&
                 isset($meta->pieces->size)  && is_int($meta->pieces->size)  && $meta->pieces->size >= 1
                                                                            && $meta->pieces->size <= 3072:

                $this->_errors[] = _('[_CLITOR_IS_] pieces not compatible');

            // file
            case isset($meta->file)       && is_object($meta->file)       &&
                 isset($meta->file->name) && is_string($meta->file->name) &&
                 isset($meta->file->mime) && is_string($meta->file->mime) &&
                 isset($meta->file->size) && is_int($meta->file->size)    &&
                 isset($meta->file->md5)  && is_string($meta->file->md5):

                $this->_errors[] = _('[_CLITOR_IS_] file not compatible');

            return false;
        }

        return true;
    }

    // Decode pieces data (by keva_get namespace response)
    public function decode(array $pieces): ?string
    {
        $chain = [];

        foreach ($pieces as $piece)
        {
            // validate piece
            if (!isset($piece->key) || !isset($piece->value) || !isset($piece->height))
            {
                $this->_errors[] = _('invalid piece format');

                // free mem
                $chain  = [];
                $pieces = [];

                return null;
            }

            // skip meta keys
            if (false !== stripos($piece->key, '_'))
            {
                continue;
            }

            $chain[$piece->key][$piece->height] = $piece->value;
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

        if (!$data = @base64_decode(implode('', $pieces)))
        {
            $this->_errors[] = _('could not decode content data');

            // free mem
            $chain  = [];
            $pieces = [];

            return null;
        }

        return $data;
    }

    // Dump tool
    public function errors(): array
    {
        return $this->_errors;
    }
}