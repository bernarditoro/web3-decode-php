<?php

namespace Web3DecodePhp;

use Web3\Utils;

class DecodeError {
    /**
     * Decodes an error from its data and ABI
     * 
     * @param string $errorData The error data (hex string)
     * @param array $abi The contract ABI (including error definitions)
     * @return array|null The decoded error, or null if no match is found
     */
    public static function decode(string $errorSelector, array $abi): ?array {
        foreach ($abi as $item) {
            if ($item['type'] === 'error' && isset($item['name'])) {
                $signature = $item['name'] . '(' . implode(',', array_map(function ($input) {
                    return $input['type'];
                }, $item['inputs'])) . ')';

                $computedSelector = substr(Utils::sha3($signature), 0, 10);

                if ($computedSelector === $errorSelector) {
                    return [
                        'name' => $item['name'],
                    ];
                }
            }
        }

        return [];
    }
}
