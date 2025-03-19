<?php

namespace Web3DecodePhp;

use kornrunner\Keccak;
use Web3\Contracts\Ethabi;

class DecodeLog {
    /**
     * ABI definition of the event
     * @var array
     */
    private $contractAbi;

    private $ethabi;

    /**
     * Constructor to initialize the EthereumLogDecoder
     * @param array $eventAbi
     */
    public function __construct(array $contractAbi) {
        $this->contractAbi = $contractAbi;
        $this->ethabi = new Ethabi();
    }

    /** 
     * Calculate the event signature
     * @param array $eventAbi The event ABI
     * @return string The event signature (keccak256 hash)
     */
    private function calculateEventSignature(array $eventAbi): string {
        // Get event name
        $name = $eventAbi['name'];

        // Get parameter types
        $types = [];
        foreach ($eventAbi['inputs'] as $input) {
            $types[] = $input['type'];
        }

        $canonicalSignature = $name . '(' . implode(',', $types) . ')';

        $hash = '0x' . substr(Keccak::hash($canonicalSignature, 256), 0, 64);

        return $hash;
    }

    public function decode($log): array {
        // TODO: Add support for decoding multiple logs
        $result = [];
        
        foreach ($this->contractAbi as $item) {
            if ($item['type'] === 'event' && isset($item['name'])) {
                $eventSignature = $this->calculateEventSignature($item);

                // Verify this is the correct event by checking the signature
                if(isset($log->topics[0]) && $log->topics[0] === $eventSignature) {                
                    $indexedParamCount = 0;

                    $data = substr($log->data, 2); // Remove '0x' prefix
                    $nonIndexedParams = [];

                    // Separate indexed and non-indexed parameters
                    foreach ($item['inputs'] as $index => $input) {
                        if (!empty($input['indexed'])) {
                            // This is an indexed parameter, get it from topics
                            $topicIndex = $indexedParamCount + 1; // +1 because topics[0] is the event signature
                                            
                            if (isset($log->topics[$topicIndex])) {
                                if ($input['type'] === 'uint256') {
                                    $result[$input['name']] = $this->decodeUint256($log->topics[$topicIndex]);
                                } else {
                                    $result[$input['name']] = $this->ethabi->decodeParameter(
                                        $input['type'],
                                        $log->topics[$topicIndex]
                                    );
                                }
                            }

                            $indexedParamCount++;
                        } else {
                            // This is a non-indexed parameter, will be decoded from data
                            $nonIndexedParams[] = $input;
                        }
                    }

                    // Decode non-indexed parameters from data
                    if (!empty($data) && !empty($nonIndexedParams)) {
                        $decodedData = $this->decodeData($data, $nonIndexedParams);

                        foreach ($decodedData as $index => $value) {
                            $paramName = $nonIndexedParams[$index]['name'];
                            $result[$paramName] = $value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Decode the data part of the log
     * @param string $data The hex data string (without 0x prefix)
     * @param array $params Array of parameter definitions
     * @return array Array of decoded values
     */
    private function decodeData($data, $params) {
        $result = [];
        $offset = 0;

        foreach ($params as $param) {
            $length = 64;
            $paramData = substr($data, $offset, $length);

            $decodedValue = $this->ethabi->decodeParameter($param['type'], '0x' . $paramData);
            $result[] = $decodedValue;

            $offset += $length;
        }

        return $result;
    }

    private function decodeUint256($hexValue) {
        $hexValue = substr($hexValue, 0, 2) === '0x' ?substr($hexValue, 2) : $hexValue;

        if ($hexValue === '0000000000000000000000000000000000000000000000000000000000000000') {
            return 0;
        }

        $hexValue = ltrim($hexValue, '0');

        if (strlen($hexValue) <= 14) {
            return hexdec($hexValue);
        }

        $dec = '0';
        $len = strlen($hexValue);

        for ($i = 0; $i < $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hexValue[$i])), bcpow('16', strval($len - $i - 1))));
        }

        return $dec;
    }
}
