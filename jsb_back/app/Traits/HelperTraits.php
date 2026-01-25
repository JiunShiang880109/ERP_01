<?php

namespace App\Traits;

use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use App\Http\Controllers\LayoutController;
use Illuminate\Support\Facades\Session;

trait HelperTraits
{
    public function print_image($file)
    {
        $connector = new NetworkPrintConnector("192.168.0.30", 9100);
        $printer = new Printer($connector);
        // $file = "E:/files/cloud/dalimart/image/invoice/invoice.png";
        $printer = new Printer($connector);
        $tux = EscposImage::load($file, false);
        $printer->bitImage($tux);
        $printer->feed();
        $printer->cut();
        $printer->close();
    }
    public function randomNumString($length)
    {$characters = 'ABCDEFGHIJKLMNPQUSTUVWXYZ1234567890';
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }
    public function randomString($length)
    {$characters = '1234567890';
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }
    public function randomAlphabet($length)
    {$characters = 'ABCDEFGHIJKLMNPQUSTUVWXYZ';
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }
    public function randomNum($length)
    {$characters = '0123456789';
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        $headnum = date('Y') . date('m');
        return $headnum . $string;
    }
    public function valid_EAN($code)
    {
        $check = new \clsLibGTIN($code);
        $res = $check->GTINCheck($code) ? 'correct' : 'incorrect';
        return $res;
    }
    public function talk($mess, $url, $showno)
    {
        $url = str_replace('http://','https://',$url);
        
        //$current = url('http://192.168.0.87/dalimarket/public');
        echo '<!doctype html><meta charset="utf-8">';
        switch ($showno) {
            case 1:
                echo "<script>alert('$mess');window.history.back();</script>";
                break;

            case 2:
                echo "<script>location.href='" . $url . "';</script>";
                break;

            case 3:
                echo "<script>alert('$mess');location.href='" . $url . "';</script>";
                break;

            case 4:
                echo "<script>alert('$mess');window.top.location.reload();</script>";
                break;

            case 5:
                echo "<script>alert('$mess');window.top.location.href='" . $url . "';</script>";
                break;

            case 6:
                echo "<script>alert('$mess')</script>";
                break;

            case 7:
                echo "<script>window.history.back();</script>";
                break;
        }
    }
    #
    public function delDirAndFile($dirName)
    {
        if ($handle = opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != "..") {
                    if (is_dir("$dirName/$item")) {
                        $this->delDirAndFile("$dirName/$item");
                    } else {
                        if (unlink("$dirName/$item")) {
                            echo "";
                        }

                    }
                }
            }
            closedir($handle);
            if (rmdir($dirName)) {
                echo "";
            }

        }
    }
    #產生隨機25碼 不重覆
    public function rand_my()
    {
        $card_array = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,A,B,C,E,S,Y,Z,W,4,5,9,7,5,3";
        //$card_ok = mb_split(',',$card_array);
        $card_ok = explode(',', $card_array);
        $card_array_num = count($card_ok);
        for ($i = 1; $i <= 4; $i++) {
            $card_rand_4[$i] = $card_ok[rand(0, $card_array_num - 1)];
        }

        $md5_rand = uniqid('', true) . $card_rand_4[1] . $card_rand_4[2] . $card_rand_4[3] . $card_rand_4[4];
        $md5_rand_ok = md5($md5_rand);
        $card_rand = substr($md5_rand_ok, 1, 30);
        return $card_rand;
    }
    #使用base64 存圖片
    public function base64tojpg($base64img, $newsfile)
    {
        $patterns = array();
        $patterns[0] = '/src=\"/';
        $patterns[1] = '/\"/';
        $replacements = array();
        $replacements[0] = '';
        $replacements[1] = '';
        $pic = preg_replace($patterns, $replacements, $base64img);
        $fileimg = "images/{$newsfile}/{$this->rand_my()}.png";
        $file = fopen($fileimg, 'wb');
        $data = explode(',', $pic);
        fwrite($file, base64_decode($data[1]));
        fclose($file);
        return $fileimg;
    }
    public function fileImg($name, $file)
    {
        // 圖片上傳模組
        $f = $_FILES["$name"];
        $pic_type = $f["type"];
        $pic_size = $f["size"];
        $picfile_1 = $f["tmp_name"];

        if (!empty($picfile_1)) {
            if ($pic_type == 'image/jpeg') {
                $picfile_3 = $this->rand_my() . '.jpg';
            } elseif ($pic_type == 'image/png') {
                $picfile_3 = $this->rand_my() . '.jpg';
            } elseif ($pic_type == 'image/gif') {
                $picfile_3 = $this->rand_my() . '.jpg';
            } else {
                $this->talk('檔案格式錯誤', '', 1);
                exit;

            }
            if ($pic_size <= 0 or $pic_size >= 4500000) {
                $this->talk('上傳的圖檔太大請壓縮', '', 1);
                exit;
            }
            move_uploaded_file($picfile_1, './images/' . $file . '/' . $picfile_3);

        } else {
            $picfile_3 = null;
        }
        return $picfile_3;
    }
    public function teacherImg($name, $fileName)
    {
        // 圖片上傳模組
        $f = $_FILES["$name"];
        $pic_type = $f["type"];
        $pic_size = $f["size"];
        $picfile_1 = $f["tmp_name"];

        if (!empty($picfile_1)) {
            if ($pic_type == 'image/jpeg') {
                $picfile_3 = $fileName . '.jpg';
            } elseif ($pic_type == 'image/png') {
                $picfile_3 = $fileName . '.jpg';
            } elseif ($pic_type == 'image/gif') {
                $picfile_3 = $fileName . '.jpg';
            } else {
                $this->talk('檔案格式錯誤', '', 1);
                exit;

            }
            if ($pic_size <= 0 or $pic_size >= 4500000) {
                $this->talk('上傳的圖檔太大請壓縮', '', 1);
                exit;
            }
            move_uploaded_file($picfile_1, './assets/images/avatars/' . $picfile_3);

        } else {
            $picfile_3 = null;
        }
        return $picfile_3;
    }

    public function NewUploadImg($name, $fileName, $path)
    {
        // 圖片上傳模組
        $f = $_FILES["$name"];
        $pic_type = $f["type"];
        $pic_size = $f["size"];
        $picfile_1 = $f["tmp_name"];

        if (!empty($picfile_1)) {
            if ($pic_type == 'image/jpeg') {
                $picfile_3 = $fileName.'.jpg';
            } elseif ($pic_type == 'image/png') {
                $picfile_3 = $fileName.'.jpg';
            } elseif ($pic_type == 'image/gif') {
                $picfile_3 = $fileName.'.jpg';
            } else {
                $this->talk('檔案格式錯誤', '', 1);
                exit;
            }
            if ($pic_size <= 0 or $pic_size >= 4500000) {
                $this->talk('上傳的圖檔太大請壓縮', '', 1);
                exit;
            }
            move_uploaded_file($picfile_1, './assets/images/'.$path.'/'.$picfile_3);

        } else {
            $picfile_3 = null;
        }
        return $picfile_3;
    }

    public function random_cid($length = 8, $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ0123456789')
    {
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }
    public function random_id($length = 8, $characters = '0123456789')
    {
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }
    public function random_string($length = 6, $characters = '0123456789')
    {
        if (!is_int($length) || $length < 0) {
            return false;
        }
        $characters_length = strlen($characters) - 1;
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, $characters_length)];
        }
        return $string;
    }

    public function courseImg($name)
    {
        // 圖片上傳模組
        $f = $_FILES["$name"];
        $pic_type = $f["type"];
        $pic_size = $f["size"];
        $picfile_1 = $f["tmp_name"];

        if (!empty($picfile_1)) {
            if ($pic_type == 'image/jpeg') {
                $picfile_3 = $this->rand_my() . '.jpg';
            } elseif ($pic_type == 'image/png') {
                $picfile_3 = $this->rand_my() . '.jpg';
            } elseif ($pic_type == 'image/gif') {
                $picfile_3 = $this->rand_my() . '.jpg';
            } else {
                $this->tool->talk('檔案格式錯誤', '', 1);
            }
            if ($pic_size <= 0 or $pic_size >= 4500000) {
                $this->tool->talk('上傳的圖檔太大請壓縮', '', 1);
                exit;
            }
            move_uploaded_file($picfile_1, './images/course/' . $picfile_3);

        } else {
            $picfile_3 = null;
        }
        return $picfile_3;
    }
    public function delpic3($Id, $img)
    {
        $file_path = './img/store/' . $Id . '/' . $img;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    public function delpic($folder, $img)
    {
        $file_path = './img/' . $folder . '/' . $img;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    public function aes128_cbc_encrypt($aesKey, $invoice_random)
    {
        $spec_key = "Dt8lyToo17X/XkXaQvihuA==";
        $key = hex2bin($aesKey);
        $iv = base64_decode($spec_key);
        $data = $this->pkcs5_pad($invoice_random, 16);
        return base64_encode(
            openssl_encrypt(
                $data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv
            )
        );
    }

    public function remove_punctuation($string)
    {
        $res = str_replace(
            array('!', '"', '#', '$', '%', '&', '\'', '(', ')', '*',
                '+', ', ', '-', '.', '/', ':', ';', '<', '=', '>',
                '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|',
                '}', '~', '；', '﹔', '︰', '﹕', '：', '，', '﹐', '、',
                '．', '﹒', '˙', '·', '。', '？', '！', '～', '‥', '‧',
                '′', '〃', '〝', '〞', '‵', '‘', '’', '『', '』', '「',
                '」', '“', '”', '…', '❞', '❝', '﹁', '﹂', '﹃', '﹄'),
            '',
            $string);
        return $res;
    }
    private function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    public function prevent_hack_butAllowSome($input)
    {
        $content = htmlspecialchars($input, ENT_QUOTES);

        $turned = array('&lt;pre&gt;', '&lt;/pre&gt;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;em&gt;', '&lt;/em&gt;', '&lt;u&gt;', '&lt;/u&gt;', '&lt;ul&gt;', '&lt;/ul&gt;', '&lt;li&gt;', '&lt;/li&gt;', '&lt;ol&gt;', '&lt;/ol&gt;');
        $turn_back = array('<pre>', '</pre>', '<b>', '</b>', '<em>', '</em>', '<u>', '</u>', '<ul>', '</ul>', '<li>', '</li>', '<ol>', '</ol>');

        $content = str_replace($turned, $turn_back, $content);

        return $content;
    }
    public function prevent_htmlspecialchars($input)
    {
        $temp = strip_tags($input);
        $res = htmlspecialchars($temp, ENT_QUOTES);
        return $res;
    }

    /************************取得layout資訊*********************** */
    public function layout_info()
    {
        $employeeId = session()->get('employeeId');
        $Layout = new LayoutController;
        $pages = $Layout->pages_db($employeeId);
        return $pages;

    }

}
