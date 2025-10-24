<?php

namespace App\Helpers;

class IdEncoder
{
    // Hàm mã hóa ID
    public static function encodeId($id) {
        // Mã hóa các chữ số
        $encodingMap = [
            '0' => 'bjasvdj',
            '1' => 'jhhgjav',
            '2' => 'ahjsdjba',
            '3' => 'jhgasdhv',
            '4' => 'basgdh',
            '5' => 'ahsdbha',
            '6' => 'hasdhbka',
            '7' => 'hajvsdhgj',
            '8' => 'amsbdhj',
            '9' => 'jhasvdhkja'
        ];
    
        // Mã hóa ID
        $encodedId = '';
        foreach (str_split($id) as $digit) {
            if (isset($encodingMap[$digit])) {
                // Mã hóa chữ số và thêm chuỗi ngẫu nhiên
                $encodedId .= $encodingMap[$digit] . self::generateRandomString(15); // Thêm chuỗi ngẫu nhiên dài 15 ký tự
            }
        }
    
        return $encodedId;
    }
    

    // Hàm giải mã ID
    public static function decodeId($encodedId)
    {
        // Giải mã mã hoá để có thể truy cập id
        $decodingMap = [
            'bjasvdj' => '0',
            'jhhgjav' => '1',
            'ahjsdjba' => '2',
            'jhgasdhv' => '3',
            'basgdh' => '4',
            'ahsdbha' => '5',
            'hasdhbka' => '6',
            'hajvsdhgj' => '7',
            'amsbdhj' => '8',
            'jhasvdhkja' => '9'
        ];
    
        $decodedId = '';
        $originalEncodedId = $encodedId; // Lưu trữ ID ban đầu để kiểm tra sau
    
        // Giải mã ID
        while ($encodedId) {
            $found = false;
            foreach ($decodingMap as $key => $digit) {
                if (strpos($encodedId, $key) === 0) {
                    $decodedId .= $digit;
                    $encodedId = substr($encodedId, strlen($key));
    
                    // Loại bỏ chuỗi ngẫu nhiên (15 ký tự) sau mỗi phần mã hóa
                    $encodedId = substr($encodedId, 15);
                    $found = true;
                    break;
                }
            }
    
            if (!$found) {
                break;
            }
        }
    
        // Kiểm tra nếu phần dư thừa có sau ID hợp lệ
        if (strlen($encodedId) > 0) {
            // Nếu có phần dư thừa sau khi giải mã, trả về null hoặc thông báo lỗi
            return null;
        }
    
        // Trả về ID giải mã hợp lệ
        return $decodedId;
    }
    

    // Hàm tạo chuỗi ngẫu nhiên
    private static function generateRandomString($length = 15) {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}
