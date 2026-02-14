<?php

namespace Nesneek\Utils;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private static $secret_key;
    private static $algorithm = 'HS256';

    public static function init() {
        self::$secret_key = $_ENV['JWT_SECRET'] ?? 'supersecretkey_nesneek_2024';
    }

    public static function generateToken($payload) {
        self::init();
        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function validateToken() {
        self::init();
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $jwt = $matches[1];
            try {
                $decoded = JWT::decode($jwt, new Key(self::$secret_key, self::$algorithm));
                return (array) $decoded;
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
}
