<?php

namespace ExpoSDK\Drivers;

use ExpoSDK\File;

class FileDriver extends Driver
{
    /**
     * The path to the file
     *
     * @var string
     */
    private $path = __DIR__ . '/../storage/expo.json';

    /**
     * The storage file object
     *
     * @var File
     */
    private $file;

    public function __construct(array $config)
    {
        $this->build($config);
    }

    /**
     * Builds the driver instance
     */
    protected function build(array $config): void
    {
        $path = $config['path'] ?? $this->path;

        $this->file = new File($path);
    }

    /**
     * Stores tokens for a channel
     */
    public function store(string $channel, array $tokens): bool
    {
        $store = $this->file->read();
        $subs = $store->{$channel} ?? null;

        $subs = $subs ? array_values(array_merge($subs, $tokens)) : $tokens;
        $subs = array_unique($subs);
        $store->{$channel} = array_values($subs);

        return $this->file->write($store);
    }

    /**
     * Retrieves a channels subscriptions
     *
     * @return array|null
     */
    public function retrieve(string $channel)
    {
        $store = $this->file->read();

        return $store->{$channel} ?? null;
    }

    /**
     * Removes subscriptions from a channel
     */
    public function forget(string $channel, $tokens = null): bool
    {
        $store = $this->file->read();
        $subs = $store->{$channel} ?? null;

        if (is_null($subs)) {
            return true;
        }

        if(is_null($tokens)){
            unset($store->{$channel});
        }else{
            $tokens = array_unique($tokens);
            $subs = array_filter($subs, function ($token) use ($tokens) {
                return ! in_array($token, $tokens);
            });
    
            // delete channel if there are no more subscriptions
            if (count($subs) === 0) {
                unset($store->{$channel});
            } else {
                $store->{$channel} = array_values($subs);
            }
        }


        return $this->file->write($store);
    }

    public function forgetAll(): bool
    {
        return $this->file->write((object)[]);
    }
}
