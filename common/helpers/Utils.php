<?php
namespace common\helpers;

use Yii;

class Utils {
    //用户ID
    const ID_TYPE_USER = 31;
    //团队ID
    const ID_TYPE_TEAM = 2;
    //平台ID
    const ID_TYPE_PLATFORM = 3;
    //文章分类ID
    const ID_TYPE_ARTICLE_CATEGORY = 4;
    //成就宣言ID
    const ID_TYPE_PLAN = 5;
    //成就宣言详情ID
    const ID_TYPE_PLAN_DETAIL = 6;
    //周计划ID
    const ID_TYPE_WEEK_PLAN = 7;

    // 生成ID
	public static function createIncrementId($type = null)
    {
        $time = time();
        $time = $time - 1071497951;//1071497951->2003-12-15 22:19:11
        $time = $time<<18;
        //0-30已知类型，31是无类型
        $type = is_null($type) ? 31 : ($type % 31);
        $type = $type<<13;
        $seq  = rand(1,8191);
        $num  = $time|$type|$seq;
        $num -= 83629516358986; //这样保证10年内位数不会增长
        return $num;
    }

    //状态返回
    public static function returnMsg($code = 0, $msg = null, $data = array())
    {
    	$result = ['ret' => $code];

        if(!empty($msg))
        {
            $result['msg'] = $msg;
        }

        if(!empty($data))
        {
            $result['data'] = $data;
        }

        return $result;
    }

    public static function redirectMsg($code = 0, $url = null) 
    {
        if($code == 403)
        {
            $url = '/#/403';
        }
        if($code == 404)
        {
            $url = '/#/404';
        }
        $result = [
            'ret' => $code,
            'url' => $url
        ];

        return $result;
    }

    public static function pagination($pages)
    {
        return [
            'pagination' => [
                'total' => (int)$pages->totalCount,
                'page_size' => $pages->getPageSize(),
                'page_count' => $pages->getPageCount(),
                'current_page' => $pages->getPage()+1
            ]
        ];
    }

    public static function truncateUtf8String($string, $length, $etc = '...')
   {
       $result = '';
       $string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
       $strlen = strlen($string);
       for ($i = 0; (($i < $strlen) && ($length > 0)); $i++)
       {
       if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0'))
           {
           if ($length < 1.0)
               {
           break;
           }
           $result .= substr($string, $i, $number);
           $length -= 1.0;
           $i += $number - 1;
       }
           else
           {
           $result .= substr($string, $i, 1);
           $length -= 0.5;
       }
       }
       $result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
       if ($i < $strlen)
       {
       $result .= $etc;
       }
       return $result;
   }

    public static function avatar($uid)
    {
        $identicon = new \Identicon\Identicon();
        $imageData = $identicon->getImageDataUri($uid);

        $new_file = '';

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $imageData, $result))
        {
            $type = $result[2];
            $file_name = time().".{$type}";
            $file_path = Yii::getAlias("@runtime/tmp/").$file_name;

            file_put_contents($file_path, base64_decode(str_replace($result[1], '', $imageData)));

            $url = Yii::$app->AliyunOss->upload($file_name, $file_path, 'avatar');

            unlink($file_path);
            return $url;
        }
    }
}