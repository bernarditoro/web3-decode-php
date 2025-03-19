# Web3 Decode PHP

A PHP library for decoding Ethereum logs and errors using contract ABIs. This library provides utilities to decode Ethereum (custom) error messages and event logs, making it easier to work with smart contract data.

## Features

- **Decode Ethereum Errors**: Decode error messages from Ethereum transactions using the contract ABI.
- **Decode Ethereum Logs**: Decode event logs emitted by smart contracts using the contract ABI.
- **Supports Indexed and Non-Indexed Parameters**: Handles both indexed and non-indexed parameters in event logs.

## Installation

You can install the library via Composer:

```bash
composer require bi/web3-decode-php
```

## Usage

### Decoding Ethereum Errors

To decode an Ethereum error, use the `decodeError` function:

```php
require 'vendor/autoload.php';

use Web3DecodePhp\DecodeError;

$errorSelector = '0x12345678'; // Example error selector
$abi = [
    [
        'type' => 'error',
        'name' => 'MyError',
        'inputs' => [
            ['type' => 'uint256'],
            ['type' => 'address']
        ]
    ]
];

$decodedError = DecodeError::decode($errorSelector, $abi);

echo $decodedError;
```

### Decoding Ethereum Logs

To decode an Ethereum log, use the `DecodeLog` class:

```php
require 'vendor/autoload.php';

use Web3DecodePhp\DecodeLog;

$eventAbi = [
    'name' => 'Transfer',
    'inputs' => [
        ['name' => 'from', 'type' => 'address', 'indexed' => true],
        ['name' => 'to', 'type' => 'address', 'indexed' => true],
        ['name' => 'value', 'type' => 'uint256', 'indexed' => false],
    ]
];

$log = (object) [
    "address" => "0x89d24a6b4ccb1b6faa2625fe562bdd9a23260359", // Contract address
    "topics" => [
        "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef", // Event signature
        "0x0000000000000000000000004bbeeb066ed09b7aed07bf39eee0460dfa261520", // from (indexed)
        "0x000000000000000000000000f977814e90da44bfa03b6295a0616a897441acec", // to (indexed)
    ],
    "data" => "0x0000000000000000000000000000000000000000000000000de0b6b3a7640000", // value (non-indexed)
    "blockHash" => "0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef",
    "blockNumber" => "0x123456",
    "blockTimestamp" => "0x6789abcd",
    "transactionHash" => "0xabcdef1234567890abcdef1234567890abcdef1234567890abcdef1234567890",
    "transactionIndex" => "0x0",
    "logIndex" => "0x1",
    "removed" => false
];

$decoder = new DecodeLog([$eventAbi]);
$decodedLog = $decoder->decode($log);

// Output the decoded log
echo json_encode($decodedLog, JSON_PRETTY_PRINT);
```

## API Reference

### `decodeError(string $errorSelector, array $abi): ?array`

Decodes an Ethereum error from its selector and ABI.

- **Parameters**:
  - `$errorSelector`: The error selector (hex string).
  - `$abi`: The contract ABI (including error definitions).
- **Returns**: An array containing the decoded error, or `null` if no match is found.

### `DecodeLog`

A class for decoding Ethereum event logs.

#### `__construct(array $contractAbi)`

Initializes the decoder with the contract ABI.

- **Parameters**:
  - `$contractAbi`: The contract ABI (including event definitions).

#### `decode(object $log): array`

Decodes an Ethereum log.

- **Parameters**:
  - `$log`: The log object to decode.
- **Returns**: An array containing the decoded log data.

## Contributing

Contributions are welcome! Please open an issue or submit a pull request on GitHub.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

