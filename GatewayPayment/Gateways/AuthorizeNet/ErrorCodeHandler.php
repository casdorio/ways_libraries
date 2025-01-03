<?php

namespace App\Libraries\GatewayPayment\Gateways\AuthorizeNet;

use ReflectionClass;
use Exception;

class ErrorCodeHandler
{
    private static $responseCodes = [];

    public static function loadResponseCodes(): void
    {
        if (empty(self::$responseCodes)) {  // Carrega uma vez só
            $path = __DIR__ . '/responseCodes.json';
            if (file_exists($path)) {
                $json = file_get_contents($path);
                self::$responseCodes = json_decode($json, true);
            } else {
                throw new \Exception("Arquivo responseCodes.json não encontrado.");
            }
        }
    }

    public static function getErrorDescription(string $code): array
    {
        // Assegura que os códigos de resposta estão carregados
        self::loadResponseCodes();

        foreach (self::$responseCodes as $responseCode) {
            if ($responseCode['code'] === $code) {
                return $responseCode;
            }
        }

        return [
            'code' => $code,
            'text' => 'Unknown error',
            'description' => 'Error description not found',
            'integration_suggestions' => '',
            'other_suggestions' => ''
        ];
    }

    public static function handlePaymentResponse($result): array
    {
        if ($result !== null) {
            if ($result->getMessages()->getResultCode() === "Ok") {
                $transactionResponse = $result->getTransactionResponse();

                if ($transactionResponse !== null && $transactionResponse->getMessages() !== null) {
                    if (class_exists(get_class($transactionResponse))) {
                        $reflection = new ReflectionClass($transactionResponse);
                        $data = [];

                        foreach ($reflection->getMethods() as $method) {
                            if (strpos($method->getName(), 'get') === 0 && $method->isPublic()) {
                                $propertyName = lcfirst(substr($method->getName(), 3));
                                try {
                                    $data[$propertyName] = $method->invoke($transactionResponse);
                                } catch (Exception $e) {
                                    $data[$propertyName] = null;
                                }
                            }
                        }

                        return [
                            'status' => 'success',
                            'gatewayName' => 'Authorize.Net',
                            'message' => $transactionResponse->getMessages()[0]->getDescription() ?? 'Payment processed successfully.',
                            'transactionResponse' => $transactionResponse,
                            'data' => $data
                        ];
                    }
                } else {
                    $error = $transactionResponse->getErrors()[0];
                    $errorDetails = self::getErrorDescription($error->getErrorCode() ?? 'Unknown error');

                    return self::getErrorDetailsFromResponse($errorDetails, $error->getErrorText());
                }
            } else {
                $tresponse = $result->getTransactionResponse();

                if ($tresponse !== null && $tresponse->getErrors() !== null) {
                    $error = $tresponse->getErrors()[0];
                    $errorDetails = self::getErrorDescription($error->getErrorCode());
                    $errorText = $error->getErrorText();
                } else {
                    $error = $result->getMessages()->getMessage()[0];
                    $errorDetails = self::getErrorDescription($error->getCode());
                    $errorText = $error->getText();
                }

                return self::getErrorDetailsFromResponse($errorDetails, $errorText);
            }
        } else {
            return [
                'status' => 'error',
                'gatewayName' => 'Authorize.Net',
                'message' => 'No response returned',
            ];
        }
    }

    private static function getErrorDetailsFromResponse($errorDetails, $errorText): array
    {
        return [
            'status' => 'error',
            'code' => $errorDetails['code'],
            'message' => $errorDetails['text'],
            'gatewayName' => 'Authorize.Net',
            'tresponse' => preg_replace('/AnetApi\/xml\/v1\/schema\/AnetApiSchema\.xsd:/', '', $errorText),
            'integration_suggestions' => html_entity_decode(html_entity_decode($errorDetails['integration_suggestions'])),
            'other_suggestions' => html_entity_decode(html_entity_decode($errorDetails['other_suggestions']))
        ];
    }
}