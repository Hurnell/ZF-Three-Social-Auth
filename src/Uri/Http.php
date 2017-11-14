<?php

namespace ZfThreeSocialAuth\Uri;

use Zend\Uri\Http as ZendHttp;


class Http extends ZendHttp
{

    public function __toString()
    {
        try {
            return $this->toString();
        } catch (\Exception $e) {
            return '';
        }
    }

    public function toString()
    {
        if (!$this->isValid()) {
            if ($this->isAbsolute() || !$this->isValidRelative()) {
                throw new Exception\InvalidUriException(
                'URI is not valid and cannot be converted into a string'
                );
            }
        }

        $uri = '';

        if ($this->scheme) {
            $uri .= $this->scheme . ':';
        }

        if ($this->host !== null) {
            $uri .= '//';
            if ($this->userInfo) {
                $uri .= $this->userInfo . '@';
            }
            $uri .= $this->host;
            if ($this->port) {
                $uri .= ':' . $this->port;
            }
        }

        if ($this->path) {
            $uri .= $this->path;
        } elseif ($this->host && ($this->query || $this->fragment)) {
            $uri .= '/';
        }

        if ($this->query) {
            $uri .= "?" . static::encodeQueryFragment($this->query);
        }

        if ($this->fragment) {
            $uri .= "#" . static::encodeQueryFragment($this->fragment);
        }

        return $uri;
    }

}
