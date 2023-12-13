# kevacoin-php

Composer library for PHP applications

## Install

`composer require clitor-is-protocol/kevacoin`

## Examples

To simply interact with Kevacoin API, use PHP library, e.g.

`composer require kevachat/kevacoin`

```

// Connect kevacoin
$client = new \Kevachat\Kevacoin\Client(
    $protocol,
    $host,
    $port,
    $username,
    $password
);

// Get meta data by namespace
if ($meta = $client->kevaGet($namespace, '_CLITOR_IS_'))
{
    // Init reader with meta data received
    $reader = new \ClitorIsProtocol\Kevacoin\Reader(
        $meta['value']
    );

    // Recommended to check the meta is valid
    if ($reader->valid())
    {
        // Grab namespace records from blockchain
        if ($pieces = $client->kevaFilter($namespace))
        {
          // Implement your app logic
          echo $reader->data($pieces);
        }
    }
}
```