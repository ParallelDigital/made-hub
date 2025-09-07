<?php

namespace App\Auth;

use Illuminate\Contracts\Hashing\Hasher;

class WordPressHasher implements Hasher
{
    /**
     * Hash the given value.
     */
    public function make($value, array $options = [])
    {
        // For new passwords, use Laravel's bcrypt
        return bcrypt($value);
    }

    /**
     * Check the given plain value against a hash.
     */
    public function check($value, $hashedValue, array $options = [])
    {
        // Check if it's a $wp$ prefixed bcrypt hash
        if (strpos($hashedValue, '$wp$') === 0) {
            return $this->checkWordPressBcrypt($value, $hashedValue);
        }

        // Check if it's a WordPress phpass hash
        if (strpos($hashedValue, '$P$') === 0) {
            return $this->checkWordPressPassword($value, $hashedValue);
        }

        // Otherwise use Laravel's default bcrypt check
        return password_verify($value, $hashedValue);
    }

    /**
     * Check if the given hash was hashed using the given options.
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        // WordPress hashes should be rehashed to Laravel format
        return strpos($hashedValue, '$wp$') === 0 || strpos($hashedValue, '$P$') === 0;
    }

    /**
     * Get information about the given hashed value.
     */
    public function info($hashedValue)
    {
        return [
            'algo' => $this->isWordPressHash($hashedValue) ? 'wordpress' : 'bcrypt',
            'algoName' => $this->isWordPressHash($hashedValue) ? 'WordPress phpass' : 'bcrypt',
            'options' => [],
        ];
    }

    /**
     * Check if the hash is a WordPress hash.
     */
    protected function isWordPressHash($hash)
    {
        return strpos($hash, '$P$') === 0 || strpos($hash, '$wp$') === 0;
    }

    /**
     * Check WordPress $wp$ prefixed bcrypt hash.
     */
    protected function checkWordPressBcrypt($password, $hash)
    {
        // Remove $wp$ prefix and verify with standard bcrypt
        $bcryptHash = substr($hash, 4); // Remove '$wp$'
        return password_verify($password, $bcryptHash);
    }

    /**
     * Check WordPress password using phpass algorithm.
     */
    protected function checkWordPressPassword($password, $hash)
    {
        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        
        if (strlen($hash) != 34) {
            return false;
        }

        $count_log2 = strpos($itoa64, $hash[3]);
        if ($count_log2 < 7 || $count_log2 > 30) {
            return false;
        }

        $count = 1 << $count_log2;
        $salt = substr($hash, 4, 8);
        
        if (strlen($salt) != 8) {
            return false;
        }

        $hash_check = md5($salt . $password);
        do {
            $hash_check = md5($hash_check . $password);
        } while (--$count);

        return substr($hash, 12) === substr($this->encode64($hash_check, 16), 0, 22);
    }

    /**
     * Encode hash for WordPress compatibility.
     */
    protected function encode64($input, $count)
    {
        $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $output = '';
        $i = 0;
        
        do {
            $value = ord($input[$i++]);
            $output .= $itoa64[$value & 0x3f];
            
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }
            
            $output .= $itoa64[($value >> 6) & 0x3f];
            
            if ($i++ >= $count) {
                break;
            }
            
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }
            
            $output .= $itoa64[($value >> 12) & 0x3f];
            
            if ($i++ >= $count) {
                break;
            }
            
            $output .= $itoa64[($value >> 18) & 0x3f];
        } while ($i < $count);

        return $output;
    }
}
