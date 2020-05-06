<?php

namespace Haxifang\Users\WithAvatar;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait AvatarHelper
{

    /**
     * 保存头像
     * 可接受参数：
     * 图像链接
     * base64图像
     * UploadedFile 上传的图像对象
     */
    public function saveAvatar($avatar, $extension = 'jpeg')
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $avatar, $res)) {
            $extension     = $res[2];
            $base64_string = str_replace($res[1], '', $avatar);
            $imageStream   = base64_decode($base64_string);
        } else if ($avatar instanceof UploadedFile) {
            $extension   = $avatar->getClientOriginalExtension();
            $imageStream = file_get_contents($avatar->getRealPath());
        } else {
            $imageStream = file_get_contents($avatar);
        }

        $avatarPath  = sprintf('/storage/app/avatars/avatar-%s.%s', $this->id, $extension);
        $storeStatus = self::UploadAvatar($avatarPath, $imageStream);
        if ($storeStatus) {
            $this->avatar = $avatarPath;
            $this->save();
        }
        return $this;
    }

    /**
     * 上传头像
     */
    public static function UploadAvatar($avatarPath, $fileStream)
    {
        return Storage::cloud()->put($avatarPath, $fileStream);
    }

    /**
     * 获取头像，如果没有返回默认头像
     */
    public function getAvatarUrlAttribute()
    {
        return $this->attributes['avatar'] ? $this->getAvatarLink() : url(self::getDefaultAvatar());
    }

    /**
     * 获取头像链接
     */
    public function getAvatarLink(string $avatarField = 'avatar', bool $AbsPath = true, $jumpCDNCache = false)
    {
        $avatar = $this->$avatarField;
        if ($jumpCDNCache) {
            $avatar = $avatar . '?t=' . now()->timestamp;
        }
        return $AbsPath ? Storage::cloud()->url($avatar) : $avatar;
    }

    /**
     * 获取默认头像相对路径
     */
    public static function getDefaultAvatar()
    {
        return '/images/avatars/avatar-' . rand(1, 20) . '.png';
    }
}
